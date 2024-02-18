<?php

namespace lucatume\WPBrowser\Module;

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
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
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
use stdClass;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use UnitTester;
use WP_Theme;

use const ABSPATH;
use const WP_DEBUG;

class WPLoaderTest extends Unit
{
    use SnapshotAssertions;
    use DatabaseAssertions;
    use LoopIsolation;
    use TmpFilesCleanup;
    use MainInstallationAccess;

    protected $backupGlobals = false;

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    protected $config;
    /**
     * @var string|null
     */
    private $previousCwd;
    /**
     * @var string|null
     */
    private $homeEnvBackup;
    /**
     * @var string|null
     */
    private $homeServerBackup;
    /**
     * @var \Codeception\Lib\ModuleContainer|null
     */
    private $mockModuleContainer;

    /**
     * @after
     */
    public function restorePaths(): void
    {
        if ($this->previousCwd !== null) {
            chdir($this->previousCwd);
        }
        unset($this->previousCwd);

        if ($this->homeEnvBackup !== null) {
            putenv('HOME=' . $this->homeEnvBackup);
        }
        unset($this->homeEnvBackup);

        if ($this->homeServerBackup !== null) {
            $_SERVER['HOME'] = $this->homeServerBackup;
        }
        unset($this->homeServerBackup);
    }

    /**
     * @after
     */
    public function undefineConstants(): void
    {
        foreach (
            [
                'DB_HOST',
                'DB_NAME',
                'DB_USER',
                'DB_PASSWORD',
            ] as $const
        ) {
            if (defined($const)) {
                uopz_undefine($const);
            }
        }
    }

    /**
     * @after
     */
    public function unsetEnvVars(): void
    {
        foreach (['LOADED', 'LOADED_2', 'LOADED_3'] as $envVar) {
            putenv($envVar);
        }
    }

    /**
     * @return WPLoader
     */
    private function module(array $moduleContainerConfig = [], ?array $moduleConfig = null): WPLoader
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), $moduleContainerConfig);
        return new WPLoader($this->mockModuleContainer, ($moduleConfig ?? $this->config));
    }

    /**
     * It should throw if cannot connect to the database
     *
     * @test
     */
    public function should_throw_if_cannot_connect_to_the_database(): void
    {
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => FS::tmpDir('wploader_'),
            'dbName' => $dbName,
            'dbHost' => 'some-non-existing-db-host',
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module()->_initialize();
    }

    /**
     * It should throw if wpRootFolder is not valid
     *
     * @test
     */
    public function should_throw_if_wp_root_folder_is_not_valid(): void
    {
        $dbName = Random::dbName();
        $this->config = [
            'wpRootFolder' => '/not/a/valid/path',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module()->_initialize();
    }

    /**
     * It should allow specifying the wpRootFolder as a relative path to cwd or abspath
     *
     * @test
     */
    public function should_allow_specifying_the_wp_root_folder_as_a_relative_path_to_cwd_or_abspath(): void
    {
        $rootDir = FS::tmpDir('wploader_', ['test' => ['wordpress' => []]], 0777);
        Installation::scaffold($rootDir . '/test/wordpress', '6.1.1');
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
     */
    public function should_allow_specifying_the_wp_root_folder_including_the_home_symbol(): void
    {
        $homeDir = FS::tmpDir('home_', ['projects' => ['work' => ['acme' => ['wordpress' => []]]]]);
        $wpRootDir = $homeDir . '/projects/work/acme/wordpress';
        Installation::scaffold($wpRootDir, '6.1.1');
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
     */
    public function should_allow_specifying_the_wp_root_folder_as_an_absolute_path(): void
    {
        $wpRootDir = FS::tmpDir();
        Installation::scaffold($wpRootDir, '6.1.1');
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
     */
    public function should_allow_specifying_the_wp_root_folder_as_absolute_path_with_escaped_spaces(): void
    {
        $wpRootDir = FS::tmpDir('wploader_', ['Word Press' => []]);
        Installation::scaffold($wpRootDir . '/Word Press', '6.1.1');
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
     * It should read salts from configured installation
     *
     * @test
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
        $installation = Installation::scaffold($wpRootDir, '6.1.1')
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
     * It should allow getting paths from the wpRootFolder
     *
     * @test
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
     * It should set some default values for salt keys
     *
     * @test
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
     * It should throw if configFiles do not exist
     *
     * @test
     */
    public function should_throw_if_config_files_do_not_exist(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'configFile' => __DIR__ . '/nonexistent.php'
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $captured = false;
            try {
                $wpLoader->_initialize();
            } catch (Exception $e) {
                Assert::assertInstanceOf(ModuleConfigException::class, $e);
                $captured = true;
            }
            Assert::assertTrue($captured);
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'configFile' => [
                codecept_data_dir('files/test_file_002.php'),
                __DIR__ . '/nonexistent.php'
            ]
        ];

        $wpLoader = $this->module();

        $this->expectException(ModuleConfigException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if loadOnly and installation empty
     *
     * @test
     */
    public function should_throw_if_load_only_and_installation_empty(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'loadOnly' => true
        ];

        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if loadOnly and installation scaffolded
     *
     * @test
     */
    public function should_throw_if_load_only_and_installation_scaffolded(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        Installation::scaffold($wpRootDir, '6.1.1');

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'loadOnly' => true
        ];

        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if loadOnly and domain empty
     *
     * @test
     */
    public function should_throw_if_load_only_and_domain_empty(): void
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
            'domain' => ''
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $wpLoader->_initialize();
    }

    /**
     * It should throw if loadOnly and WordPress not installed
     *
     * @test
     */
    public function should_throw_if_load_only_and_word_press_not_installed(): void
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
            'domain' => 'wordpress.test'
        ];
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $wpLoader = $this->module();

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(InstallationException::becauseWordPressIsNotInstalled()->getMessage());

        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();

            Dispatcher::dispatch(Events::SUITE_INIT);
        });
    }

    /**
     * It should load WordPress before suite if loadOnly w/ config files
     *
     * @test
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
        Installation::scaffold($wpRootDir, '6.1.1')
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
            Dispatcher::addListener(WPLoader::EVENT_BEFORE_LOADONLY, static function () use (&$actions) {
                $actions[] = WPLoader::EVENT_BEFORE_LOADONLY;
            });
            Dispatcher::addListener(WPLoader::EVENT_AFTER_LOADONLY, static function () use (&$actions) {
                $actions[] = WPLoader::EVENT_AFTER_LOADONLY;
            });

            Dispatcher::dispatch(Events::SUITE_INIT);

            Assert::assertEquals('test_file_002.php', getenv('LOADED_2'));
            Assert::assertEquals($wpRootDir . '/', ABSPATH);
            Assert::assertEquals([
                WPLoader::EVENT_BEFORE_LOADONLY,
                WPLoader::EVENT_AFTER_LOADONLY,
            ], $actions);
            Assert::assertInstanceOf(FactoryStore::class, $wpLoader->factory());
        });
    }

    /**
     * It should create the database if it does not exist
     *
     * @test
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

    public function dbModuleCompatDataProvider(): Generator
    {
        yield 'MysqlDatabase' => ['MysqlDatabase', MysqlDatabase::class];
        yield 'WPDb' => ['WPDb', WPDb::class];
        yield WPDb::class => [WPDb::class, WPDb::class];
    }

    /**
     * It should not throw when loadOnly true and using DB module
     *
     * @test
     * @dataProvider dbModuleCompatDataProvider
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
        Installation::scaffold($wpRootDir, '6.1.1')
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

            Dispatcher::dispatch(Events::SUITE_INIT);

            Assert::assertEquals($wpRootDir . '/', ABSPATH);
        });
    }

    /**
     * It should throw if using with WPDb and not loadOnly
     *
     * @test
     * @dataProvider dbModuleCompatDataProvider
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
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db);

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
     * It should throw if configFile not found
     *
     * @test
     */
    public function should_throw_if_config_file_not_found(): void
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
            'configFile' => __DIR__ . '/some-file-that-does-not-exist.php',
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $wpLoader = $this->module();

        $this->expectException(ModuleConfigException::class);

        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should install and bootstrap single site using constants' names
     *
     * @test
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
        Installation::scaffold($wpRootDir, 'latest');

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
     * It should throw module exception on error during bootstrap
     *
     * @test
     */
    public function should_throw_module_exception_on_error_during_bootstrap(): void
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
            'dbPassword' => $dbPassword
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessageRegExp('/WordPress bootstrap failed/');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            // This will cause an exit 1 during bootstrap.
            uopz_set_return('tests_get_phpunit_version', '5.0.0');
            $wpLoader->_initialize();
        });
    }

    /**
     * It should install and bootstrap single installation
     *
     * @test
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
        $installation = Installation::scaffold($wpRootDir, 'latest');
        $this->copyOverContentFromTheMainInstallation($installation);

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $actions = [];
            Dispatcher::addListener(WPLoader::EVENT_BEFORE_INSTALL, function () use (&$actions) {
                $actions[] = 'before_install';
            });
            Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function () use (&$actions) {
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
        $installation = Installation::scaffold($wpRootDir, 'latest');
        $this->copyOverContentFromTheMainInstallation($installation);

        $wpLoader = $this->module();
        $installationOutput = $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $actions = [];
            Dispatcher::addListener(WPLoader::EVENT_BEFORE_INSTALL, function () use (&$actions) {
                $actions[] = 'before_install';
            });
            Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function () use (&$actions) {
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

    public function singleSiteAndMultisite(): array
    {
        return [
            'single site' => [false],
            'multisite' => [true],
        ];
    }

    /**
     * It should throw if there is an error while activating a plugin
     *
     * @test
     */
    public function should_throw_if_there_is_an_error_while_activating_a_plugin(): void
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
                'some-plugin/some-plugin.php',
            ]
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'Failed to activate plugin some-plugin/some-plugin.php. Plugin file does not exist.'
        );

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if there is an error while activating a plugin in multisite
     *
     * @test
     */
    public function should_throw_if_there_is_an_error_while_activating_a_plugin_in_multisite(): void
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
                'some-plugin/some-plugin.php',
            ],
            'multisite' => true,
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'Failed to activate plugin some-plugin/some-plugin.php. Plugin file does not exist.'
        );

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if there is an error while switching theme
     *
     * @test
     */
    public function should_throw_if_there_is_an_error_while_switching_theme(): void
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
            'theme' => 'some-theme',
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage('Theme some-theme does not exist.');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if there is an error while switching theme in multisite
     *
     * @test
     */
    public function should_throw_if_there_is_an_error_while_switching_theme_in_multisite(): void
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
            'theme' => 'some-theme',
            'multisite' => true,
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage('Theme some-theme does not exist.');

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should throw if theme is an array
     *
     * @test
     */
    public function should_throw_if_theme_is_an_array(): void
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
            'theme' => ['some-template', 'some-stylesheet'],
        ];
        Installation::scaffold($wpRootDir, 'latest');

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
        });
    }

    /**
     * It should correctly activate child theme in single installation
     *
     * @test
     */
    public function should_correctly_activate_child_theme_in_single_installation(): void
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
            'theme' => 'some-child-theme'
        ];
        if (PHP_VERSION >= 7.4) {
            // WooCommerce has a minimum PHP version of 7.4.0 required.
            $this->config['plugins'][] = 'woocommerce/woocommerce.php';
        }
        $db = (new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost))->create();
        $installation = Installation::scaffold($wpRootDir, 'latest')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $this->copyOverContentFromTheMainInstallation($installation);
        // Create a twentytwenty-child theme.
        $installation->runWpCliCommandOrThrow([
            'scaffold',
            'child-theme',
            'some-child-theme',
            '--parent_theme=twentytwenty',
            '--theme_name=some-child-theme',
            '--force'
        ]);

        $wpLoader = $this->module();
        $installationOutput = $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            Assert::assertEquals('twentytwenty', get_option('template'));
            Assert::assertEquals('some-child-theme', get_option('stylesheet'));

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        });
    }

    /**
     * It should correctly activate child theme in multisite installation
     *
     * @test
     */
    public function should_correctly_activate_child_theme_in_multisite_installation(): void
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
            'theme' => 'twentytwenty-child',
            'multisite' => true,
        ];
        if (PHP_VERSION >= 7.4) {
            // WooCommerce has a minimum PHP version of 7.4.0 required.
            $this->config['plugins'][] = 'woocommerce/woocommerce.php';
        }
        $db = (new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost))->create();
        $installation = Installation::scaffold($wpRootDir, 'latest')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBFOLDER);
        $installation->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );
        $this->copyOverContentFromTheMainInstallation($installation);
        // Create a twentytwenty-child theme.
        $installation->runWpCliCommandOrThrow([
            'scaffold',
            'child-theme',
            'twentytwenty-child',
            '--parent_theme=twentytwenty',
            '--theme_name=twentytwenty-child',
            '--force'
        ]);

        $wpLoader = $this->module();
        $installationOutput = $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            Assert::assertEquals('twentytwenty', get_option('template'));
            Assert::assertEquals('twentytwenty-child', get_option('stylesheet'));

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        });
    }

    /**
     * It should throw if specified dump file does not exist
     *
     * @test
     */
    public function should_throw_if_specified_dump_file_does_not_exist(): void
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
            'dump' => 'not-really-existing.sql'
        ];
        Installation::scaffold($wpRootDir);

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $wpLoader->_initialize();
    }

    /**
     * It should throw if any dump file specified does not exist
     *
     * @test
     */
    public function should_throw_if_any_dump_file_specified_does_not_exist(): void
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
                'not-really-existing.sql',
            ]
        ];
        Installation::scaffold($wpRootDir);

        $this->expectException(ModuleConfigException::class);

        $wpLoader = $this->module();
        $wpLoader->_initialize();
    }

    /**
     * It should rethrow on failure to load a dump file
     *
     * @test
     */
    public function should_rethrow_on_failure_to_load_a_dump_file(): void
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
            ]
        ];
        Installation::scaffold($wpRootDir);

        $wpLoader = $this->module();

        $this->expectException(ModuleException::class);

        $this->assertInIsolation(static function () use ($wpLoader) {
            uopz_set_return('fopen', false);
            $wpLoader->_initialize();
        });
    }

    /**
     * It should allow loading a database dump before tests
     *
     * @test
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
        Installation::scaffold($wpRootDir);

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
        Installation::scaffold($wpRootDir);

        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();

            Assert::assertEquals('value_1', get_option('option_1'));
            Assert::assertEquals('value_2', get_option('option_2'));
            Assert::assertEquals('value_3', get_option('option_3'));
        });
    }

    /**
     * It should support using dbUrl to set up module
     *
     * @test
     */
    public function should_support_using_db_url_to_set_up_module(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        Installation::scaffold($wpRootDir);
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
     * It should throw if dbUrl not set and db credentials are not provided
     *
     * @test
     */
    public function should_throw_if_db_url_not_set_and_db_credentials_are_not_provided(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        Installation::scaffold($wpRootDir);
        $this->config = ['wpRootFolder' => $wpRootDir];

        $this->expectException(ModuleConfigException::class);
        $message = "The `dbUrl` configuration parameter must be set or the `dbPassword`, `dbHost`, `dbName` and " .
            "`dbUser` parameters must be set.";
        $this->expectExceptionMessage($message);

        $this->module();
    }

    /**
     * It should place SQLite dropin if using SQLite database for tests
     *
     * @test
     * @group sqlite
     */
    public function should_place_sq_lite_dropin_if_using_sq_lite_database_for_tests(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        Installation::scaffold($wpRootDir);
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
     */
    public function should_initialize_correctly_with_sqlite_database(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        Installation::scaffold($wpRootDir);
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
     */
    public function should_initialize_correctly_with_sqlite_database_in_load_only_mode(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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
            Dispatcher::dispatch(Events::SUITE_INIT);

            Assert::assertTrue(function_exists('do_action'));
            Assert::assertInstanceOf(\WP_User::class, wp_get_current_user());
        });
    }

    /**
     * It should correctly load the module on a Bedrock installation
     *
     * @test
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
     */
    public function should_correctly_load_with_different_database_names(string $dbName): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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
     */
    public function should_not_backup_globals_by_default(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsFalse');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());
        });
    }

    /**
     * It should allow controlling the backup of global variables in the WPTestCase
     *
     * @test
     * @group backup-globals
     */
    public function should_allow_controlling_the_backup_of_global_variables_in_the_wp_test_case(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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

        // Set`WPLoader.backupGlobals` to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => false,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsFalse');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());
        });

        // Set `WPLoader.backupGlobals` to `true`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => true,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsTrue');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());
        });

        // Do not set `WPLoader.backupGlobals`, but use the default value of `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testBackupGlobalsIsFalse');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());
        });

        // Set `WPLoader.backupGlobals` to `true`, but use a use-case that sets it explicitly to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => true,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $overridingTestCaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $overridingTestCaseFile;

            $testCase = new \BackupControlTestCaseOverridingTestCase('testBackupGlobalsIsFalse');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
        ];
        $wpLoader = $this->module();

        // Test that globals defined before the test runs should not be backed up by default.
        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            // Set the initial value of the global variable.
            global $_wpbrowser_test_global_var;
            $_wpbrowser_test_global_var = 'initial_value';

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillUpdateTheValueOfGlobalVar');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());

            // Check that the value of the global variable has been updated.
            Assert::assertEquals('updated_value', $_wpbrowser_test_global_var);
        });

        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobalsExcludeList' => ['_wpbrowser_test_global_var'],
        ];
        $wpLoader = $this->module();

        // Test that adding a global to the list of `backupGlobalsExcludeList` will not back it up.
        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            // Set the initial value of the global variable.
            global $_wpbrowser_test_global_var;
            $_wpbrowser_test_global_var = 'initial_value';

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillUpdateTheValueOfGlobalVar');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());

            // Check that the value of the global variable has been updated.
            Assert::assertEquals('updated_value', $_wpbrowser_test_global_var);
        });
    }

    /**
     * It should allow controlling the backup of static attributes in the WPTestCase
     *
     * @test
     * @group backup-globals
     */
    public function should_allow_controlling_the_backup_of_static_attributes_in_the_wp_test_case(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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

        // Set`WPLoader.backupStaticAttributes` to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => false,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillAlterStoreStaticAttribute');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());

            Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttribute);
        });

        // Don't set`WPLoader.backupStaticAttributes`, it should be `false` by default.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl()
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $testcaseFile;

            $testCase = new \BackupControlTestCase('testWillAlterStoreStaticAttribute');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());

            Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttribute);
        });

        // Set the value of `WPLoader.backupStaticAttributes` to `true`, but use a use-case that sets it explicitly to `false`.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => true,
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $overridingTestCaseFile) {
            $wpLoader->_initialize();

            Assert::assertTrue(function_exists('do_action'));

            require_once $overridingTestCaseFile;

            $testCase = new \BackupControlTestCaseOverridingTestCase('testWillAlterStoreStaticAttribute');
            /** @var TestResult $result */
            $result = $testCase->run();

            Assert::assertTrue($result->wasSuccessful());

            Assert::assertEquals('updated_value', \BackupControlTestCaseOverridingStore::$staticAttribute);
        });

        // Set the value of the `WPLoader.backupStaticAttributesExcludeList` to not back up the static attribute.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => true,
            'backupStaticAttributesExcludeList' => [
                \BackupControlTestCaseStore::class => ['staticAttribute', 'staticAttributeThree'],
                \BackupControlTestCaseStoreTwo::class => ['staticAttributeFour'],
            ]
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $testcaseFile) {
                $wpLoader->_initialize();

                Assert::assertTrue(function_exists('do_action'));

                require_once $testcaseFile;

                $testCase = new \BackupControlTestCase('testWillAlterStoreStaticAttribute');
                /** @var TestResult $result */
                $result = $testCase->run();

                Assert::assertTrue($result->wasSuccessful());

                Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttribute);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStore::$staticAttributeTwo);
                Assert::assertEquals('updated_value', \BackupControlTestCaseStore::$staticAttributeThree);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStore::$staticAttributeFour);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStoreTwo::$staticAttribute);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStoreTwo::$staticAttributeTwo);
                Assert::assertEquals('initial_value', \BackupControlTestCaseStoreTwo::$staticAttributeThree);
                Assert::assertEquals('updated_value', \BackupControlTestCaseStoreTwo::$staticAttributeFour);
            }
        );
    }

    public function notABooleanProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [1],
            'float' => [1.1],
            'array' => [[]],
            'object' => [new stdClass()],
        ];
    }

    /**
     * It should throw if backupGlobals is not a boolean
     *
     * @test
     * @dataProvider notABooleanProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_globals_is_not_a_boolean($notABoolean): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobals' => $notABoolean,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    public function notArrayOfStringsProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [1],
            'float' => [1.1],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of floats' => [[1.1, 2.2, 3.3]],
            'array of objects' => [[new stdClass(), new stdClass(), new stdClass()]],
            'array of arrays' => [[[1, 2, 3], [4, 5, 6], [7, 8, 9]]],
            'array of mixed' => [[1, 2.2, new stdClass(), [1, 2, 3]]],
        ];
    }

    /**
     * It should throw if backupGlobalsExcludeList is not an array of strings
     *
     * @test
     * @dataProvider notArrayOfStringsProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_globals_exclude_list_is_not_an_array_of_strings($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupGlobalsExcludeList' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    /**
     * It should throw if backupStaticAttributes is not a boolean
     *
     * @test
     * @dataProvider notABooleanProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_static_attributes_is_not_a_boolean($notABoolean): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributes' => $notABoolean,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    public function notStaticAttributeExcludeListProvider(): array
    {
        return [
            'string' => ['string'],
            'integer' => [1],
            'float' => [1.1],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of floats' => [[1.1, 2.2, 3.3]],
            'array of objects' => [[new stdClass(), new stdClass(), new stdClass()]],
            'array of arrays' => [[[1, 2, 3], [4, 5, 6], [7, 8, 9]]],
            'array of mixed' => [[1, 2.2, new stdClass(), [1, 2, 3]]],
        ];
    }

    /**
     * It should throw if backupStaticAttributesExcludeList is not in the correct format
     *
     * @test
     * @dataProvider notStaticAttributeExcludeListProvider
     * @group backup-globals
     */
    public function should_throw_if_backup_static_attributes_exclude_list_is_not_in_the_correct_format($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'backupStaticAttributesExcludeList' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    /**
     * It should throw if skipInstall is not a boolean
     *
     * @test
     * @dataProvider notABooleanProvider
     */
    public function should_throw_if_skip_install_is_not_a_boolean($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'skipInstall' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    /**
     * It should skip installation when skipInstall is true
     *
     * @test
     */
    public function should_skip_installation_when_skip_install_is_true(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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
                Dispatcher::addListener(WPLoader::EVENT_BEFORE_INSTALL, function () use (&$beforeInstallCalled) {
                    $beforeInstallCalled = true;
                });
                Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function () use (&$afterInstallCalled) {
                    $afterInstallCalled = true;
                });
                Dispatcher::addListener(WPLoader::EVENT_AFTER_BOOTSTRAP, function () use (&$afterBootstrapCalled) {
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
                Dispatcher::addListener(WPLoader::EVENT_BEFORE_INSTALL, function () use (&$beforeInstallCalled) {
                    $beforeInstallCalled = true;
                });
                Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function () use (&$afterInstallCalled) {
                    $afterInstallCalled = true;
                });
                Dispatcher::addListener(WPLoader::EVENT_AFTER_BOOTSTRAP, function () use (&$afterBootstrapCalled) {
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
                Dispatcher::addListener(WPLoader::EVENT_BEFORE_INSTALL, function () use (&$beforeInstallCalled) {
                    $beforeInstallCalled = true;
                });
                Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function () use (&$afterInstallCalled) {
                    $afterInstallCalled = true;
                });
                Dispatcher::addListener(WPLoader::EVENT_AFTER_BOOTSTRAP, function () use (&$afterBootstrapCalled) {
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
     * It should throw if silentlyActivatePlugins config parameter is not a list of strings
     *
     * @test
     * @dataProvider notArrayOfStringsProvider
     */
    public function should_throw_if_silently_activate_plugins_config_parameter_is_not_a_list_of_strings($input): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'silentlyActivatePlugins' => $input,
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    /**
     * It should throw if plugin appears in both plugins and silentlyActivatePlugins config parameters
     *
     * @test
     */
    public function should_throw_if_plugin_appears_in_both_plugins_and_silently_activate_plugins_config_parameters(
    ): void
    {
        $wpRootDir = Env::get('WORDPRESS_ROOT_DIR');
        $db = (new Installation($wpRootDir))->getDb();
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'plugins' => ['woocommerce/woocommerce.php', 'my-plugin/plugin.php'],
            'silentlyActivatePlugins' => ['foo-plugin/plugin.php', 'woocommerce/woocommerce.php'],
        ];

        $this->expectException(ModuleConfigException::class);

        $this->module();
    }

    /**
     * It should fail to activate when plugins generate unexpected output
     *
     * @test
     */
    public function should_fail_to_activate_when_plugins_generate_unexpected_output(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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
     */
    public function should_allow_activating_plugins_silently(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($wpRootDir);
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
