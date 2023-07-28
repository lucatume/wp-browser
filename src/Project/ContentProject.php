<?php

namespace lucatume\WPBrowser\Project;

use Closure;
use Codeception\InitTemplate;
use Exception;
use lucatume\WPBrowser\Command\DevInfo;
use lucatume\WPBrowser\Command\DevRestart;
use lucatume\WPBrowser\Command\DevStart;
use lucatume\WPBrowser\Command\DevStop;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\Source;
use Symfony\Component\Process\Process;

abstract class ContentProject extends InitTemplate implements ProjectInterface
{
    protected ?TestEnvironment $testEnvironment;

    abstract protected function getProjectType(): string;

    abstract public function getName(): string;

    abstract public function getActivationString(): string;

    abstract protected function symlinkProjectInContentDir(string $wpRootDir): void;

    abstract protected function activate(string $wpRootDir, int $serverLocalhostPort): bool;

    /**
     * @return array<string>|false
     */
    abstract public static function parseDir(string $workDir): array|false;

    abstract protected function scaffoldEndToEndActivationCest(): void;

    abstract protected function scaffoldIntegrationActivationTest(): void;

    protected function getFreeLocalhostPort(
        string $docRoot
    ): int {
        try {
            $process = new Process(['php', '-S', 'localhost:0', '-t', $docRoot]);
            $process->start();
            do {
                if (!$process->isRunning() && $process->getExitCode() !== 0) {
                    throw new RuntimeException($process->getErrorOutput() ?: $process->getOutput());
                }
                $output = $process->getErrorOutput();
                $port = preg_match('~localhost:(\d+)~', $output, $matches) ? $matches[1] : null;
            } while ($port === null);
            return (int)$port;
        } catch (Exception $e) {
            throw new RuntimeException(
                'Could not start PHP built-in server to find free localhost port: ' . $e->getMessage()
            );
        } finally {
            if (isset($process)) {
                $process->stop();
            }
        }
    }

    public function getTestEnv(): ?TestEnvironment
    {
        return $this->testEnvironment;
    }

    private function getAfterSuccessClosure(bool $activated): Closure
    {
        $basename = basename($this->workDir);
        return function () use ($basename, $activated): void {
            if ($activated) {
                $this->scaffoldEndToEndActivationCest();
                $this->scaffoldIntegrationActivationTest();
            }
            $this->say("The {$this->getProjectType()} has been linked into the " .
                "<info>tests/_wordpress/wp-content/{$this->getProjectType()}s/$basename</info> directory.");
            $this->say("If your {$this->getProjectType()} requires additional plugins and themes, place them in the " .
                '<info>tests/_wordpress/wp-content/plugins</info> and ' .
                '<info>tests/_wordpress/wp-content/themes</info> directories.');
        };
    }

    public function setup()
    {
        try {
            $this->say('You can use a portable configuration based on PHP built-in server, Chromedriver ' .
                'and SQLite.');
            $useDefaultConfiguration = $this->ask('Do you want to use this configuration?', true);

            if (!$useDefaultConfiguration) {
                $this->say('Review and update the <info>tests/.env</info> file to configure your testing environment.');
                $this->testEnvironment = new TestEnvironment();
                return;
            }

            $wpRootDir = $this->workDir . '/tests/_wordpress';
            $dataDir = $this->workDir . '/tests/_wordpress/data';

            if (!is_dir($dataDir) && !(mkdir($dataDir, 0777, true) && is_dir($dataDir))) {
                throw new RuntimeException("Could not create WordPress data directory $dataDir.");
            }

            $db = new SQLiteDatabase('tests/_wordpress/data', 'db.sqlite');

            $this->sayInfo('Installing WordPress in <info>tests/_wordpress</info> ...');
            Installation::scaffold($wpRootDir);
            // Remove the directory used to store the WordPress installation.
            FS::rrmdir(Source::getWordPressVersionsCacheDir());
            $installation = new Installation($wpRootDir);
            $installation->configure($db);
            $serverLocalhostPort = $this->getFreeLocalhostPort($this->workDir);
            $installation->install(
                "http://localhost:$serverLocalhostPort",
                'admin',
                'password',
                'admin@exmaple.com',
                $this->getName() . ' Test'
            );

            // Symlink the project into the WordPress plugins or themes directory.
            $this->symlinkProjectInContentDir($wpRootDir);

            $activated = $this->activate($wpRootDir, $serverLocalhostPort);

            $tmpDumpFile = tempnam(sys_get_temp_dir(), 'wpb');

            if ($tmpDumpFile === false) {
                throw new RuntimeException('Could not create temporary file to store database dump.');
            }

            $db->dump($tmpDumpFile);
            FS::mkdirp($this->workDir . '/tests/Support/Data');
            if (!rename($tmpDumpFile, $this->workDir . '/tests/Support/Data/dump.sql')) {
                throw new RuntimeException(
                    "Could not move database dump from $tmpDumpFile to tests/Support/Data/dump.sql."
                );
            }
            $this->sayInfo('Created database dump in <info>tests/Support/Data/dump.sql</info>.');
        } catch (Exception $e) {
            throw new RuntimeException('Could not create database dump: ' . $e->getMessage());
        }

        $this->sayInfo('Adding Chromedriver binary as a development dependency ...');
        $composer = new Composer($this->workDir . '/composer.json');
        $composer->requireDev(['webdriver-binary/binary-chromedriver' => '*']);
        $composer->allowPluginsFromPackage('webdriver-binary/binary-chromedriver');
        $composer->update('webdriver-binary/binary-chromedriver');
        $this->say();

        $chromedriverPort = $this->getFreeLocalhostPort($this->workDir);

        $testEnvironment = new TestEnvironment;
        $testEnvironment->wpRootDir = FS::relativePath($this->workDir, $wpRootDir);
        $testEnvironment->dbUrl = 'sqlite://%codecept_root_dir%/tests/_wordpress/data/db.sqlite';
        $testEnvironment->testTablePrefix = 'test_';
        $testEnvironment->wpTablePrefix = 'wp_';
        $testEnvironment->wpUrl = "http://localhost:$serverLocalhostPort";
        $testEnvironment->wpDomain = "localhost:$serverLocalhostPort";
        $testEnvironment->chromeDriverHost = 'localhost';
        $testEnvironment->chromeDriverPort = $chromedriverPort;
        $testEnvironment->envFileContents = <<<EOT
# The port on which the PHP built-in server will serve the WordPress installation.
BUILTIN_SERVER_PORT=$serverLocalhostPort
EOT;

        $testEnvironment->extensionsEnabled = [
            ChromeDriverController::class => [
                'port' => "%CHROMEDRIVER_PORT%",
            ],
            BuiltInServerController::class => [
                'workers' => 5,
                'port' => "%BUILTIN_SERVER_PORT%",
                'docroot' => FS::relativePath($this->workDir, $wpRootDir)
            ]

        ];
        $testEnvironment->customCommands[] = DevStart::class;
        $testEnvironment->customCommands[] = DevStop::class;
        $testEnvironment->customCommands[] = DevInfo::class;
        $testEnvironment->customCommands[] = DevRestart::class;

        $testEnvironment->afterSuccess = $this->getAfterSuccessClosure($activated);

        $this->testEnvironment = $testEnvironment;
    }
}
