<?php

namespace lucatume\WPBrowser\Project;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\TestCase\WPTestCase;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Strings;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class PluginProject extends ContentProject
{
    /**
     * @var string
     */
    protected $workDir;
    public const ERR_PLUGIN_NOT_FOUND = 1;
    /**
     * @var string
     */
    private $pluginFile;
    /**
     * @var string
     */
    private $pluginName;
    /**
     * @var string
     */
    private $pluginDir;

    protected function getProjectType(): string
    {
        return 'plugin';
    }

    public function __construct(InputInterface $input, OutputInterface $output, string $workDir)
    {
        $this->workDir = $workDir;
        parent::__construct($input, $output);
        $pluginNameAndFile = self::parseDir($workDir);
        $this->pluginDir = basename($this->workDir);

        if ($pluginNameAndFile === false) {
            throw new InvalidArgumentException(
                "Could not find a plugin file in $workDir.",
                self::ERR_PLUGIN_NOT_FOUND
            );
        }

        [$this->pluginFile, $this->pluginName] = $pluginNameAndFile;
        $this->testEnvironment = new TestEnvironment();
    }

    public function getType(): string
    {
        return 'plugin';
    }

    public function getActivationString(): string
    {
        return basename($this->workDir) . '/' . basename($this->pluginFile);
    }

    /**
     * @return array{0: string, 1: string}|false
     */
    public static function parseDir(string $workDir)
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

        if (empty($pluginName) || empty($pluginFile) || !($realpath = realpath($pluginFile))) {
            return false;
        }

        return [$realpath, $pluginName];
    }

    public function getName(): string
    {
        return $this->pluginName;
    }

    public function getPluginFilePathName(): string
    {
        return $this->pluginFile;
    }

    /**
     * @throws WorkerException
     * @throws Throwable
     * @throws ProcessException
     */
    protected function activate(string $wpRootDir, int $serverLocalhostPort): bool
    {
        $codeExec = new CodeExecutionFactory($wpRootDir, 'localhost:' . $serverLocalhostPort);
        $pluginString = basename(dirname($this->pluginFile)) . '/' . basename($this->pluginFile);
        $activatePlugin = $codeExec->toActivatePlugin($pluginString, false);
        $activationResult = Loop::executeClosure($activatePlugin)->getReturnValue();
        if ($activationResult instanceof Throwable) {
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

    protected function scaffoldEndToEndActivationCest(): void
    {
        $cestCode = Strings::renderString(
            <<<EOT
<?php

namespace Tests\\EndToEnd;

use Tests\\Support\\EndToEndTester;

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

EOT
,
            [
                'slug' => Strings::slug($this->getName())
            ]
        );

        if (!file_put_contents($this->workDir . '/tests/EndToEnd/ActivationCest.php', $cestCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/EndToEnd/ActivationCest.php.');
        }
    }

    protected function scaffoldIntegrationActivationTest(): void
    {
        $testCode = Strings::renderString(
            <<<EOT
<?php

namespace Tests;

use lucatume\\WPBrowser\\TestCase\\WPTestCase;

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

        \$this->assertInstanceOf(\\WP_Post::class, \$post);
    }

    public function test_plugin_active(): void
    {
        \$this->assertTrue(is_plugin_active('{{pluginString}}'));
    }
}
EOT
,
            [
                'pluginString' => $this->getActivationString()
            ]
        );

        if (!file_put_contents($this->workDir . '/tests/Integration/SampleTest.php', $testCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/Integration/SampleTest.php.');
        }
    }

    protected function symlinkProjectInContentDir(string $wpRootDir): void
    {
        FS::symlink($this->workDir, $wpRootDir . "/wp-content/plugins/" . $this->pluginDir);
    }
}
