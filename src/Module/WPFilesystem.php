<?php
/**
 * WordPress specific filesystem operations.
 *
 * @package Codeception\Module
 */

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module\Filesystem;
use Codeception\TestInterface;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimezone;
use Exception;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use PHPUnit\Framework\Assert;

/**
 * Class WPFilesystem
 *
 * @package Codeception\Module
 */
class WPFilesystem extends Filesystem
{
    /**
     * A list of the module required fields.
     *
     * @var array<string>
     */
    protected array $requiredFields = ['wpRootFolder'];

    /**
     * The default module configuration.
     *
     * @var array{
     *     themes: string,
     *     plugins: string,
     *     mu-plugins: string,
     *     uploads: string,
     * }
     */
    protected array $config = [
        'themes' => '/wp-content/themes',
        'plugins' => '/wp-content/plugins',
        'mu-plugins' => '/wp-content/mu-plugins',
        'uploads' => '/wp-content/uploads',
    ];

    /**
     * A list of paths to clean after the tests.
     *
     * @var array<string>
     */
    private array $toClean = [];

    /**
     * The current count of generated test plugins.
     *
     * @var int
     */
    private int $testPluginCount = 0;

    /**
     * Runs before each test.
     *
     * @param TestInterface $test The current test.
     *
     *
     * @throws ModuleConfigException If there's an issue with the module configuration.
     */
    public function _before(TestInterface $test): void
    {
        $this->ensureOptionalPaths(false);
    }

    /**
     * @throws ModuleConfigException
     * @throws ModuleException
     */
    protected function validateConfig(): void
    {
        parent::validateConfig();
        foreach (['wpRootFolder', 'themes', 'plugins', 'mu-plugins', 'uploads'] as $path) {
            if (!is_string($this->config[$path])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "The {$path} path must be a string."
                );
            }
        }
    }

    /**
     * Sets and checks that the optional paths, if set, are actually valid.
     *
     * @param bool $check Whether to check the paths for existence or not.
     *
     *
     * @throws ModuleConfigException If one of the paths does not exist.
     */
    private function ensureOptionalPaths(bool $check = true): void
    {
        $optionalPaths = [
            'themes' => [
                'mustExist' => true,
                'default' => '/wp-content/themes',
            ],
            'plugins' => [
                'mustExist' => true,
                'default' => '/wp-content/plugins',
            ],
            'mu-plugins' => [
                'mustExist' => false,
                'default' => '/wp-content/mu-plugins',
            ],
            'uploads' => [
                'mustExist' => true,
                'default' => '/wp-content/uploads',
            ],
        ];
        /** @var array{
         *     wpRootFolder: string,
         *     themes: string,
         *     plugins: string,
         *     mu-plugins: string,
         *     uploads: string
         * } $config
         */
        $config = $this->config;
        $wpRoot = FS::untrailslashit($config['wpRootFolder']);
        foreach ($optionalPaths as $configKey => $info) {
            if (empty($config[$configKey])) {
                $path = $info['default'];
            } else {
                $path = $config[$configKey];
            }
            if (!is_dir($path) || ($configKey === 'mu-plugins' && !is_dir(dirname($path)))) {
                $path = FS::unleadslashit(str_replace($wpRoot, '', $path));
                $absolutePath = $wpRoot . DIRECTORY_SEPARATOR . $path;
            } else {
                $absolutePath = $path;
            }

            if ($check
                && $info['mustExist'] && !is_dir($absolutePath)
                && !mkdir($absolutePath, 0777, true) && !is_dir($absolutePath)
            ) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "The {$configKey} config path [{$path}] does not exist."
                );
            }

            $this->config[$configKey] = FS::untrailslashit($absolutePath) . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Initializes the module before the tests.
     *
     *
     * @throws ModuleConfigException If the path to WordPress or the optional paths are not correct.
     */
    public function _initialize(): void
    {
        $this->ensureWpRootFolder();
        $this->ensureOptionalPaths();
    }

    /**
     * Checks the WordPress root folder exists and is a WordPress root folder.
     *
     * @throws ModuleConfigException if the WordPress root folder does not exist
     *                                                      or is not a valid WordPress root folder.
     *
     */
    private function ensureWpRootFolder(): void
    {
        /** @var array{wpRootFolder: string} $config */
        $config = $this->config;
        $wpRoot = $config['wpRootFolder'];

        if (!is_dir($wpRoot)) {
            $wpRoot = codecept_root_dir(FS::unleadslashit($wpRoot));
        }

        $message = "[{$wpRoot}] is not a valid WordPress root folder.\n\nThe WordPress root folder is the one that "
            . "contains the 'wp-load.php' file.";

        if (!(is_dir($wpRoot) && is_readable($wpRoot) && is_writable($wpRoot))) {
            throw new ModuleConfigException(__CLASS__, $message);
        }

        if (!file_exists($wpRoot . '/wp-load.php')) {
            throw new ModuleConfigException(__CLASS__, $message);
        }

        $this->config['wpRootFolder'] = FS::untrailslashit($wpRoot) . DIRECTORY_SEPARATOR;
    }

    /**
     * Runs after a test failure.
     *
     * @param TestInterface $test The test that just ran.
     * @param Exception $fail     The failure
     *
     * @return void;
     */
    public function _failed(TestInterface $test, Exception $fail): void
    {
        $this->_after($test);
    }

    /**
     * Runs after each test.
     *
     * @param TestInterface $test The test that just ran.
     */
    public function _after(TestInterface $test): void
    {
        if (!empty($this->toClean)) {
            $this->toClean = array_unique($this->toClean);
            foreach ($this->toClean as $target) {
                if (is_dir($target)) {
                    $this->debug("Removing [{$target}]...");
                    FS::rrmdir($target);
                } elseif (file_exists($target)) {
                    $this->debug("Removing [{$target}]...");
                    unlink($target);
                }
            }
        }
    }

    /**
     * Enters, changing directory, to the uploads folder in the local filesystem.
     *
     * @example
     * ```php
     * $I->amInUploadsPath('/logs');
     * $I->seeFileFound('shop.log');
     * ```
     *
     * @param string|int|null $path The path, relative to the site uploads folder.
     *
     *
     * @throws Exception If the path is a date string and is not parsable by the `strtotime` function.
     */
    public function amInUploadsPath(string|int $path = null): void
    {
        if (null === $path) {
            $path = $this->config['uploads'];
        } elseif (is_dir($this->config['uploads'] . FS::unleadslashit((string)$path))) {
            $path = $this->config['uploads'] . FS::unleadslashit((string)$path);
        } else {
            // time based?
            $path = $this->config['uploads'] . $this->buildDateFrag($path);
        }
        $this->amInPath($path);
    }

    /**
     * Checks if file exists in the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ```php
     * $I->seeUploadedFileFound('some-file.txt');
     * $I->seeUploadedFileFound('some-file.txt','today');
     * ?>
     * ```
     *
     * @param string $filename      The file path, relative to the uploads folder or the current folder.
     * @param int|string|null $date A string compatible with `strtotime` or a Unix timestamp.
     *
     * @throws ModuleException
     */
    public function seeUploadedFileFound(string $filename, int|string $date = null): void
    {
        $path = $this->getUploadsPath($filename, $date);
        Assert::assertFileExists($path);
    }

    /**
     * Returns the path to the specified uploads file of folder.
     *
     * Not providing a value for `$file` and `$date` will return the uploads folder path.
     *
     * @example
     * ```php
     * $todaysPath = $I->getUploadsPath();
     * $lastWeek = $I->getUploadsPath('', '-1 week');
     * ```
     *
     * @param string $file                            The file path, relative to the uploads folder.
     * @param DateTimeInterface|string|int|null $date A string compatible with `strtotime`, a Unix timestamp or a Date
     *                                                object.
     *
     * @return string The absolute path to an uploaded file.
     * @throws ModuleException
     * @throws Exception
     */
    public function getUploadsPath(string $file = '', mixed $date = null): string
    {
        $dateFrag = $date !== null ?
            $this->buildDateFrag($date)
            : '';

        $uploads = FS::untrailslashit($this->config['uploads']);
        $path = $file;
        if (!str_contains($file, $uploads)) {
            $path = implode(
                DIRECTORY_SEPARATOR,
                array_filter([
                    $uploads,
                    $dateFrag,
                    FS::unleadslashit($file),
                ])
            );
        }

        return $path;
    }

    /**
     * Builds the additional path fragment depending on the date.
     *
     * @param string|int|DateTimeInterface $date
     *
     * @return string The relative path with the date path appended, if needed.
     *
     * @throw InvalidArgumentException
     * @throws Exception
     */
    private function buildDateFrag(string|int|DateTimeInterface $date): string
    {
        if (!$date instanceof DateTimeInterface) {
            $timestamp = is_string($date) && !is_numeric($date) ? strtotime($date) : (int)$date;
            if (empty($timestamp)) {
                throw new InvalidArgumentException('The date provided is not valid');
            }
            $date = new DateTimeImmutable(
                '@' . $timestamp,
                new DateTimezone(date_default_timezone_get())
            );
        }

        return $date->format('Y') . DIRECTORY_SEPARATOR . $date->format('m');
    }

    /**
     * Checks thata a file does not exist in the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->dontSeeUploadedFileFound('some-file.txt');
     * $I->dontSeeUploadedFileFound('some-file.txt','today');
     * ```
     *
     * @param string $file          The file path, relative to the uploads folder or the current folder.
     * @param int|string|null $date A string compatible with `strtotime` or a Unix timestamp.
     *
     * @throws ModuleException
     */
    public function dontSeeUploadedFileFound(string $file, int|string $date = null): void
    {
        if (method_exists(Assert::class, 'assertFileDoesNotExist')) {
            Assert::assertFileDoesNotExist($this->getUploadsPath($file, $date));
        } else {
            Assert::assertFileNotExists($this->getUploadsPath($file, $date));
        }
    }

    /**
     * Checks that a file in the uploads folder contains a string.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ```php
     * $I->seeInUploadedFile('some-file.txt', 'foo');
     * $I->seeInUploadedFile('some-file.txt','foo', 'today');
     * ```
     *
     * @param string $file          The file path, relative to the uploads folder or the current folder.
     * @param string $contents      The expected file contents or part of them.
     * @param int|string|null $date A string compatible with `strtotime` or a Unix timestamp.
     *
     * @throws ModuleException
     */
    public function seeInUploadedFile(string $file, string $contents, int|string $date = null): void
    {
        Assert::assertStringEqualsFile(
            $this->getUploadsPath(
                $file,
                $date
            ),
            $contents
        );
    }

    /**
     * Checks that a file in the uploads folder does contain a string.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ```php
     * $I->dontSeeInUploadedFile('some-file.txt', 'foo');
     * $I->dontSeeInUploadedFile('some-file.txt','foo', 'today');
     * ```
     *
     * @param string $file          The file path, relative to the uploads folder or the current folder.
     * @param string $contents      The not expected file contents or part of them.
     * @param int|string|null $date A string compatible with `strtotime` or a Unix timestamp.
     */
    public function dontSeeInUploadedFile(string $file, string $contents, int|string $date = null): void
    {
        Assert::assertStringNotEqualsFile(
            $this->getUploadsPath(
                $file,
                $date
            ),
            $contents
        );
    }

    /**
     * Deletes a dir in the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->deleteUploadedDir('folder');
     * $I->deleteUploadedDir('folder', 'today');
     * ```
     *
     * @param DateTime|int|string|null $date The date of the uploads to delete, will default to `now`.
     *
     * @param string $dir                    The path to the directory to delete, relative to the uploads folder.
     *
     * @throws ModuleException If the destination folder could not be removed.
     */
    public function deleteUploadedDir(string $dir, DateTime|int|string $date = null): void
    {
        $dir = $this->getUploadsPath($dir, $date);
        $this->debug('Deleting folder ' . $dir);
        if (!FS::rrmdir($dir)) {
            throw new ModuleException(__CLASS__, "Could not remove the [{$dir}] folder.");
        }
    }

    /**
     * Deletes a file in the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->deleteUploadedFile('some-file.txt');
     * $I->deleteUploadedFile('some-file.txt', 'today');
     * ```
     *
     * @param string $file          The file path, relative to the uploads folder or the current folder.
     * @param int|string|null $date A string compatible with `strtotime` or a Unix timestamp.
     */
    public function deleteUploadedFile(string $file, int|string $date = null): void
    {
        $file = $this->getUploadsPath($file, $date);
        $this->deleteFile($file);
    }

    /**
     * Clears a folder in the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->cleanUploadsDir('some/folder');
     * $I->cleanUploadsDir('some/folder', 'today');
     * ```
     *
     * @param DateTime|int|string|null $date The date of the uploads to delete, will default to `now`.
     *
     * @param string|null $dir               The path to the directory to delete, relative to the uploads folder.
     */
    public function cleanUploadsDir(string $dir = null, DateTime|int|string $date = null): void
    {
        $dir = null === $dir ? $this->config['uploads'] : $this->getUploadsPath(
            $dir,
            $date
        );
        $this->cleanDir($dir);
    }

    /**
     * Copies a folder to the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo');
     * $I->copyDirToUploads(codecept_data_dir('foo'), 'uploadsFoo', 'today');
     * ```
     *
     * @param string $dst                    The path to the destination file, relative to the current uploads folder.
     * @param DateTime|int|string|null $date The date of the uploads to delete, will default to `now`.
     *
     * @param string $src                    The path to the source file, relative to the current uploads folder.
     */
    public function copyDirToUploads(string $src, string $dst, DateTime|int|string $date = null): void
    {
        $this->copyDir($src, $this->getUploadsPath($dst, $date));
    }

    /**
     * Writes a string to a file in the the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->writeToUploadedFile('some-file.txt', 'foo bar');
     * $I->writeToUploadedFile('some-file.txt', 'foo bar', 'today');
     * ```
     *
     * @param string $data                   The data to write to the file.
     * @param DateTime|int|string|null $date The date of the uploads to delete, will default to `now`.
     *
     * @param string $filename               The path to the destination file, relative to the current uploads folder.
     *
     * @return string The absolute path to the destination file.
     *
     * @throws ModuleException If the destination folder could not be created or the destination
     *                                                file could not be written.
     */
    public function writeToUploadedFile(string $filename, string $data, DateTime|int|string $date = null): string
    {
        $filename = $this->getUploadsPath($filename, $date);
        $dir = dirname($filename);

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new ModuleException(__CLASS__, "Could not create the [{$dir}] folder.");
            }
            $this->toClean[] = $dir;
        }

        if (file_put_contents($filename, $data) === false) {
            throw new ModuleException(__CLASS__, "Could not write data to file[{$filename}].");
        }

        return $filename;
    }

    /**
     * Opens a file in the the uploads folder.
     *
     * The date argument can be a string compatible with `strtotime` or a Unix
     * timestamp that will be used to build the `Y/m` uploads subfolder path.
     *
     * @example
     * ``` php
     * $I->openUploadedFile('some-file.txt');
     * $I->openUploadedFile('some-file.txt', 'time');
     * ```
     *
     * @param DateTime|int|string|null $date The date of the uploads to delete, will default to `now`.
     *
     * @param string $filename               The path to the file, relative to the current uploads folder.
     */
    public function openUploadedFile(string $filename, DateTime|int|string $date = null): void
    {
        $this->openFile($this->getUploadsPath($filename, $date));
    }

    /**
     * Sets the current working folder to a folder in a plugin.
     *
     * @example
     * ``` php
     * $I->amInPluginPath('my-plugin');
     * ```
     *
     * @param string $path The folder path, relative to the root uploads folder, to change to.
     */
    public function amInPluginPath(string $path): void
    {
        $this->amInPath($this->config['plugins'] . FS::unleadslashit($path));
    }

    /**
     * Copies a folder to a folder in a plugin.
     *
     * @example
     * ``` php
     * // Copy the 'foo' folder to the 'foo' folder in the plugin.
     * $I->copyDirToPlugin(codecept_data_dir('foo'), 'my-plugin/foo');
     * ```
     *
     * @param string $src       The path to the source directory to copy.
     * @param string $pluginDst The destination path, relative to the plugins root folder.
     */
    public function copyDirToPlugin(string $src, string $pluginDst): void
    {
        $this->copyDir(
            $src,
            $this->config['plugins'] . FS::unleadslashit($pluginDst)
        );
    }

    /**
     * Deletes a file in a plugin folder.
     *
     * @example
     * ``` php
     * $I->deletePluginFile('my-plugin/some-file.txt');
     * ```
     *
     * @param string $file The folder path, relative to the plugins root folder.
     */
    public function deletePluginFile(string $file): void
    {
        $this->deleteFile($this->config['plugins'] . FS::unleadslashit($file));
    }

    /**
     * Writes a file in a plugin folder.
     *
     * @example
     * ``` php
     * $I->writeToPluginFile('my-plugin/some-file.txt', 'foo');
     * ```
     *
     * @param string $file The path to the file, relative to the plugins root folder.
     * @param string $data The data to write in the file.
     */
    public function writeToPluginFile(string $file, string $data): void
    {
        $this->writeToFile(
            $this->config['plugins'] . FS::unleadslashit($file),
            $data
        );
    }

    /**
     * Checks that a file is not found in a plugin folder.
     *
     * @example
     * ``` php
     * $I->dontSeePluginFileFound('my-plugin/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to the plugins root folder.
     */
    public function dontSeePluginFileFound(string $file): void
    {
        $this->dontSeeFileFound($this->config['plugins'] . FS::unleadslashit($file));
    }

    /**
     * Checks that a file is found in a plugin folder.
     *
     * @example
     * ``` php
     * $I->seePluginFileFound('my-plugin/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to thep plugins root folder.
     */
    public function seePluginFileFound(string $file): void
    {
        $this->seeFileFound($this->config['plugins'] . FS::unleadslashit($file));
    }

    /**
     * Checks that a file in a plugin folder contains a string.
     *
     * @example
     * ``` php
     * $I->seeInPluginFile('my-plugin/some-file.txt', 'foo');
     * ```
     *
     * @param string $file     The path to the file, relative to the plugins root folder.
     * @param string $contents The contents to check the file for.
     */
    public function seeInPluginFile(string $file, string $contents): void
    {
        Assert::assertStringEqualsFile(
            $this->config['plugins'] . FS::unleadslashit($file),
            $contents
        );
    }

    /**
     * Checks that a file in a plugin folder does not contain a string.
     *
     * @example
     * ``` php
     * $I->dontSeeInPluginFile('my-plugin/some-file.txt', 'foo');
     * ```
     *
     * @param string $file     The path to the file, relative to the plugins root folder.
     * @param string $contents The contents to check the file for.
     */
    public function dontSeeInPluginFile(string $file, string $contents): void
    {
        Assert::assertStringNotEqualsFile(
            $this->config['plugins'] . FS::unleadslashit($file),
            $contents
        );
    }

    /**
     * Cleans, emptying it, a folder in a plugin folder.
     *
     * @example
     * ``` php
     * $I->cleanPluginDir('my-plugin/foo');
     * ```
     *
     * @param string $dir The path to the folder, relative to the plugins root folder.
     */
    public function cleanPluginDir(string $dir): void
    {
        $this->cleanDir($this->config['plugins'] . FS::unleadslashit($dir));
    }

    /**
     * Sets the current working folder to a folder in a theme.
     *
     * @example
     * ``` php
     * $I->amInThemePath('my-theme');
     * ```
     *
     * @param string $path The path to the theme folder, relative to themes root folder.
     */
    public function amInThemePath(string $path): void
    {
        $this->amInPath($this->config['themes'] . FS::unleadslashit($path));
    }

    /**
     * Copies a folder in a theme folder.
     *
     * @example
     * ``` php
     * $I->copyDirToTheme(codecept_data_dir('foo'), 'my-theme');
     * ```
     *
     * @param string $src      The path to the source file.
     * @param string $themeDst The path to the destination folder, relative to the themes root folder.
     */
    public function copyDirToTheme(string $src, string $themeDst): void
    {
        $this->copyDir(
            $src,
            $this->config['themes'] . FS::unleadslashit($themeDst)
        );
    }

    /**
     * Deletes a file in a theme folder.
     *
     * @example
     * ``` php
     * $I->deleteThemeFile('my-theme/some-file.txt');
     * ```
     *
     * @param string $file The path to the file to delete, relative to the themes root folder.
     */
    public function deleteThemeFile(string $file): void
    {
        $this->deleteFile($this->config['themes'] . FS::unleadslashit($file));
    }

    /**
     * Writes a string to a file in a theme folder.
     *
     * @example
     * ``` php
     * $I->writeToThemeFile('my-theme/some-file.txt', 'foo');
     * ```
     *
     * @param string $file The path to the file, relative to the themese root folder.
     * @param string $data The data to write to the file.
     */
    public function writeToThemeFile(string $file, string $data): void
    {
        $this->writeToFile(
            $this->config['themes'] . FS::unleadslashit($file),
            $data
        );
    }

    /**
     * Checks that a file is not found in a theme folder.
     *
     * @example
     * ``` php
     * $I->dontSeeThemeFileFound('my-theme/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to the themes root folder.
     */
    public function dontSeeThemeFileFound(string $file): void
    {
        $this->dontSeeFileFound($this->config['themes'] . FS::unleadslashit($file));
    }

    /**
     * Checks that a file is found in a theme folder.
     *
     * @example
     * ``` php
     * $I->seeThemeFileFound('my-theme/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to the themes root folder.
     */
    public function seeThemeFileFound(string $file): void
    {
        $this->seeFileFound($this->config['themes'] . FS::unleadslashit($file));
    }

    /**
     * Checks that a file in a theme folder contains a string.
     *
     * @example
     * ``` php
     * <?php
     * $I->seeInThemeFile('my-theme/some-file.txt', 'foo');
     * ?>
     * ```
     *
     * @param string $file     The path to the file, relative to the themes root folder.
     * @param string $contents The contents to check the file for.
     */
    public function seeInThemeFile(string $file, string $contents): void
    {
        Assert::assertStringEqualsFile(
            $this->config['themes'] . FS::unleadslashit($file),
            $contents
        );
    }

    /**
     * Checks that a file in a theme folder does not contain a string.
     *
     * @example
     * ``` php
     * $I->dontSeeInThemeFile('my-theme/some-file.txt', 'foo');
     * ```
     *
     * @param string $file     The path to the file, relative to the themes root folder.
     * @param string $contents The contents to check the file for.
     */
    public function dontSeeInThemeFile(string $file, string $contents): void
    {
        Assert::assertStringNotEqualsFile(
            $this->config['themes'] . FS::unleadslashit($file),
            $contents
        );
    }

    /**
     * Clears, emptying it, a folder in a theme folder.
     *
     * @example
     * ``` php
     * $I->cleanThemeDir('my-theme/foo');
     * ```
     *
     * @param string $dir The path to the folder, relative to the themese root folder.
     */
    public function cleanThemeDir(string $dir): void
    {
        $this->cleanDir($this->config['themes'] . FS::unleadslashit($dir));
    }

    /**
     * Sets the current working folder to a folder in a mu-plugin.
     *
     * @example
     * ``` php
     * $I->amInMuPluginPath('mu-plugin');
     * ```
     *
     * @param string $path The path to the folder, relative to the mu-plugins root folder.
     */
    public function amInMuPluginPath(string $path): void
    {
        $this->amInPath($this->config['mu-plugins'] . FS::unleadslashit($path));
    }

    /**
     * Copies a folder to a folder in a mu-plugin.
     *
     * @example
     * ``` php
     * $I->copyDirToMuPlugin(codecept_data_dir('foo'), 'mu-plugin/foo');
     * ```
     *
     * @param string $src       The path to the source file to copy.
     * @param string $pluginDst The path to the destination folder, relative to the mu-plugins root folder.
     */
    public function copyDirToMuPlugin(string $src, string $pluginDst): void
    {
        $this->copyDir(
            $src,
            $this->config['mu-plugins'] . FS::unleadslashit($pluginDst)
        );
    }

    /**
     * Deletes a file in a mu-plugin folder.
     *
     * @example
     * ``` php
     * $I->deleteMuPluginFile('mu-plugin1/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to the mu-plugins root folder.
     */
    public function deleteMuPluginFile(string $file): void
    {
        $this->deleteFile($this->config['mu-plugins'] . FS::unleadslashit($file));
    }

    /**
     * Writes a file in a mu-plugin folder.
     *
     * @example
     * ``` php
     * $I->writeToMuPluginFile('mu-plugin1/some-file.txt', 'foo');
     * ```
     *
     * @param string $file The path to the destination file, relative to the mu-plugins root folder.
     * @param string $data The data to write to the file.
     */
    public function writeToMuPluginFile(string $file, string $data): void
    {
        $this->writeToFile(
            $this->config['mu-plugins'] . FS::unleadslashit($file),
            $data
        );
    }

    /**
     * Checks that a file is not found in a mu-plugin folder.
     *
     * @example
     * ``` php
     * $I->dontSeeMuPluginFileFound('mu-plugin1/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to the mu-plugins folder.
     */
    public function dontSeeMuPluginFileFound(string $file): void
    {
        $this->dontSeeFileFound($this->config['mu-plugins'] . FS::unleadslashit($file));
    }

    /**
     * Checks that a file is found in a mu-plugin folder.
     *
     * @example
     * ``` php
     * $I->seeMuPluginFileFound('mu-plugin1/some-file.txt');
     * ```
     *
     * @param string $file The path to the file, relative to the mu-plugins folder.
     */
    public function seeMuPluginFileFound(string $file): void
    {
        $this->seeFileFound($this->config['mu-plugins'] . FS::unleadslashit($file));
    }

    /**
     * Checks that a file in a mu-plugin folder contains a string.
     *
     * @example
     * ``` php
     * $I->seeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
     * ```
     *
     * @param string $file     The path the file, relative to the mu-plugins root folder.
     * @param string $contents The contents to check the file for.
     */
    public function seeInMuPluginFile(string $file, string $contents): void
    {
        Assert::assertStringEqualsFile(
            $this->config['mu-plugins'] . FS::unleadslashit($file),
            $contents
        );
    }

    /**
     * Checks that a file in a mu-plugin folder does not contain a string.
     *
     * @example
     * ``` php
     * $I->dontSeeInMuPluginFile('mu-plugin1/some-file.txt', 'foo');
     * ```
     *
     * @param string $file     The path to the file, relative to the mu-plugins root folder.
     * @param string $contents The contents to check the file for.
     */
    public function dontSeeInMuPluginFile(string $file, string $contents): void
    {
        Assert::assertStringNotEqualsFile(
            $this->config['mu-plugins'] . FS::unleadslashit($file),
            $contents
        );
    }

    /**
     * Cleans, emptying it, a folder in a mu-plugin folder.
     *
     * @example
     * ``` php
     * $I->cleanMuPluginDir('mu-plugin1/foo');
     * ```
     *
     * @param string $dir The path to the directory, relative to the mu-plugins root folder.
     */
    public function cleanMuPluginDir(string $dir): void
    {
        $this->cleanDir($this->config['mu-plugins'] . FS::unleadslashit($dir));
    }

    /**
     * Creates a plugin file, including plugin header, in the plugins folder.
     *
     * The plugin is just created and not activated; the code can not contain the opening '<?php' tag.
     *
     * @example
     * ``` php
     * $code = 'echo "Hello world!"';
     * $I->havePlugin('foo/plugin.php', $code);
     * // Load the code from a file.
     * $code = file_get_contents(codecept_data_dir('code/plugin.php'));
     * $I->havePlugin('foo/plugin.php', $code);
     * ```
     *
     * @param string $path The path to the file to create, relative to the plugins folder.
     * @param string $code The content of the plugin file with or without the opening PHP tag.
     *
     * @throws ModuleException If the plugin folder and/or files could not be created.
     *
     */
    public function havePlugin(string $path, string $code): void
    {
        $path = str_replace('\\', '/', $path);
        $fullPath = $this->config['plugins'] . FS::unleadslashit($path);

        if (!isset(pathinfo($fullPath)['extension'])) {
            $fullPath .= '.php';
        }

        if (!str_contains($path, '/')) {
            $slug = pathinfo($fullPath, PATHINFO_FILENAME);
            $toClean = $fullPath;
        } else {
            $slug = basename(dirname($path));
            $dir = dirname($fullPath);

            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new ModuleException(
                    __CLASS__,
                    "Could not create [{$dir}] plugin folder."
                );
            }

            $toClean = $dir;
        }

        $code = $this->removeOpeningPhpTag($code);
        $name = $slug;
        $contents = <<<PHP
<?php
/*
Plugin Name: $name
Description: $name
*/

$code
PHP;

        if (!(file_put_contents($fullPath, $contents, LOCK_EX))) {
            throw new ModuleException(
                __CLASS__,
                "Could not create [{$fullPath}] plugin file."
            );
        }

        $this->toClean[] = $toClean;
    }

    /**
     * Creates a mu-plugin file, including plugin header, in the mu-plugins folder.
     *
     * The code can not contain the opening '<?php' tag.
     *
     * @example
     * ``` php
     * $code = 'echo "Hello world!"';
     * $I->haveMuPlugin('foo-mu-plugin.php', $code);
     * // Load the code from a file.
     * $code = file_get_contents(codecept_data_dir('code/mu-plugin.php'));
     * $I->haveMuPlugin('foo-mu-plugin.php', $code);
     * ```
     *
     * @param string $filename The path to the file to create, relative to the plugins root folder.
     * @param string $code     The content of the plugin file with or without the opening PHP tag.
     *
     * @throws ModuleException If the mu-plugin folder and/or files could not be created.
     *
     */
    public function haveMuPlugin(string $filename, string $code): void
    {
        $fullPath = $this->config['mu-plugins'] . FS::unleadslashit($filename);

        if (!pathinfo($fullPath, PATHINFO_EXTENSION)) {
            $fullPath .= '.php';
        }

        $dir = dirname($fullPath);

        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new ModuleException(
                    __CLASS__,
                    "Could not create [{$dir}] mu-plugin folder."
                );
            }

            $this->toClean[] = $dir;
        }

        $code = $this->removeOpeningPhpTag($code);
        $name = 'Test mu-plugin ' . ++$this->testPluginCount;
        $contents = <<<PHP
<?php
/*
Plugin Name: $name
Description: $name
*/

$code
PHP;

        if (!(file_put_contents($fullPath, $contents, LOCK_EX))) {
            throw new ModuleException(
                __CLASS__,
                "Could not create [{$fullPath}] mu-plugin file."
            );
        }

        $this->toClean[] = $fullPath;
    }

    /**
     * Creates a theme file structure, including theme style file and index, in the themes folder.
     *
     * The theme is just created and not activated; the code can not contain the opening '<?php' tag.
     *
     * @example
     * ``` php
     * $code = 'sayHi();';
     * $functionsCode  = 'function sayHi(){echo "Hello world";};';
     * $I->haveTheme('foo', $indexCode, $functionsCode);
     * // Load the code from a file.
     * $indexCode = file_get_contents(codecept_data_dir('code/index.php'));
     * $functionsCode = file_get_contents(codecept_data_dir('code/functions.php'));
     * $I->haveTheme('foo', $indexCode, $functionsCode);
     * ```
     *
     * @param string $folder            The path to the theme to create, relative to the themes root folder.
     * @param string $indexFileCode     The content of the theme index.php file with or without the opening PHP tag.
     * @param string $functionsFileCode The content of the theme functions.php file with or without the opening PHP tag.
     *
     * @throws ModuleException If the mu-plugin folder and/or files could not be created.
     */
    public function haveTheme(string $folder, string $indexFileCode, string $functionsFileCode = ''): void
    {
        $dir = $this->config['themes'] . FS::untrailslashit(FS::unleadslashit($folder));
        $styleFile = $dir . DIRECTORY_SEPARATOR . 'style.css';
        $indexFile = $dir . DIRECTORY_SEPARATOR . 'index.php';
        $functionsFile = $dir . DIRECTORY_SEPARATOR . 'functions.php';

        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new ModuleException(
                __CLASS__,
                "Could not create [{$dir}] theme folder."
            );
        }
        $indexFileCode = $this->removeOpeningPhpTag($indexFileCode);
        $functionsFileCode = $this->removeOpeningPhpTag($functionsFileCode);
        $name = $folder;
        $style = <<<CSS
/*
Theme Name: $name
Author: wp-browser
Description: $name
Version: 1.0
*/
CSS;
        if (!(file_put_contents($styleFile, $style, LOCK_EX))) {
            throw new ModuleException(
                __CLASS__,
                "Could not create [{$styleFile}] theme file."
            );
        }

        $this->toClean[] = $dir;

        $index = '<?php ' . $indexFileCode;


        if (!(file_put_contents($indexFile, $index, LOCK_EX))) {
            throw new ModuleException(
                __CLASS__,
                "Could not create [{$indexFile}] theme file."
            );
        }

        $functions = '<?php ' . $functionsFileCode;

        if (!(file_put_contents($functionsFile, $functions, LOCK_EX))) {
            throw new ModuleException(
                __CLASS__,
                "Could not create [{$indexFile}] theme file."
            );
        }
    }

    /**
     * Returns the absolute path to WordPress root folder without trailing slash.
     *
     * @example
     * ```php
     * $rootFolder = $I->getWpRootFolder();
     * $I->assertFileExists($rootFolder . 'wp-load.php');
     * ```
     *
     * @return string The absolute path to the WordPress root folder.
     */
    public function getWpRootFolder(): string
    {
        /** @var array{wpRootFolder: string} $config */
        $config = $this->config;
        return FS::untrailslashit($config['wpRootFolder']);
    }

    /**
     * Returns the absolute path to a blog uploads folder or file.
     *
     * @example
     * ```php
     * $blogId = $I->haveBlogInDatabase('test');
     * $testTodayUploads = $I->getBlogUploadsPath($blogId);
     * $testLastMonthLogs = $I->getBlogUploadsPath($blogId, '/logs', '-1 month');
     * ```
     *
     * @param string $file                                     The path, relatitve to the blog uploads folder, to the
     *                                                         file or folder.
     * @param DateTimeImmutable|DateTime|string|null $date     The date that should be used to build the uploads
     *                                                         sub-folders in the year/month format; a UNIX timestamp or
     *                                                         a string supported by the `strtotime` function; defaults
     *                                                         to `now`.
     *
     * @param int $blogId                                      The blog ID to get the path for.
     *
     * @return string The absolute path to a blog uploads folder or file.
     *
     * @throws Exception If the date is not a valid format.
     */
    public function getBlogUploadsPath(
        int $blogId,
        string $file = '',
        DateTimeImmutable|DateTime|string $date = null
    ): string {
        $dateFrag = $date !== null ?
            $this->buildDateFrag($date)
            : '';

        $uploads = FS::untrailslashit($this->config['uploads']) . "/sites/{$blogId}";
        $path = $file;

        if (!str_contains($file, $uploads)) {
            $path = implode(
                DIRECTORY_SEPARATOR,
                array_filter([
                    $uploads,
                    $dateFrag,
                    FS::unleadslashit($file),
                ])
            );
        }

        return $path;
    }

    /**
     * Creates an empty folder in the WordPress installation uploads folder.
     *
     * @example
     * ```php
     * $logsDir = $I->makeUploadsDir('logs/acme');
     * ```
     *
     * @param string $path The path, relative to the WordPress installation uploads folder, of the folder
     *                     to create.
     *
     * @return string The absolute path to the created folder.
     *
     * @throws ModuleException
     */
    public function makeUploadsDir(string $path): string
    {
        $path = $this->getUploadsPath($path);

        if (is_dir($path)) {
            $this->debug("Uploads folder '{$path}' already exists.");
            return $path;
        }
        try {
            if (!mkdir($path, 0777, true) && !is_dir($path)) {
                throw new ModuleException($this, sprintf('Could not create uploads folder "%s"', $path));
            }
        } catch (ModuleException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ModuleException($this, sprintf(
                "Could not create uploads folder '%s'\nreason: %s\nuser: %s",
                $path,
                $e->getMessage(),
                get_current_user()
            ));
        }

        $this->debug("Created uploads folder '{$path}'");

        $this->toClean[] = $path;

        return $path;
    }

    /**
     * Remove the opening PHP tag from the code if present.
     *
     * @param string $code The code to update.
     *
     * @return string The code without the opening PHP tag.
     */
    private function removeOpeningPhpTag(string $code): string
    {
        // Remove the opening PHP tag if present.
        $code = preg_replace('/^\<\?php\\s*/', '', $code) ?: '';

        return $code;
    }

    /**
     * @param string $directory The directory to check.
     * @param string $message   An optional message to display if the directory does not exist.
     */
    public function assertDirectoryExists(string $directory, string $message = ''): void
    {
        Assert::assertDirectoryExists($directory, $message);
    }
}
