<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Events;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use Exception;
use Generator;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader\FactoryStore;
use lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use PHPUnit\Framework\Assert;
use RuntimeException;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use UnitTester;
use lucatume\WPBrowser\WordPress\Assert as WPAssert;
use WP_Theme;
use const ABSPATH;
use const WP_DEBUG;

class WPLoaderTest extends Unit
{
    use SnapshotAssertions;
    use DatabaseAssertions;
    use LoopIsolation;
    use TmpFilesCleanup;

    protected $backupGlobals = false;

    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /**
     * @var array
     */
    protected array $config;
    private ?string $previousCwd = null;
    private ?string $homeEnvBackup = null;
    private ?string $homeServerBackup = null;
    private ?ModuleContainer $mockModuleContainer = null;

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
        foreach ([
                     'DB_HOST',
                     'DB_NAME',
                     'DB_USER',
                     'DB_PASSWORD',
                 ] as $const) {
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
    private function module(): WPLoader
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), []);
        return new WPLoader($this->mockModuleContainer, $this->config);
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
            chdir($rootDir) ;
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
            chdir($rootDir) ;
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $wpLoader = $this->module();

        $this->expectException(InstallationException::class);
        $this->expectExceptionMessage(InstallationException::becauseWordPressIsNotInstalled()->getMessage());

        $this->assertInIsolation(static function () use ($wpRootDir, $wpLoader) {
            $wpLoader->_initialize();

            Dispatcher::dispatch(Events::SUITE_BEFORE);
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
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

            Dispatcher::dispatch(Events::SUITE_BEFORE);

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
        yield 'Db' => ['Db', Db::class];
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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
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
        $mockDbModule = $this->createStub($dbModuleClass);
        $this->mockModuleContainer->mock($dbModuleName, $mockDbModule);

        $this->assertInIsolation(static function () use ($wpLoader, $wpRootDir) {
            $wpLoader->_initialize();

            Dispatcher::dispatch(Events::SUITE_BEFORE);

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
        $mockDbModule = $this->createStub($dbModuleClass);
        $this->mockModuleContainer->mock($dbModuleName, $mockDbModule);

        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessageMatches('/The WPLoader module is not being used to only load ' .
            'WordPress, but to also install it/');

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
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        Installation::scaffold($wpRootDir, '6.1.1')->configure($db);

        $wpLoader = $this->module();
        $mockDbModule = $this->createStub($dbModuleClass);
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
        $this->expectExceptionMessageMatches('/WordPress bootstrap failed/');

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
                'hello-dolly/hello.php',
                'woocommerce/woocommerce.php',
            ],
            'theme' => 'twentytwenty',
        ];
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

            Assert::assertEquals([
                'akismet/akismet.php',
                'hello-dolly/hello.php',
                'woocommerce/woocommerce.php',
            ], get_option('active_plugins'));
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
            Assert::assertEquals($wpRootDir . '/wp-content/some/path/some-file.php',
                $wpLoader->getContentFolder('some/path/some-file.php'));
            Assert::assertEquals($wpRootDir . '/wp-content/plugins/some-file.php',
                $wpLoader->getPluginsFolder('/some-file.php'));
            Assert::assertEquals($wpRootDir . '/wp-content/themes/some-file.php',
                $wpLoader->getThemesFolder('/some-file.php'));
            WPAssert::assertTableExists('posts');
            WPAssert::assertTableExists('woocommerce_order_items');
            WPAssert::assertUpdatesDisabled();

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        });

        codecept_debug($installationOutput);
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
                'hello-dolly/hello.php',
                'woocommerce/woocommerce.php',
            ],
            'theme' => 'twentytwenty',
            'multisite' => true,
        ];
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

            Assert::assertEquals([
                'akismet/akismet.php',
                'hello-dolly/hello.php',
                'woocommerce/woocommerce.php',
            ], array_keys(get_site_option('active_sitewide_plugins')));
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
            Assert::assertEquals($wpRootDir . '/wp-content/some/path/some-file.php',
                $wpLoader->getContentFolder('some/path/some-file.php'));
            Assert::assertEquals($wpRootDir . '/wp-content/plugins/some-file.php',
                $wpLoader->getPluginsFolder('/some-file.php'));
            Assert::assertEquals($wpRootDir . '/wp-content/themes/some-file.php',
                $wpLoader->getThemesFolder('/some-file.php'));
            WPAssert::assertTableExists('posts');
            WPAssert::assertTableExists('woocommerce_order_items');
            WPAssert::assertUpdatesDisabled();

            return [
                'bootstrapOutput' => $wpLoader->_getBootstrapOutput(),
                'installationOutput' => $wpLoader->_getInstallationOutput(),
            ];
        });

        codecept_debug($installationOutput);
    }

    private function copyOverContentFromTheMainInstallation(Installation $installation): void
    {
        $mainWPInstallationRootDir = Env::get('WORDPRESS_ROOT_DIR');
        foreach ([
                     'hello-dolly',
                     'akismet',
                     'woocommerce',
                 ] as $plugin) {
            if (!FS::recurseCopy($mainWPInstallationRootDir . '/wp-content/plugins/' . $plugin,
                $installation->getPluginsDir($plugin))) {
                throw new RuntimeException(sprintf('Could not copy plugin %s', $plugin));
            }
        }
        // Copy over theme from the main installation.
        if (!FS::recurseCopy($mainWPInstallationRootDir . '/wp-content/themes/twentytwenty',
            $installation->getThemesDir('twentytwenty'))) {
            throw new RuntimeException('Could not copy theme twentytwenty');
        }
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Plugin some-plugin/some-plugin.php could not be activated. Plugin file does not exist.');

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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Plugin some-plugin/some-plugin.php could not be network activated. Plugin file does not exist.');

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

        $this->expectException(RuntimeException::class);
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

        $this->expectException(RuntimeException::class);
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
                'hello-dolly/hello.php',
                'woocommerce/woocommerce.php',
            ],
            'theme' => 'twentytwenty-child'
        ];
        $db = (new Db($dbName, $dbUser, $dbPassword, $dbHost))->create();
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
            'twentytwenty-child',
            '--parent_theme=twentytwenty',
            '--theme_name=twentytwenty-child'
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

        codecept_debug($installationOutput);
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
                'hello-dolly/hello.php',
                'woocommerce/woocommerce.php',
            ],
            'theme' => 'twentytwenty-child',
            'multisite' => true,
        ];
        $db = (new Db($dbName, $dbUser, $dbPassword, $dbHost))->create();
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
            '--theme_name=twentytwenty-child'
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

        codecept_debug($installationOutput);
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
}
