<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use Codeception\Util\Debug;
use Exception;
use Generator;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader\FactoryStore;
use lucatume\WPBrowser\Tests\FSTemplates\BedrockProject;
use lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
use lucatume\WPBrowser\Tests\Traits\FastScaffold;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
use lucatume\WPBrowser\Tests\Traits\PhaseTimer;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Assert as WPAssert;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Version as PHPUnitVersion;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use stdClass;
use Symfony\Component\VarDumper\VarDumper;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use UnitTester;
use WP_Theme;
use lucatume\WPBrowser\Tests\Traits\WPLoaderSetup;

use const ABSPATH;
use const WP_DEBUG;

/**
 * @group isolated-1
 */
class WPLoaderDbDumpsSqliteBedrockTest extends \Codeception\Test\Unit
{
    use \tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    use \lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
    use \lucatume\WPBrowser\Tests\Traits\LoopIsolation;
    use \lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
    use \lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
    use \lucatume\WPBrowser\Tests\Traits\PhaseTimer;
    use \lucatume\WPBrowser\Tests\Traits\FastScaffold;
    use \lucatume\WPBrowser\Tests\Traits\WPLoaderSetup;

    protected $backupGlobals = false;

    /** @var \UnitTester */
    protected $tester;


    /**
     * It should allow loading a database dump before tests
     *
     * @test
     * @group slow
     */
    public function should_allow_loading_a_database_dump_before_tests(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => $dbHost,
            'dbUser' => $dbUser,
            'dbPassword' => $dbPassword,
            'dump' => codecept_data_dir('files/test-dump-001.sql')
        ];
        $this->fastScaffold($wpRootDir);

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals('value_1', get_option('option_1'));
        });
    }


    /**
     * It should allow loading multiple database dumps before the tests
     *
     * @test
     * @group slow
     */
    public function should_allow_loading_multiple_database_dumps_before_the_tests(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => $dbHost,
            'dbUser' => $dbUser,
            'dbPassword' => $dbPassword,
            'dump' => [
                codecept_data_dir('files/test-dump-001.sql'),
                codecept_data_dir('files/test-dump-002.sql'),
                codecept_data_dir('files/test-dump-003.sql'),
            ]
        ];
        $this->fastScaffold($wpRootDir);

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals('value_1', get_option('option_1'));
            Assert::assertEquals('value_2', get_option('option_2'));
            Assert::assertEquals('value_3', get_option('option_3'));
        });
    }


    /**
     * It should place SQLite dropin if using SQLite database for tests
     *
     * @test
     * @group sqlite
     * @group slow
     */
    public function should_place_sq_lite_dropin_if_using_sq_lite_database_for_tests(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->fastScaffold($wpRootDir);
        $dbPathname = $wpRootDir . '/db.sqlite';

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => 'sqlite://' . $dbPathname,
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();
            Assert::assertFileExists($wpRootDir . '/wp-content/db.php');
        });
    }


    /**
     * It should initialize correctly with Sqlite database
     *
     * @test
     * @group sqlite
     * @group slow
     */
    public function should_initialize_correctly_with_sqlite_database(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->fastScaffold($wpRootDir);
        $dbPathname = $wpRootDir . '/db.sqlite';
        Installation::placeSqliteMuPlugin($wpRootDir . '/wp-content/mu-plugins', $wpRootDir . '/wp-content');

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => 'sqlite://' . $dbPathname,
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));
            Assert::assertInstanceOf(\WP_User::class, wp_get_current_user());
        });
    }


    /**
     * It should initialize correctly with Sqlite database in loadOnly mode
     *
     * @test
     * @group sqlite
     * @group slow
     */
    public function should_initialize_correctly_with_sqlite_database_in_load_only_mode(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        Installation::placeSqliteMuPlugin($wpRootDir . '/wp-content/mu-plugins', $wpRootDir . '/wp-content');
        $dbPathname = $wpRootDir . '/db.sqlite';
        $installation->configure(new SQLiteDatabase($wpRootDir, 'db.sqlite'));
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => 'sqlite://' . $dbPathname,
            'loadOnly' => true,
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
            $wpLoader->_loadWordPress();

            Assert::assertTrue(function_exists('do_action'));
            Assert::assertInstanceOf(\WP_User::class, wp_get_current_user());
        });
    }


    /**
     * It should correctly load the module on a Bedrock installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_correctly_load_the_module_on_a_bedrock_installation(): void
    {
        if (PHP_VERSION < 8.0) {
            $this->markTestSkipped();
        }
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $wpRootDir = (new BedrockProject($db, 'https://the-project.local'))->scaffold($wpRootDir);

        $setupInstallation = new Installation($wpRootDir . '/web/wp');
        $this->assertEquals($wpRootDir . '/web/app/plugins', $setupInstallation->getPluginsDir());

        // Scaffold 1 plugins to activate.
        $pluginsDir = $wpRootDir . '/web/app/plugins';

        FS::mkdirp($pluginsDir, [
            'plugin-1' => [
                'plugin.php' => <<< PHP
<?php
/** Plugin Name: Plugin 1 */
function plugin_1_canary() {}
PHP
            ],
            'plugin-2' => [
                'plugin.php' => <<< PHP
<?php
/** Plugin Name: Plugin 2 */
function plugin_2_canary() {}
PHP
            ]
        ]);

        // Scaffold the theme to activate.
        $themesDir = $wpRootDir . '/web/app/themes';
        FS::mkdirp($themesDir, [
            'theme-1' => [
                'style.css' => '/** Theme Name: Theme 1 */',
                'index.php' => '<?php echo "Hello World"; ?>'
            ]
        ]);

        $this->config = [
            'wpRootFolder' => $wpRootDir . '/web/wp',
            'tablePrefix' => 'test_',
            'dbUrl' => "mysql://$dbUser:$dbPassword@$dbHost/$dbName",
            'plugins' => [
                'plugin-1/plugin.php',
                'plugin-2/plugin.php',
            ],
            'theme' => 'theme-1'
        ];

        // @todo test content

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));
            Assert::assertTrue(function_exists('plugin_1_canary'));
            Assert::assertTrue(is_plugin_active('plugin-1/plugin.php'));
            Assert::assertTrue(function_exists('plugin_2_canary'));
            Assert::assertTrue(is_plugin_active('plugin-2/plugin.php'));
            Assert::assertEquals('theme-1', wp_get_theme()->get_stylesheet());
        });
    }


    public function differentDbNamesProvider(): array
    {
        return [
            'with dashes, underscores and dots' => ['test-db_db.db'],
            'only words and numbers' => ['testdb1234567890'],
            'all together' => ['test-db_db.db1234567890'],
            'mydatabase.dev' => ['mydatabase.dev'],
            'my_dbname_n8h96prxar4r' => ['my_dbname_n8h96prxar4r'],
            '!funny~db-name' => ['!funny~db-name'],
        ];
    }


    /**
     * It should correctly load with different database names
     *
     * @test
     * @dataProvider differentDbNamesProvider
     * @group slow
     * @group requires-mysql-server
     */
    public function should_correctly_load_with_different_database_names(string $dbName): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $db->drop();
        $installation->configure($db);
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl()
        ];
        $wpLoader = $this->module();

        $this->assertEquals(
            $db->getDbName(),
            $this->assertInIsolation(static function () use ($wpLoader) {
                $wpLoader->_initialize();

                Assert::assertTrue(function_exists('do_action'));
                Assert::assertInstanceOf(\WP_User::class, wp_get_current_user());

                return $wpLoader->getInstallation()->getDb()->getDbName();
            })
        );
    }


    /**
     * It should not backup globals by default
     *
     * @test
     * @group backup-globals
     * @group slow
     * @group requires-mysql-server
     */
    public function should_not_backup_globals_by_default(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $db->drop();
        $installation->configure($db);
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $testcaseFile = codecept_data_dir('files/BackupControlTestCase.php');
        $overridingTestCaseFile = codecept_data_dir('files/BackupControlTestCaseOverridingTestCase.php');

        // Do not set`WPLoader.backupGlobals`, let the default value kick in.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
        ];
        $wpLoader = $this->module();
        $serializedPhpunitConfiguration = (int)PHPUnitVersion::series() >= 10 ?
            serialize(ConfigurationRegistry::get())
            : null;

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile, $serializedPhpunitConfiguration) {
            if ((int)PHPUnitVersion::series() >= 10) {
                $reflector = new \ReflectionClass(ConfigurationRegistry::class);
                $instanceProp = $reflector->getProperty('instance');
                PHP_VERSION_ID < 80100 && $instanceProp->setAccessible(true);
                $instanceProp->setValue(unserialize($serializedPhpunitConfiguration));
            }

            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsFalse');
            if ((int)PHPUnitVersion::series() >= 10) {
                $testCase->run();
                $status = $testCase->status();
                Assert::assertTrue($status->isSuccess());
            } else {
                $result = $testCase->run();
                Assert::assertTrue($result->wasSuccessful());
            }
        });
    }

}
