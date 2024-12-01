<?php

namespace lucatume\WPBrowser\WordPress;

use DirectoryIterator;
use FilesystemIterator;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationChecks;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Multisite;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Throwable;

class Installation
{
    use WordPressChecks;
    use InstallationChecks;

    public const SQLITE_PLUGIN = __DIR__ . '/../../includes/sqlite-database-integration';

    /**
     * @var array<string>
     */
    private static array $scaffoldedInstallations = [];
    private string $wpRootDir;
    private InstallationState\InstallationStateInterface $installationState;

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     * @throws WpConfigFileException
     */
    public function __construct(
        string $wpRootDir,
        bool $checkDb = true
    ) {
        $this->wpRootDir = $this->checkWpRootDir($wpRootDir);
        $this->installationState = $this->setInstallationState($checkDb);
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     * @throws WpConfigFileException
     */
    public static function scaffold(string $wpRootDir, string $version = 'latest'): self
    {
        $emptyDir = new EmptyDir($wpRootDir);
        $emptyDir->scaffold($version);

        self::$scaffoldedInstallations[] = $wpRootDir;

        return new self($wpRootDir);
    }

    /**
     * @return array<string>
     */
    public static function getCleanScaffoldedInstallations(): array
    {
        $installations = self::$scaffoldedInstallations;
        self::$scaffoldedInstallations = [];

        return $installations;
    }

    /**
     * @throws InstallationException
     */
    public static function placeSqliteMuPlugin(string $muPluginsDir, string $contentDir): string
    {
        $dropinPathname = $contentDir . '/db.php';
        if (self::hasSqliteDropin($dropinPathname)) {
            return $dropinPathname;
        }

        if (is_file($dropinPathname)) {
            throw new InstallationException(
                "The db.php file already exists in the $contentDir directory and it's not a SQLite drop-in.",
                InstallationException::DB_DROPIN_ALREADY_EXISTS
            );
        }

        if (!(
            FS::mkdirp($muPluginsDir)
            && FS::recurseCopy(self::SQLITE_PLUGIN, $muPluginsDir . '/sqlite-database-integration')
        )) {
            throw new InstallationException(
                "Could not copy the SQLite mu-plugin file to $muPluginsDir.",
                InstallationException::SQLITE_PLUGIN_COPY_FAILED
            );
        }

        $pluginDirPathname = $muPluginsDir . '/sqlite-database-integration';
        $dbCopyPathname = $pluginDirPathname . '/db.copy';
        $dbCopyContents = file_get_contents($dbCopyPathname);

        if (empty($dbCopyContents)) {
            throw new InstallationException(
                "Could not read the SQLite db.copy file at $dbCopyPathname.",
                InstallationException::SQLITE_PLUGIN_DB_COPY_READ_FAILED
            );
        }

        $updatedContents = str_replace(
            [
                '{SQLITE_IMPLEMENTATION_FOLDER_PATH}',
                '{SQLITE_PLUGIN}',
                '{SQLITE_MAIN_FILE}'
            ],
            [
                $pluginDirPathname,
                'sqlite-database-integration/load.php',
                $pluginDirPathname . '/load.php'
            ],
            $dbCopyContents
        );

        if (!file_put_contents($dropinPathname, $updatedContents, LOCK_EX)) {
            throw new InstallationException(
                "Could not write the SQLite db.php file at $contentDir.",
                InstallationException::SQLITE_DROPIN_COPY_FAILED
            );
        }

        // Place a .gitignore file to ignore the db.php file, throw if failed.
        if (!is_file($contentDir . '/.gitignore')
            && !file_put_contents($contentDir . '/.gitignore', 'db.php', LOCK_EX)
        ) {
            throw new InstallationException(
                "Could not write the SQLite .gitignore file at $contentDir.",
                InstallationException::SQLITE_DROPIN_COPY_FAILED
            );
        }

        return $dropinPathname;
    }

    /**
     * @throws Throwable
     */
    public static function findInDir(string $searchDir, bool $checkDb = true): self
    {
        $wpDir = null;

        // Recursively look into the wpDir to find the directory that contains the wp-load.php file.
        $files = new \RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $searchDir,
                    FilesystemIterator::UNIX_PATHS | FilesystemIterator::CURRENT_AS_PATHNAME
                ),
                RecursiveIteratorIterator::SELF_FIRST
            ),
            '/^.+\/wp-load\.php$/i',
        );
        /** @var string $file */
        foreach ($files as $file) {
            $wpDir = dirname($file);
            break;
        }

        if (!is_string($wpDir)) {
            throw new InstallationException(
                "Could not find a WordPress installation in the $searchDir directory.",
                InstallationException::WORDPRESS_NOT_FOUND
            );
        }

        return new self($wpDir, $checkDb);
    }

    public function configure(
        DatabaseInterface $db,
        int $multisite = InstallationStateInterface::SINGLE_SITE,
        ?ConfigurationData $configurationData = null
    ): self {
        $this->installationState = $this->installationState->configure($db, $multisite, $configurationData);

        return $this;
    }

    public function convertToMultisite(bool $subdomainInstall = false): self
    {
        $this->installationState = $this->installationState->convertToMultisite($subdomainInstall);

        return $this;
    }

    public function getAuthKey(): string
    {
        return $this->installationState->getAuthKey();
    }

    public function getSecureAuthKey(): string
    {
        return $this->installationState->getSecureAuthKey();
    }

    public function getLoggedInKey(): string
    {
        return $this->installationState->getLoggedInKey();
    }

    public function getNonceKey(): string
    {
        return $this->installationState->getNonceKey();
    }

    public function getAuthSalt(): string
    {
        return $this->installationState->getAuthSalt();
    }

    public function getSecureAuthSalt(): string
    {
        return $this->installationState->getSecureAuthSalt();
    }

    public function getLoggedInSalt(): string
    {
        return $this->installationState->getLoggedInSalt();
    }

    public function getNonceSalt(): string
    {
        return $this->installationState->getNonceSalt();
    }

    public function getRootDir(): string
    {
        return $this->installationState->getWpRootDir();
    }

    public function getVersion(): ?Version
    {
        return $this->installationState->getVersion();
    }

    public function isMultisite(): bool
    {
        return $this->installationState->isMultisite();
    }

    public function getDb(): ?DatabaseInterface
    {
        return $this->installationState->getDb();
    }

    public function getWpRootDir(?string $path = null): string
    {
        return empty($path) ? $this->wpRootDir : $this->wpRootDir . FS::unleadslashit($path);
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws WpConfigFileException
     * @throws Throwable
     * @throws WorkerException
     */
    private function setInstallationState(bool $checkDb = true): InstallationState\InstallationStateInterface
    {
        if (!is_file($this->wpRootDir . '/wp-load.php')) {
            return new InstallationState\EmptyDir($this->wpRootDir);
        }

        $wpConfigFilePath = $this->findWpConfigFilePath($this->wpRootDir);

        if (!$wpConfigFilePath) {
            return new InstallationState\Scaffolded($this->wpRootDir);
        }

        $installationState = new InstallationState\Configured($this->wpRootDir, $wpConfigFilePath);
        $multisite = $installationState->isMultisite();

        if (!$checkDb) {
            return $installationState;
        }

        if (!$this->isInstalled($multisite, $installationState->getDb())) {
            return $installationState;
        }

        return $multisite ?
            new InstallationState\Multisite($this->wpRootDir, $wpConfigFilePath)
            : new InstallationState\Single($this->wpRootDir, $wpConfigFilePath);
    }

    public function isEmpty(): bool
    {
        return $this->installationState instanceof InstallationState\EmptyDir;
    }

    public function install(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): self {
        $this->installationState = $this->installationState->install(
            $url,
            $adminUser,
            $adminPassword,
            $adminEmail,
            $title
        );

        return $this;
    }

    public function getState(): InstallationState\InstallationStateInterface
    {
        return $this->installationState;
    }

    public function isConfigured(): bool
    {
        return $this->installationState->isConfigured();
    }

    /**
     * @return array{
     *     AUTH_KEY: string,
     *     SECURE_AUTH_KEY: string,
     *     LOGGED_IN_KEY: string,
     *     NONCE_KEY: string,
     *     AUTH_SALT: string,
     *     SECURE_AUTH_SALT: string,
     *     LOGGED_IN_SALT: string,
     *     NONCE_SALT: string
     * }
     */
    public function getSalts(): array
    {
        return $this->installationState->getSalts();
    }

    /**
     * @param ?array<string> $checkKeys
     *
     * @return array<string, mixed>
     */
    public function report(?array $checkKeys = null): array
    {
        $map = [
            'rootDir' => fn(): string => $this->installationState->getWpRootDir(),
            'version' => fn(): array => $this->installationState->getVersion()->toArray(),
            'constants' => fn(): array => $this->installationState->getConstants(),
            'globals' => fn(): array => $this->installationState->getGlobals()
        ];

        $checksMap = $checkKeys === null ? $map : array_intersect_key($map, array_flip($checkKeys));

        foreach ($checksMap as &$value) {
            $value = $value();
        }

        return $checksMap;
    }

    public function getPluginsDir(string $path = ''): string
    {
        return $this->installationState->getPluginsDir($path);
    }

    public function updateOption(string $option, mixed $value): int
    {
        return $this->installationState->updateOption($option, $value);
    }

    public function getThemesDir(string $path = ''): string
    {
        return $this->installationState->getThemesDir($path);
    }

    public function getWpConfigFilePath(): string
    {
        return $this->installationState->getWpConfigPath();
    }

    public function getContentDir(string $path = ''): string
    {
        return $this->installationState->getContentDir($path);
    }

    /**
     * @param string[] $command
     *
     * @throws ProcessFailedException
     * @throws DbException
     */
    public function runWpCliCommandOrThrow(array $command): Process
    {
        $process = $this->runWpCliCommand($command);

        if ($process->run() !== 0) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    public function usesSqlite(): bool
    {
        return $this->installationState->getDb() instanceof SQLiteDatabase;
    }

    public function usesMysql(): bool
    {
        return $this->installationState->getDb() instanceof MySQLDatabase;
    }

    public function getMuPluginsDir(): string
    {
        return $this->installationState->getMuPluginsDir();
    }

    /**
     * @throws Throwable
     */
    public function setDb(DatabaseInterface $db): self
    {
        if ($db instanceof SQLiteDatabase && !$this->hasSqliteDropin($this->getContentDir('db.php'))) {
            throw new InstallationException(
                'SQLite database requires sqlite drop-in.',
                InstallationException::SQLITE_PLUGIN_NOT_FOUND
            );
        }

        $this->installationState = $this->installationState->setDb($db);
        return $this;
    }

    private static function hasSqliteDropin(string $dropinPathname): bool
    {

        if (!is_file($dropinPathname)) {
            return false;
        }

        $file = fopen($dropinPathname, 'rb');

        if (!is_resource($file)) {
            return false;
        }

        $contents = fread($file, 1024);

        if ($contents === false) {
            return false;
        }

        fclose($file);

        return str_contains($contents, 'Plugin Name: SQLite integration (Drop-in)');
    }

    /**
     * @param string[] $command
     *
     * @throws DbException
     */
    public function runWpCliCommand(array $command): Process
    {
        if ($this->installationState instanceof Multisite) {
            $hasUrlOption = false;
            foreach ($command as $arg) {
                if (str_starts_with($arg, '--url')) {
                    $hasUrlOption = true;
                    break;
                }
            }
            $db = $this->installationState->getDb();
            if (!$hasUrlOption && ($url = $db->getOption('home'))) {
                array_unshift($command, '--url=' . $url);
            }
        }

        $process = new CliProcess($command, $this->getRootDir());

        $process->run();

        return $process;
    }
}
