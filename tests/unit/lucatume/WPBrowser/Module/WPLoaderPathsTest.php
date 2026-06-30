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
class WPLoaderPathsTest extends \Codeception\Test\Unit
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
     * It should allow specifying the wpRootFolder as a relative path to cwd or abspath
     *
     * @test
     * @group slow
     */
    public function should_allow_specifying_the_wp_root_folder_as_a_relative_path_to_cwd_or_abspath(): void
    {
        $rootDir = FS::tmpDir('wploader_', ['test' => ['wordpress' => []]], 0777);
        $this->fastScaffold($rootDir . '/test/wordpress', '6.1.1');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => 'test/wordpress',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader1 = $this->module();
        $this->assertInIsolation(static function () use ($rootDir, $wpLoader1) {
            chdir($rootDir);
            $wpLoader1->_initialize();
            Assert::assertEquals($rootDir . '/test/wordpress/', $wpLoader1->_getConfig('wpRootFolder'));
            Assert::assertEquals($rootDir . '/test/wordpress/', $wpLoader1->getWpRootFolder());
        }, $rootDir);

        $this->config = [
            'wpRootFolder' => $rootDir . '/test/wordpress',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader2 = $this->module();

        $this->assertInIsolation(static function () use ($rootDir, $wpLoader2) {
            chdir($rootDir);
            $wpLoader2->_initialize();
            Assert::assertEquals($rootDir . '/test/wordpress/', $wpLoader2->_getConfig('wpRootFolder'));
            Assert::assertEquals($rootDir . '/test/wordpress/', $wpLoader2->getWpRootFolder());
        }, $rootDir);
    }


    /**
     * It should allow specifying the wpRootFolder including the home symbol
     *
     * @test
     * @group slow
     */
    public function should_allow_specifying_the_wp_root_folder_including_the_home_symbol(): void
    {
        $homeDir = FS::tmpDir('home_', ['projects' => ['work' => ['acme' => ['wordpress' => []]]]]);
        $wpRootDir = $homeDir . '/projects/work/acme/wordpress';
        $this->fastScaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => '~/projects/work/acme/wordpress',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader, $homeDir) {
            putenv('HOME=' . $homeDir);
            $_SERVER['HOME'] = $homeDir;
            $wpLoader->_initialize();

            Assert::assertEquals($homeDir . '/projects/work/acme/wordpress/', $wpLoader->_getConfig('wpRootFolder'));
            Assert::assertEquals($homeDir . '/projects/work/acme/wordpress/', $wpLoader->getWpRootFolder());
        });
    }


    /**
     * It should allow specifying the wpRootFolder as an absolute path
     *
     * @test
     * @group slow
     */
    public function should_allow_specifying_the_wp_root_folder_as_an_absolute_path(): void
    {
        $wpRootDir = FS::tmpDir();
        $this->fastScaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals($wpRootDir . '/', $wpLoader->_getConfig('wpRootFolder'));
            Assert::assertEquals($wpRootDir . '/', $wpLoader->getWpRootFolder());
        });
    }


    /**
     * It should allow specifying the wpRootFolder as absolute path with escaped spaces
     *
     * @test
     * @group slow
     */
    public function should_allow_specifying_the_wp_root_folder_as_absolute_path_with_escaped_spaces(): void
    {
        $wpRootDir = FS::tmpDir('wploader_', ['Word Press' => []]);
        $this->fastScaffold($wpRootDir . '/Word Press', '6.1.1');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => $wpRootDir . '/Word\ Press',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals($wpRootDir . '/Word Press/', $wpLoader->_getConfig('wpRootFolder'));
            Assert::assertEquals($wpRootDir . '/Word Press/', $wpLoader->getWpRootFolder());
        });
    }


    /**
     * It should scaffold the installation if the wpRootFolder is empty
     *
     * @test
     * @group slow
     */
    public function should_scaffold_the_installation_if_the_wp_root_folder_is_empty(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            Assert::assertInstanceOf(Scaffolded::class, $wpLoader->getInstallation()->getState());
        });
    }


    /**
     * It should allow getting paths from the wpRootFolder
     *
     * @test
     * @group slow
     */
    public function should_allow_getting_paths_from_the_wp_root_folder(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals($wpRootDir . '/foo-bar', $wpLoader->getWpRootFolder('foo-bar'));
            Assert::assertEquals($wpRootDir . '/foo-bar/baz', $wpLoader->getWpRootFolder('foo-bar/baz'));
            Assert::assertEquals($wpRootDir . '/wp-config.php', $wpLoader->getWpRootFolder('wp-config.php'));
        });
    }


    /**
     * It should read salts from configured installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_read_salts_from_configured_installation(): void
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
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $installation = $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
            $installation = $wpLoader->getInstallation();

            Assert::assertEquals($installation->getAuthKey(), $wpLoader->_getConfig('AUTH_KEY'));
            Assert::assertEquals($installation->getSecureAuthKey(), $wpLoader->_getConfig('SECURE_AUTH_KEY'));
            Assert::assertEquals($installation->getLoggedInKey(), $wpLoader->_getConfig('LOGGED_IN_KEY'));
            Assert::assertEquals($installation->getNonceKey(), $wpLoader->_getConfig('NONCE_KEY'));
            Assert::assertEquals($installation->getAuthSalt(), $wpLoader->_getConfig('AUTH_SALT'));
            Assert::assertEquals($installation->getSecureAuthSalt(), $wpLoader->_getConfig('SECURE_AUTH_SALT'));
            Assert::assertEquals($installation->getLoggedInSalt(), $wpLoader->_getConfig('LOGGED_IN_SALT'));
            Assert::assertEquals($installation->getNonceSalt(), $wpLoader->_getConfig('NONCE_SALT'));
        });
    }


    /**
     * It should set some default values for salt keys
     *
     * @test
     * @group slow
     */
    public function should_set_some_default_values_for_salt_keys(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            $var = [
                'AUTH_KEY',
                'SECURE_AUTH_KEY',
                'LOGGED_IN_KEY',
                'NONCE_KEY',
                'AUTH_SALT',
                'SECURE_AUTH_SALT',
                'LOGGED_IN_SALT',
                'NONCE_SALT',
            ];
            foreach ($var as $i => $key) {
                if ($i > 0) {
                    Assert::assertNotEquals($var[$i - 1], $wpLoader->_getConfig($key));
                }
                Assert::assertEquals(64, strlen($wpLoader->_getConfig($key)));
            }
        });
    }


    /**
     * It should load config files if set
     *
     * @test
     * @group slow
     */
    public function should_load_config_files_if_set(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'configFile' => codecept_data_dir('files/test_file_001.php')
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
            Assert::assertEquals('test_file_001.php', getenv('LOADED'));
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'configFile' =>
                [
                    codecept_data_dir('files/test_file_002.php'),
                    codecept_data_dir('files/test_file_003.php'),
                ]
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
            Assert::assertEquals(getenv('LOADED_2'), 'test_file_002.php');
            Assert::assertEquals(getenv('LOADED_3'), 'test_file_003.php');
        });
    }


    /**
     * It should support using dbUrl to set up module
     *
     * @test
     * @group slow
     */
    public function should_support_using_db_url_to_set_up_module(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->fastScaffold($wpRootDir);
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => 'mysql://User:secret!@127.0.0.1:2389/test_db',
        ];

        $wploader = $this->module();

        $this->assertEquals('test_db', $wploader->_getConfig('dbName'));
        $this->assertEquals('127.0.0.1:2389', $wploader->_getConfig('dbHost'));
        $this->assertEquals('User', $wploader->_getConfig('dbUser'));
        $this->assertEquals('secret!', $wploader->_getConfig('dbPassword'));
    }


    /**
     * It should load WordPress before suite if loadOnly w/ config files
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_load_word_press_before_suite_if_load_only_w_config_files(): void
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
            'loadOnly' => true,
            'domain' => 'wordpress.test',
            'configFile' => codecept_data_dir('files/test_file_002.php'),
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals('', getenv('LOADED_2'));
            Assert::assertFalse(defined('ABSPATH'));

            $actions = [];
            Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_LOADONLY, static function () use (&$actions) {
                $actions[] = \lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_LOADONLY;
            });
            Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_LOADONLY, static function () use (&$actions) {
                $actions[] = \lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_LOADONLY;
            });

            $wpLoader->_loadWordPress();

            Assert::assertEquals('test_file_002.php', getenv('LOADED_2'));
            Assert::assertEquals($wpRootDir . '/', ABSPATH);
            Assert::assertEquals([
                \lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_LOADONLY,
                \lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_LOADONLY,
            ], $actions);
            Assert::assertInstanceOf(FactoryStore::class, $wpLoader->factory());
        });
    }


    /**
     * It should create the database if it does not exist
     *
     * @test
     * @group slow
     */
    public function should_create_the_database_if_it_does_not_exist(): void
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
        ];

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($dbName, $dbPassword, $dbUser, $dbHost, $wpLoader) {
            $wpLoader->_initialize();

            self::assertDatabaseExists($dbHost, $dbUser, $dbPassword, $dbName);
        });
    }

}
