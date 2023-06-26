<?php

namespace lucatume\WPBrowser\Project;

use Codeception\InitTemplate;
use Exception;
use lucatume\WPBrowser\Command\DevStart;
use lucatume\WPBrowser\Command\DevStop;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Extension\DockerComposeController;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\DockerCompose;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PluginProject extends InitTemplate implements ProjectInterface
{
    public const ERR_PLUGIN_NOT_FOUND = 1;
    private string $pluginFile;
    private string $pluginName;
    private ?TestEnvironment $testEnvironment;
    private $projectType = 'plugin';

    public function __construct(InputInterface $input, OutputInterface $output, protected string $workDir)
    {
        parent::__construct($input, $output);
        $pluginNameAndFile = self::findPluginNameAndFile($workDir);

        if ($pluginNameAndFile === false) {
            throw new InvalidArgumentException(
                "Could not find a plugin file in $workDir.",
                self::ERR_PLUGIN_NOT_FOUND
            );
        }

        [$this->pluginFile, $this->pluginName] = $pluginNameAndFile;
    }

    public function getType(): string
    {
        return 'plugin';
    }

    public function getPluginsString(): string
    {
        return basename($this->workDir) . '/' . basename($this->pluginFile);
    }

    /**
     * @return array{0: string, 1: string}|false
     */
    public static function findPluginNameAndFile(string $workDir): array|false
    {
        $pluginFile = null;
        $pluginName = null;

        $glob = glob($workDir . '/*.php', GLOB_NOSORT);

        if ($glob === false) {
            return false;
        }

        foreach ($glob as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }
            if (preg_match('~\\s*Plugin Name:\\s*(.*?)\\s*($|\\*/)~', $content, $matches)) {
                $pluginName = $matches[1];
                $pluginFile = $file;
                break;
            }
        }

        if (empty($pluginFile) || !($realpath = realpath($pluginFile))) {
            return false;
        }

        return [$realpath, $pluginName];
    }

    public function setup(): void
    {

        try {
            $this->say('You can use a portable configuration based on PHP built-in server, Chromedriver ' .
                'and MySQL on Docker.');
            $fast = $this->ask('Do you want to use this configuration?', true);

            if (!$fast) {
                $this->say('Review and update the <info>tests/.env</info> file to configure your testing environment.');
                $this->testEnvironment = new TestEnvironment();
            }

            $wpRootDir = $this->workDir . '/tests/_wordpress';

            if (!is_dir($wpRootDir) && !(mkdir($wpRootDir, 0777, true) && is_dir($wpRootDir))) {
                throw new RuntimeException("Could not create WordPress root directory $wpRootDir.");
            }

            $this->ensureDockerOrFail();
            $mysqlContainerPort = $this->getFreeLocalhostPort($this->workDir);
            $dockerComposeFile = $this->workDir . '/tests/docker-compose.yml';
            $this->scaffoldDockerComposeFile($mysqlContainerPort, $dockerComposeFile);
            $dockerCompose = new DockerCompose($dockerComposeFile);
            $dockerCompose->up('database');
            $this->sayInfo('Waiting for the database to be ready ...');
            $dockerCompose->waitForHealthy('database');

            $db = new MysqlDatabase('test', 'test', 'test', "127.0.0.1:$mysqlContainerPort", 'wp_');
            $db->create();

            $this->sayInfo('Installing WordPress in <info>tests/_wordpress</info> ...');
            Installation::scaffold($wpRootDir);
            $installation = new Installation($wpRootDir);
            $installation->configure($db);
            $serverLocalhostPort = $this->getFreeLocalhostPort($this->workDir);
            $installation->install(
                "http://localhost:$serverLocalhostPort",
                'admin',
                'password',
                'admin@exmaple.com',
                'Test'
            );
            FS::rrmdir($wpRootDir . '/wp-content/plugins');
            FS::rrmdir($wpRootDir . '/wp-content/themes');

            try {
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
            } catch (\Exception $e) {
                throw new RuntimeException('Could not create database dump: ' . $e->getMessage());
            }


            $this->sayInfo('Adding Chromedriver binary as a development dependency ...');
            $this->sayInfo('Composer might ask if you trust "webdriver-binary/binary-chromedriver" to execute code ' .
                'and wish to enable it now, answer "yes".');
            $composer = new Composer($this->workDir . '/composer.json');
            $composer->requireDev(['webdriver-binary/binary-chromedriver' => '*']);
            $composer->update('webdriver-binary/binary-chromedriver');
            $this->say();

            $chromedriverPort = $this->getFreeLocalhostPort($this->workDir);

            $testEnvironment = new TestEnvironment;
            $testEnvironment->wpRootDir = FS::relativePath($this->workDir, $wpRootDir);
            $testEnvironment->dbUrl = sprintf('mysql://root:password@127.0.0.1:%d/test', $mysqlContainerPort);
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
                    'port' => "%BUILTIN_SERVER_PORT%",
                ],
                BuiltInServerController::class => [
                    'workers' => 5,
                    'port' => "%CHROMEDRIVER_PORT%",
                    'docroot' => FS::relativePath($this->workDir, $wpRootDir)
                ],
                DockerComposeController::class => [
                    'compose-file' => FS::relativePath($this->workDir, $dockerComposeFile)
                ],

            ];
            $testEnvironment->customCommands[] = DevStart::class;
            $testEnvironment->customCommands[] = DevStop::class;

            $basename = basename($this->workDir);
            FS::symlink($this->workDir, $wpRootDir . '/wp-content/plugins/' . $basename);

            $testEnvironment->sayAfterSuccess = function () use ($basename): void {
                $this->say('The plugin has been linked into the ' .
                    "<info>tests/_wordpress/wp-content/plugins/$basename</info> directory.");
                $this->say("If your {$this->projectType} requires additional plugins and themes, place them in the " .
                    '<info>tests/_wordpress/wp-content/plugins</info> and ' .
                    '<info>tests/_wordpress/wp-content/themes</info> directories.');
            };

            $this->testEnvironment = $testEnvironment;
        } finally {
            if (isset($dockerCompose)) {
                $dockerCompose->down();
            }
        }
    }

    public function getTestEnv(): ?TestEnvironment
    {
        return $this->testEnvironment;
    }

    public function getName(): string
    {
        return $this->pluginName;
    }

    public function getPluginFilePathName(): string
    {
        return $this->pluginFile;
    }

    private function getPluginName(): string
    {
        return $this->pluginName;
    }

    private function getFreeLocalhostPort(
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

    private function ensureDockerOrFail(): void
    {
        $process = new Process(['docker', '--version']);
        $process->mustRun();
    }

    private function scaffoldDockerComposeFile(
        int $mysqlContainerPort,
        string $pathname
    ): void {
        $dockerComposeFileContents = <<<YAML
version: '3.7'
services:
  database:
    image: mariadb:10.8
    ports:
      - '$mysqlContainerPort:3306'
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: test
      MYSQL_USER: test
      MYSQL_PASSWORD: test
    healthcheck:
      test: [ "CMD", "mariadb-show", "-uroot", "-ppassword",  "test" ]
      interval: 1s
      timeout: 1s
      retries: 30
    tmpfs:
      - /var/lib/mysql
YAML;

        if (!file_put_contents($pathname, $dockerComposeFileContents)) {
            throw new RuntimeException("Could not write Docker Compose file $pathname.");
        }
        $this->sayInfo("Wrote Docker Compose file $pathname.");
    }
}
