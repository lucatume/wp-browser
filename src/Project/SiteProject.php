<?php

namespace lucatume\WPBrowser\Project;

use Codeception\InitTemplate;
use lucatume\WPBrowser\Command\ChromedriverUpdate;
use lucatume\WPBrowser\Command\DevInfo;
use lucatume\WPBrowser\Command\DevRestart;
use lucatume\WPBrowser\Command\DevStart;
use lucatume\WPBrowser\Command\DevStop;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Utils\ChromedriverInstaller;
use lucatume\WPBrowser\Utils\Codeception;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class SiteProject extends InitTemplate implements ProjectInterface
{
    /**
     * @var string
     */
    protected $workDir;
    /**
     * @var \lucatume\WPBrowser\WordPress\Installation
     */
    private $installation;
    /**
     * @var \lucatume\WPBrowser\Project\TestEnvironment
     */
    private $testEnvironment;

    /**
     * @throws RuntimeException
     */
    public function __construct(InputInterface $input, OutputInterface $output, string $workDir)
    {
        $this->workDir = $workDir;
        parent::__construct($input, $output);

        try {
            $this->installation = Installation::findInDir($this->workDir, false);
            $installationState = $this->installation->getState();
        } catch (Throwable $t) {
            throw new RuntimeException(
                'Failed to initialize the WordPress installation: ' . lcfirst($t->getMessage()),
                0,
                $t
            );
        }

        $suggest = "Make sure you're initializing wp-browser at the root of your site project,".
            " and that the directory contains the WordPress files and a wp-config.php file.";

        if ($installationState instanceof EmptyDir) {
            throw new RuntimeException(
                "The WordPress installation directory is empty.\n{$suggest}"
            );
        }

        if ($installationState instanceof Scaffolded) {
            throw new RuntimeException(
                "The WordPress installation directory is scaffolded, but not configured.\n{$suggest}"
            );
        }

        $this->testEnvironment = new TestEnvironment();
    }

    public function getType(): string
    {
        return 'site';
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

        $dataDir = Codeception::dataDir($this->workDir);
        $dataDirRelativePath = Codeception::dataDir();

        if (!is_dir($dataDir) && !(mkdir($dataDir, 0777, true) && is_dir($dataDir))) {
            throw new RuntimeException("Could not create WordPress data directory $dataDir.");
        }

        $contentDir = $this->installation->getContentDir();
        Installation::placeSqliteMuPlugin($this->installation->getMuPluginsDir(), $contentDir);

        $this->sayInfo('SQLite drop-in placed, installing WordPress ...');
        $serverLocalhostPort = Random::openLocalhostPort();
        $tablePrefix = $this->installation->getState()->getTablePrefix();
        $db = new SQLiteDatabase($dataDir, 'db.sqlite', $tablePrefix);
        $this->installation->setDb($db);
        $this->installation->install(
            "http://localhost:$serverLocalhostPort",
            'admin',
            'password',
            'admin@exmaple.com',
            ucwords($this->getName()) . ' Test'
        );

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
            ]

        ];
        $this->testEnvironment->customCommands[] = DevStart::class;
        $this->testEnvironment->customCommands[] = DevStop::class;
        $this->testEnvironment->customCommands[] = DevInfo::class;
        $this->testEnvironment->customCommands[] = DevRestart::class;
        $this->testEnvironment->customCommands[] = ChromedriverUpdate::class;
        $this->testEnvironment->wpRootDir = '.';
        $this->testEnvironment->dbUrl = 'sqlite://' . implode(
            DIRECTORY_SEPARATOR,
            ['%codecept_root_dir%', $dataDirRelativePath, 'db.sqlite']
        );

        $this->testEnvironment->afterSuccess = function (): void {
            $this->scaffoldEndToEndActivationCest();
            $this->scaffoldIntegrationActivationTest();
        };
    }

    public function getTestEnv(): ?TestEnvironment
    {
        return $this->testEnvironment;
    }

    private function getName(): string
    {
        return basename(dirname($this->workDir));
    }

    private function scaffoldEndToEndActivationCest(): void
    {
        $cestCode = <<< EOT
<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class ActivationCest
{
    public function test_homepage_works(EndToEndTester \$I): void
    {
        \$I->amOnPage('/');
        \$I->seeElement('body');
    }
    
    public function test_can_login_as_admin(EndToEndTester \$I): void
    {
        \$I->loginAsAdmin();
        \$I->amOnAdminPage('/');
        \$I->seeElement('body.wp-admin');
    }
}

EOT;

        if (!file_put_contents($this->workDir . '/tests/EndToEnd/ActivationCest.php', $cestCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/EndToEnd/ActivationCest.php.');
        }
    }

    private function scaffoldIntegrationActivationTest(): void
    {
        $testCode = <<< EOT
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

    public function test_default_users(): void
    {
        \$this->assertEquals(0, wp_get_current_user()->ID);
        
        wp_set_current_user(1);
        
        \$this->assertEquals(1, wp_get_current_user()->ID);
        \$this->assertEquals('admin', wp_get_current_user()->user_login);
    }
}
EOT;

        if (!file_put_contents($this->workDir . '/tests/Integration/SampleTest.php', $testCode, LOCK_EX)) {
            throw new RuntimeException('Could not write tests/Integration/SampleTest.php.');
        }
    }
}
