<?php

namespace lucatume\WPBrowser\Project;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Strings;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ThemeProject extends ContentProject
{
    /**
     * @var string
     */
    protected $workDir;
    public const ERR_INVALID_THEME_DIR = 1;
    /**
     * @var string
     */
    private $basename;
    /**
     * @var string
     */
    private $name;

    public function __construct(InputInterface $input, OutputInterface $output, string $workDir)
    {
        $this->workDir = $workDir;
        parent::__construct($input, $output);
        $this->basename = basename($workDir);
        $themeInfo = self::parseDir($workDir);

        if ($themeInfo === false) {
            throw new InvalidArgumentException(
                sprintf(
                    'The directory "%s" does not seem to be a valid theme directory.',
                    $workDir
                ),
                self::ERR_INVALID_THEME_DIR
            );
        }

        [$this->name] = $themeInfo;
        $this->testEnvironment = new TestEnvironment();
    }

    public function getType(): string
    {
        return 'theme';
    }

    public function getActivationString(): string
    {
        return $this->basename;
    }

    protected function getProjectType(): string
    {
        return 'theme';
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array{0: string}|false
     * @throws RuntimeException
     */
    public static function parseDir(string $workDir)
    {
        if (!is_file($workDir . '/style.css')) {
            return false;
        }

        $styleCssContents = file_get_contents($workDir . '/style.css');

        if (empty($styleCssContents)) {
            return false;
        }

        preg_match('/Theme Name:\\s(.*?)$/um', $styleCssContents, $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $name = $matches[1];

        return [$name];
    }

    protected function symlinkProjectInContentDir(string $wpRootDir): void
    {
        FS::symlink($this->workDir, $wpRootDir . "/wp-content/themes/" . $this->basename);
    }

    /**
     * @throws WorkerException
     * @throws Throwable
     * @throws ProcessException
     */
    public function activate(string $wpRootDir, int $serverLocalhostPort): bool
    {
        $codeExec = new CodeExecutionFactory($wpRootDir, 'localhost:' . $serverLocalhostPort);
        $switchTheme = $codeExec->toSwitchTheme($this->basename, false);
        $activationResult = Loop::executeClosure($switchTheme)->getReturnValue();
        if ($activationResult instanceof Throwable) {
            $message = $activationResult->getMessage();
            $this->sayWarning('Could not activate theme: ' . $message);
            $this->say('This might happen because the theme has unmet dependencies; wp-browser configuration ' .
                'will continue, but you will need to manually activate the theme and update the dump in ' .
                'tests/Support/Data/dump.sql.');
            return false;
        }

        $this->sayInfo('Theme activated.');

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
    public function test_it_activates_correctly(EndToEndTester \$I): void
    {
        \$I->loginAsAdmin();
        \$I->amOnAdminPage('/themes.php');

        \$I->seeElement('.theme.active[data-slug="{{basename}}"]');
    }
}

EOT
            ,
            [
                'basename' => Strings::slug($this->basename)
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

    public function test_theme_active(): void
    {
        \$this->assertTrue(wp_get_theme()->stylesheet === '{{stylesheet}}');
    }
}
EOT
            ,
            [
                'stylesheet' => $this->getActivationString()
            ]
        );

        if (!file_put_contents($this->workDir . '/tests/Integration/SampleTest.php', $testCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/Integration/SampleTest.php.');
        }
    }
}
