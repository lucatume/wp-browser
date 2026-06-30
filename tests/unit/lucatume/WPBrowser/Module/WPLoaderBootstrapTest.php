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
class WPLoaderBootstrapTest extends \Codeception\Test\Unit
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


    public function singleSiteAndMultisite(): array
    {
        return [
            'single site' => [false],
            'multisite' => [true],
        ];
    }


    public function dbModuleCompatDataProvider(): Generator
    {
        yield 'MysqlDatabase' => ['MysqlDatabase', MysqlDatabase::class];
        yield 'WPDb' => ['WPDb', WPDb::class];
        yield WPDb::class => [WPDb::class, WPDb::class];
    }


    /**
     * It should install and bootstrap single site using constants' names
     *
     * @test
     * @group slow
     */
    public function should_should_install_and_bootstrap_single_site_using_constants_names(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $this->config = [
            'ABSPATH' => $wpRootDir,
            'DB_NAME' => $dbName,
            'DB_HOST' => $dbHost,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
            'configFile' => [
                codecept_data_dir('files/test_file_001.php'),
                codecept_data_dir('files/test_file_002.php'),
            ],
        ];
        $this->fastScaffold($wpRootDir, 'latest');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            Assert::assertEquals('test_file_001.php', getenv('LOADED'));
            Assert::assertEquals('test_file_002.php', getenv('LOADED_2'));
            Assert::assertEquals($wpRootDir . '/', ABSPATH);
            Assert::assertTrue(defined('WP_DEBUG'));
            Assert::assertTrue(WP_DEBUG);
        });
    }


    /**
     * It should install and bootstrap single installation
     *
     * @test
     * @group slow
     */
    public function should_install_and_bootstrap_single_installation(): void
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
            'configFile' => [
                codecept_data_dir('files/test_file_001.php'),
                codecept_data_dir('files/test_file_002.php'),
            ],
            'plugins' => [
                'akismet/akismet.php',
                'hello-dolly/hello.php'
            ],
            'theme' => 'twentytwenty',
        ];
        if (PHP_VERSION >= 7.4) {
            // WooCommerce has a minimum PHP version of 7.4.0 required.
            $this->config['plugins'][] = 'woocommerce/woocommerce.php';
        }
        $installation = $this->fastScaffold($wpRootDir, 'latest');
        $this->copyOverContentFromTheMainInstallation($installation);

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $actions = [];
            Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_INSTALL, function () use (&$actions) {
                $actions[] = 'before_install';
            });
            Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_INSTALL, function () use (&$actions) {
                $actions[] = 'after_install';
            });

            $wpLoader->_initialize();

            $expectedActivePlugins = [
                'akismet/akismet.php',
                'hello-dolly/hello.php'
            ];
            if (PHP_VERSION >= 7.4) {
                $expectedActivePlugins[] = 'woocommerce/woocommerce.php';
            }
            Assert::assertEquals($expectedActivePlugins, get_option('active_plugins'));
            Assert::assertEquals([
                'before_install',
                'after_install',
            ], $actions);
            Assert::assertEquals('twentytwenty', get_option('template'));
            Assert::assertEquals('twentytwenty', get_option('stylesheet'));
            Assert::assertEquals('test_file_001.php', getenv('LOADED'));
            Assert::assertEquals('test_file_002.php', getenv('LOADED_2'));
            Assert::assertEquals($wpRootDir . '/', ABSPATH);
            Assert::assertTrue(defined('WP_DEBUG'));
            Assert::assertTrue(WP_DEBUG);
            Assert::assertInstanceOf(\wpdb::class, $GLOBALS['wpdb']);
            Assert::assertFalse(is_multisite());
            Assert::assertEquals($wpRootDir . '/wp-content/', $wpLoader->getContentFolder());
            Assert::assertEquals($wpRootDir . '/wp-content/some/path', $wpLoader->getContentFolder('some/path'));
            Assert::assertEquals(
                $wpRootDir . '/wp-content/some/path/some-file.php',
                $wpLoader->getContentFolder('some/path/some-file.php')
            );
            Assert::assertEquals(
                $wpRootDir . '/wp-content/plugins/some-file.php',
                $wpLoader->getPluginsFolder('/some-file.php')
            );
            Assert::assertEquals(
                $wpRootDir . '/wp-content/themes/some-file.php',
                $wpLoader->getThemesFolder('/some-file.php')
            );
            WPAssert::assertTableExists('posts');
            if (PHP_VERSION >= 7.4) {
                WPAssert::assertTableExists('woocommerce_order_items');
            }
            WPAssert::assertUpdatesDisabled();
        });
    }


    /**
     * It should install and bootstrap multisite installation
     *
     * @test
     * @group slow
     */
    public function should_install_and_bootstrap_multisite_installation(): void
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
            'configFile' => [
                codecept_data_dir('files/test_file_001.php'),
                codecept_data_dir('files/test_file_002.php'),
            ],
            'plugins' => [
                'akismet/akismet.php',
                'hello-dolly/hello.php'
            ],
            'theme' => 'twentytwenty',
            'multisite' => true,
        ];
        if (PHP_VERSION >= 7.4) {
            // WooCommerce has a minimum PHP version of 7.4.0 required.
            $this->config['plugins'][] = 'woocommerce/woocommerce.php';
        }
        $installation = $this->fastScaffold($wpRootDir, 'latest');
        $this->copyOverContentFromTheMainInstallation($installation);

        $wpLoader = $this->module();
        $installationOutput = $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $actions = [];
            Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_INSTALL, function () use (&$actions) {
                $actions[] = 'before_install';
            });
            Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_INSTALL, function () use (&$actions) {
                $actions[] = 'after_install';
            });

            $wpLoader->_initialize();

            $expectedActivePlugins = [
                'akismet/akismet.php',
                'hello-dolly/hello.php'
            ];
            if (PHP_VERSION >= 7.4) {
                $expectedActivePlugins[] = 'woocommerce/woocommerce.php';
            }
            Assert::assertEquals($expectedActivePlugins, array_keys(get_site_option('active_sitewide_plugins')));
            Assert::assertEquals([
                'before_install',
                'after_install',
            ], $actions);
            Assert::assertEquals('twentytwenty', get_option('template'));
            Assert::assertEquals('twentytwenty', get_option('stylesheet'));
            Assert::assertEquals(['twentytwenty' => true], WP_Theme::get_allowed());
            Assert::assertEquals('test_file_001.php', getenv('LOADED'));
            Assert::assertEquals('test_file_002.php', getenv('LOADED_2'));
            Assert::assertEquals($wpRootDir . '/', ABSPATH);
            Assert::assertTrue(defined('WP_DEBUG'));
            Assert::assertTrue(WP_DEBUG);
            Assert::assertInstanceOf(\wpdb::class, $GLOBALS['wpdb']);
            Assert::assertTrue(is_multisite());
            Assert::assertEquals($wpRootDir . '/wp-content/', $wpLoader->getContentFolder());
            Assert::assertEquals($wpRootDir . '/wp-content/some/path', $wpLoader->getContentFolder('some/path'));
            Assert::assertEquals(
                $wpRootDir . '/wp-content/some/path/some-file.php',
                $wpLoader->getContentFolder('some/path/some-file.php')
            );
            Assert::assertEquals(
                $wpRootDir . '/wp-content/plugins/some-file.php',
                $wpLoader->getPluginsFolder('/some-file.php')
            );
            Assert::assertEquals(
                $wpRootDir . '/wp-content/themes/some-file.php',
                $wpLoader->getThemesFolder('/some-file.php')
            );
            WPAssert::assertTableExists('posts');
            if (PHP_VERSION >= 7.4) {
                WPAssert::assertTableExists('woocommerce_order_items');
            }
            WPAssert::assertUpdatesDisabled();

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        });
    }


    /**
     * It should not throw when loadOnly true and using DB module
     *
     * @test
     * @dataProvider dbModuleCompatDataProvider
     * @group slow
     * @group requires-mysql-server
     */
    public function should_not_throw_when_load_only_true_and_using_db_module(
        string $dbModuleName,
        string $dbModuleClass
    ): void {
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
            'configFile' => codecept_data_dir('files/test_file_002.php')
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
        $mockDbModule = $this->createMock($dbModuleClass);
        $this->mockModuleContainer->mock($dbModuleName, $mockDbModule);

        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            $wpLoader->_loadWordPress();

            Assert::assertEquals($wpRootDir . '/', ABSPATH);
        });
    }


    /**
     * It should throw if using with WPDb and not loadOnly
     *
     * @test
     * @dataProvider dbModuleCompatDataProvider
     * @group slow
     */
    public function should_throw_if_using_with_wp_db_and_not_load_only(
        string $dbModuleName,
        string $dbModuleClass
    ): void {
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
        $mockDbModule = $this->createMock($dbModuleClass);
        $this->mockModuleContainer->mock($dbModuleName, $mockDbModule);

        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessageRegExp(
            '/The WPLoader module is not being used to only load ' .
            'WordPress, but to also install it/'
        );

        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();
        });
    }


    /**
     * It should not throw if using db module and loadOnly true
     *
     * @test
     * @dataProvider dbModuleCompatDataProvider
     * @group slow
     * @group requires-mysql-server
     */
    public function should_not_throw_if_using_db_module_and_load_only_true(
        string $dbModuleName,
        string $dbModuleClass
    ): void {
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
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db);

        $wpLoader = $this->module();
        $mockDbModule = $this->createMock($dbModuleClass);
        $this->mockModuleContainer->mock($dbModuleName, $mockDbModule);

        $ok = $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            return true;
        });

        $this->assertTrue($ok);
    }


    /**
     * It should skip installation when skipInstall is true
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_skip_installation_when_skip_install_is_true(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $installationDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $installation->configure($installationDb);
        $this->copyOverContentFromTheMainInstallation($installation, [
            'plugins' => [
                'woocommerce'
            ]
        ]);
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $installationDb->getDbUrl(),
            'tablePrefix' => 'test_',
            'skipInstall' => true,
            'plugins' => ['woocommerce/woocommerce.php'],
            'theme' => 'twentytwenty'
        ];

        // Run the module a first time: it should create the flag file indicating the database was installed.
        $wpLoader = $this->module();
        $moduleSplObjectHash = spl_object_hash($wpLoader);
        $this->assertInIsolation(
            static function () use ($wpLoader, $moduleSplObjectHash) {
                $beforeInstallCalled = false;
                $afterInstallCalled = false;
                $afterBootstrapCalled = false;
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_INSTALL, function () use (&$beforeInstallCalled) {
                    $beforeInstallCalled = true;
                });
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_INSTALL, function () use (&$afterInstallCalled) {
                    $afterInstallCalled = true;
                });
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_BOOTSTRAP, function () use (&$afterBootstrapCalled) {
                    $afterBootstrapCalled = true;
                });

                $wpLoader->_initialize();

                // Check the value directly in the database to skip the `pre_option_` filter.
                global $wpdb;
                $activePlugins = $wpdb->get_var(
                    "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins'"
                );
                Assert::assertEquals(['woocommerce/woocommerce.php'], unserialize($activePlugins));
                Assert::assertNotEquals('1', getenv('WP_TESTS_SKIP_INSTALL'));
                Assert::assertTrue(function_exists('do_action'));
                Assert::assertTrue($beforeInstallCalled);
                Assert::assertTrue($afterInstallCalled);
                Assert::assertTrue($afterBootstrapCalled);
                Assert::assertTrue(function_exists('wc_get_product'));
                Assert::assertEquals('twentytwenty', wp_get_theme()->get_stylesheet());

                // Set a canary value.
                update_option('canary', $moduleSplObjectHash);
            }
        );

        $checkDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $checkDb->useDb($dbName);
        $this->assertEquals(
            ['woocommerce/woocommerce.php'],
            $checkDb->getOption('active_plugins'),
            'After the first run, WordPress should be installed and the plugins activated.'
        );
        $this->assertEquals('twentytwenty', $checkDb->getOption('stylesheet'));

        // Run a second time, this time the installation should be skipped.
        $wpLoader = $this->module();
        $this->assertInIsolation(
            static function () use ($moduleSplObjectHash, $wpLoader) {
                $beforeInstallCalled = false;
                $afterInstallCalled = false;
                $afterBootstrapCalled = false;
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_INSTALL, function () use (&$beforeInstallCalled) {
                    $beforeInstallCalled = true;
                });
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_INSTALL, function () use (&$afterInstallCalled) {
                    $afterInstallCalled = true;
                });
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_BOOTSTRAP, function () use (&$afterBootstrapCalled) {
                    $afterBootstrapCalled = true;
                });

                $wpLoader->_initialize();

                // Check the value directly in the database to skip the `pre_option_` filter.
                global $wpdb;
                $activePlugins = $wpdb->get_var(
                    "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins'"
                );
                Assert::assertEquals(['woocommerce/woocommerce.php'], unserialize($activePlugins));
                Assert::assertEquals('1', getenv('WP_TESTS_SKIP_INSTALL'));
                Assert::assertTrue(function_exists('do_action'));
                Assert::assertTrue($beforeInstallCalled);
                Assert::assertTrue($afterInstallCalled);
                Assert::assertTrue($afterBootstrapCalled);
                Assert::assertTrue(function_exists('wc_get_product'));
                Assert::assertEquals('twentytwenty', wp_get_theme()->get_stylesheet());
                Assert::assertEquals($moduleSplObjectHash, get_option('canary'));
            }
        );

        // Now run in --debug mode, the installation should run again.
        $wpLoader = $this->module();
        $this->assertInIsolation(
            static function () use ($wpLoader) {
                $beforeInstallCalled = false;
                $afterInstallCalled = false;
                $afterBootstrapCalled = false;
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_BEFORE_INSTALL, function () use (&$beforeInstallCalled) {
                    $beforeInstallCalled = true;
                });
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_INSTALL, function () use (&$afterInstallCalled) {
                    $afterInstallCalled = true;
                });
                Dispatcher::addListener(\lucatume\WPBrowser\Module\WPLoader::EVENT_AFTER_BOOTSTRAP, function () use (&$afterBootstrapCalled) {
                    $afterBootstrapCalled = true;
                });
                uopz_set_return(Debug::class, 'isEnabled', true);

                $wpLoader->_initialize();

                // Check the value directly in the database to skip the `pre_option_` filter.
                global $wpdb;
                $activePlugins = $wpdb->get_var(
                    "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins'"
                );
                Assert::assertEquals(['woocommerce/woocommerce.php'], unserialize($activePlugins));
                Assert::assertNotEquals('1', getenv('WP_TESTS_SKIP_INSTALL'));
                Assert::assertTrue(function_exists('do_action'));
                Assert::assertTrue($beforeInstallCalled);
                Assert::assertTrue($afterInstallCalled);
                Assert::assertTrue($afterBootstrapCalled);
                Assert::assertTrue(function_exists('wc_get_product'));
                Assert::assertEquals('twentytwenty', wp_get_theme()->get_stylesheet());
                Assert::assertEquals(
                    '',
                    get_option('canary'),
                    'The value set in the previous installation should be gone.'
                );
            }
        );
    }


    /**
     * It should fail to activate when plugins generate unexpected output
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_fail_to_activate_when_plugins_generate_unexpected_output(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $installationDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $installation->configure($installationDb);
        $this->copyOverContentFromTheMainInstallation($installation, [
            'plugins' => [
                'woocommerce'
            ]
        ]);
        // Create a plugin that will raise a doing_it_wrong error on activation.
        FS::mkdirp($wpRootDir . '/wp-content/plugins', [
            'my-plugin' => [
                'plugin.php' => <<< PHP
<?php
/** Plugin Name: DIW Plugin */

function activate_my_plugin(){
    echo 'Something went wrong';
}

register_activation_hook( __FILE__, 'activate_my_plugin' );
PHP
            ]
        ]);

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $installationDb->getDbUrl(),
            'tablePrefix' => 'test_',
            'plugins' => ['woocommerce/woocommerce.php', 'my-plugin/plugin.php'],
        ];

        // Run a first initialization that should fail due to the doing_it_wrong error.
        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(
            static function () use ($wpLoader) {
                $wpLoader->_initialize();
            }
        );
    }


    /**
     * It should allow activating plugins silently
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_activating_plugins_silently(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = $this->fastScaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $installationDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $installation->configure($installationDb);
        $this->copyOverContentFromTheMainInstallation($installation, [
            'plugins' => [
                'woocommerce'
            ]
        ]);
        // Create a plugin that will raise a doing_it_wrong error on activation.
        FS::mkdirp($wpRootDir . '/wp-content/plugins', [
            'my-plugin' => [
                'plugin.php' => <<< PHP
<?php
/** Plugin Name: DIW Plugin */

function activate_my_plugin(){
    echo 'Something went wrong';
    update_option('my_plugin_activated', '__activated__');
}

register_activation_hook( __FILE__, 'activate_my_plugin' );
update_option('my_plugin_loaded', '__loaded__');
PHP
            ]
        ]);

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $installationDb->getDbUrl(),
            'tablePrefix' => 'test_',
            'plugins' => ['woocommerce/woocommerce.php'],
            'silentlyActivatePlugins' => ['my-plugin/plugin.php'],
        ];

        // Run a first initialization that should fail due to the doing_it_wrong error.
        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader) {
                $wpLoader->_initialize();

                Assert::assertEquals('', get_option('my_plugin_activated'));
                Assert::assertEquals('__loaded__', get_option('my_plugin_loaded'));
            }
        );
    }

}
