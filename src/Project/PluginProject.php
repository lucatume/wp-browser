<?php

namespace lucatume\WPBrowser\Project;

use Codeception\InitTemplate;
use Exception;
use lucatume\WPBrowser\Command\DevInfo;
use lucatume\WPBrowser\Command\DevRestart;
use lucatume\WPBrowser\Command\DevStart;
use lucatume\WPBrowser\Command\DevStop;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\TestCase\WPTestCase;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Strings;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\Source;
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

            if (preg_match('/Plugin Name:\\s+(.*?)(\*\/)?$/um', $content, $matches)) {
                $pluginName = trim($matches[1]);
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

            $basename = basename($this->workDir);
            FS::symlink($this->workDir, $wpRootDir . '/wp-content/plugins/' . $basename);

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
        } catch (\Exception $e) {
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

        $testEnvironment->afterSuccess = function () use ($basename, $activated): void {
            if ($activated) {
                $this->scaffoldEndToEndActivationCest();
                $this->scaffoldIntegrationActivationTest();
            }
            $this->say('The plugin has been linked into the ' .
                "<info>tests/_wordpress/wp-content/plugins/$basename</info> directory.");
            $this->say("If your {$this->projectType} requires additional plugins and themes, place them in the " .
                '<info>tests/_wordpress/wp-content/plugins</info> and ' .
                '<info>tests/_wordpress/wp-content/themes</info> directories.');
        };

        $this->testEnvironment = $testEnvironment;
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

    protected function activate(string $wpRootDir, int $serverLocalhostPort): bool
    {
        $codeExec = new CodeExecutionFactory($wpRootDir, 'localhost:' . $serverLocalhostPort);
        $pluginString = basename(dirname($this->pluginFile)) . '/' . basename($this->pluginFile);
        $activatePlugin = $codeExec->toActivatePlugin($pluginString, false);
        $activationResult = Loop::executeClosure($activatePlugin)->getReturnValue();
        if ($activationResult instanceof \Throwable) {
            $message = $activationResult->getMessage();
            $this->sayWarning('Could not activate plugin: ' . $message);
            $this->say('This might happen because the plugin has unmet dependencies; wp-browser configuration ' .
                'will continue, but you will need to manually activate the plugin and update the dump in ' .
                'tests/Support/Data/dump.sql.');
            return false;
        }

        $this->sayInfo('Plugin activated.');

        return true;
    }

    private function scaffoldEndToEndActivationCest(): void
    {
        $cestCode = Strings::renderString(
            <<< EOT
<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class ActivationCest
{
    public function test_it_deactivates_activates_correctly(EndToEndTester \$I): void
    {
        \$I->loginAsAdmin();
        \$I->amOnPluginsPage();

        \$I->seePluginActivated('{{slug}}');

        \$I->deactivatePlugin('{{slug}}');

        \$I->seePluginDeactivated('{{slug}}');

        \$I->activatePlugin('{{slug}}');

        \$I->seePluginActivated('{{slug}}');
    }
}

EOT,
            [
                'slug' => Strings::slug($this->getName())
            ]
        );

        if (!file_put_contents($this->workDir . '/tests/EndToEnd/ActivationCest.php', $cestCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/EndToEnd/ActivationCest.php.');
        }
    }

    private function scaffoldIntegrationActivationTest(): void
    {
        $testCode = Strings::renderString(
            <<< EOT
<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

class SampleTest extends WPTestCase
{
    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set-up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_factory(): void
    {
        \$post = static::factory()->post->create_and_get();

        \$this->assertInstanceOf(\WP_Post::class, \$post);
    }

    public function test_plugin_active(): void
    {
        \$this->assertTrue(is_plugin_active('{{pluginString}}'));
    }
}
EOT,
            [
                'pluginString' => $this->getPluginsString()
            ]
        );

        if (!file_put_contents($this->workDir . '/tests/Integration/SampleTest.php', $testCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/Integration/SampleTest.php.');
        }
    }
}
