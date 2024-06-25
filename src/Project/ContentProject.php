<?php

namespace lucatume\WPBrowser\Project;

use Closure;
use Codeception\InitTemplate;
use lucatume\WPBrowser\Command\ChromedriverUpdate;
use lucatume\WPBrowser\Command\DevInfo;
use lucatume\WPBrowser\Command\DevRestart;
use lucatume\WPBrowser\Command\DevStart;
use lucatume\WPBrowser\Command\DevStop;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Extension\Symlinker;
use lucatume\WPBrowser\Utils\ChromedriverInstaller;
use lucatume\WPBrowser\Utils\Codeception;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\Source;
use Throwable;

abstract class ContentProject extends InitTemplate implements ProjectInterface
{
    /**
     * @var \lucatume\WPBrowser\Project\TestEnvironment
     */
    protected $testEnvironment;

    /**
     * @return array<string>|false
     */
    abstract public static function parseDir(string $workDir);

    abstract public function getActivationString(): string;

    public function getTestEnv(): TestEnvironment
    {
        return $this->testEnvironment;
    }

    /**
     * @throws Throwable
     */
    public function setup(): void
    {
        $this->say(
            'You can use a portable configuration based on PHP built-in server, Chromedriver ' .
            'and SQLite.'
        );
        $useDefaultConfiguration = $this->ask('Do you want to use this configuration?', true);

        if (!$useDefaultConfiguration) {
            $this->say('Review and update the <info>tests/.env</info> file to configure your testing environment.');
            $this->testEnvironment = new TestEnvironment();
            return;
        }

        $wpRootDir = $this->workDir . '/tests/_wordpress';
        $dataDir = $this->workDir . '/tests/_wordpress/data';
        $dataDirRelativePath = Codeception::dataDir();

        if (!is_dir($dataDir) && !(mkdir($dataDir, 0777, true) && is_dir($dataDir))) {
            throw new RuntimeException("Could not create WordPress data directory $dataDir.");
        }

        $db = new SQLiteDatabase('tests/_wordpress/data', 'db.sqlite');

        $this->sayInfo('Installing WordPress in tests/_wordpress ...');
        Installation::scaffold($wpRootDir);
        // Remove the directory used to store the WordPress installation.
        FS::rrmdir(Source::getWordPressVersionsCacheDir());
        $installation = new Installation($wpRootDir);
        $installation->configure($db);
        $serverLocalhostPort = Random::openLocalhostPort();
        $installation->install(
            "http://localhost:$serverLocalhostPort",
            'admin',
            'password',
            'admin@exmaple.com',
            $this->getName() . ' Test'
        );

        $this->symlinkProjectInContentDir($wpRootDir);

        $activated = $this->activate($wpRootDir, $serverLocalhostPort);

        $tmpDumpFile = tempnam(sys_get_temp_dir(), 'wpb');

        if ($tmpDumpFile === false) {
            throw new RuntimeException('Could not create temporary file to store database dump.');
        }

        $db->dump($tmpDumpFile);
        FS::mkdirp($this->workDir . '/' . $dataDirRelativePath);
        if (!rename($tmpDumpFile, $this->workDir . '/' . $dataDirRelativePath . '/dump.sql')) {
            throw new RuntimeException(
                "Could not move database dump from $tmpDumpFile to '.$dataDirRelativePath.'/dump.sql."
            );
        }
        $this->sayInfo('Created database dump in <info>' . $dataDirRelativePath . '/dump.sql</info>.');

        $this->sayInfo('Installing Chromedriver ...');
        $chromedriverPath = (new ChromedriverInstaller())->install();
        $this->sayInfo("Chromedriver installed in $chromedriverPath");
        $chromedriverPort = Random::openLocalhostPort();
        $this->testEnvironment->testTablePrefix = 'test_';
        $this->testEnvironment->wpTablePrefix = 'wp_';
        $this->testEnvironment->wpUrl = "http://localhost:$serverLocalhostPort";
        $this->testEnvironment->wpDomain = "localhost:$serverLocalhostPort";
        $this->testEnvironment->chromeDriverHost = 'localhost';
        $this->testEnvironment->chromeDriverPort = $chromedriverPort;
        $this->testEnvironment->extraEnvFileContents = <<<EOT
# The port on which the PHP built-in server will serve the WordPress installation.
BUILTIN_SERVER_PORT=$serverLocalhostPort

EOT;

        $symlinkerConfig = [ 'wpRootFolder' => '%WORDPRESS_ROOT_DIR%' ];

        if ($this instanceof PluginProject) {
            $symlinkerConfig['plugins'] = ['.'];
        } elseif ($this instanceof ThemeProject) {
            $symlinkerConfig['themes'] = ['.'];
        }

        $this->testEnvironment->extensionsEnabled = [
            ChromeDriverController::class => [
                'port' => "%CHROMEDRIVER_PORT%",
            ],
            BuiltInServerController::class => [
                'workers' => 5,
                'port' => "%BUILTIN_SERVER_PORT%",
                'docroot' => "%WORDPRESS_ROOT_DIR%",
                'env' => [
                    'DATABASE_TYPE' => 'sqlite',
                    'DB_ENGINE' => 'sqlite',
                    'DB_DIR' => '%codecept_root_dir%' . DIRECTORY_SEPARATOR . $dataDirRelativePath,
                    'DB_FILE' => 'db.sqlite'
                ]
            ],
            Symlinker::class => $symlinkerConfig
        ];
        $this->testEnvironment->customCommands[] = DevStart::class;
        $this->testEnvironment->customCommands[] = DevStop::class;
        $this->testEnvironment->customCommands[] = DevInfo::class;
        $this->testEnvironment->customCommands[] = DevRestart::class;
        $this->testEnvironment->customCommands[] = ChromedriverUpdate::class;
        $this->testEnvironment->wpRootDir = FS::relativePath($this->workDir, $wpRootDir);
        $this->testEnvironment->dbUrl = 'sqlite://' . implode(
            DIRECTORY_SEPARATOR,
            ['%codecept_root_dir%', 'tests', '_wordpress', 'data', 'db.sqlite']
        );
        $this->testEnvironment->afterSuccess = $this->getAfterSuccessClosure($activated);
    }

    abstract public function getName(): string;

    abstract public function activate(string $wpRootDir, int $serverLocalhostPort): bool;

    private function getAfterSuccessClosure(bool $activated): Closure
    {
        $basename = basename($this->workDir);
        return function () use ($basename, $activated): void {
            if ($activated) {
                $this->scaffoldEndToEndActivationCest();
                $this->scaffoldIntegrationActivationTest();
            }
            $this->say(
                "The {$this->getProjectType()} was symlinked the " .
                "<info>tests/_wordpress/wp-content/{$this->getProjectType()}s/$basename</info> directory."
            );
            $this->say(
                "If your {$this->getProjectType()} requires additional plugins and themes, add them to the 'plugins' " .
                "and 'themes' section of the Symlinker extension or place them in the " .
                "<info>tests/_wordpress/wp-content/plugins</info> and " .
                "<info>tests/_wordpress/wp-content/themes</info> directories."
            );
            $this->say(
                "Read more about the Symlinker extension in the " .
                "<info>https://github.com/lucatume/wp-browser/blob/master/docs/extensions.md#symlinker</info> file."
            );
        };
    }

    abstract protected function scaffoldEndToEndActivationCest(): void;

    abstract protected function scaffoldIntegrationActivationTest(): void;

    abstract protected function getProjectType(): string;

    abstract protected function symlinkProjectInContentDir(string $wpRootDir): void;
}
