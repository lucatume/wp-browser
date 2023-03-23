<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use PHPUnit\Framework\Assert;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use UnitTester;

class WPLoaderTest extends Unit
{
    use SnapshotAssertions;
    use DatabaseAssertions;
    use LoopIsolation;

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


    private function changeDir(string $dirname): void
    {
        $this->previousCwd = getcwd();
        chdir($dirname);
    }

    private function changeHome(string $dir): void
    {
        $this->homeEnvBackup = getenv('HOME');
        $this->homeServerBackup = $_SERVER['HOME'] ?? null;
        putenv('HOME=' . $dir);
        $_SERVER['HOME'] = $dir;
    }

    /**
     * @return WPLoader
     */
    private function module(): WPLoader
    {
        $moduleContainer = new ModuleContainer(new Di(), []);
        return new WPLoader($moduleContainer, $this->config);
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
        $this->changeDir($rootDir);
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
        $this->changeHome($homeDir);
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
        $this->assertInIsolation(static function() use ($wpRootDir, $wpLoader) {
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
        Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db);

        $wpLoader = $this->module();
        $this->assertInIsolation(static function () use ($wpLoader) {
            $wpLoader->_initialize();
            $installation = $wpLoader->getInstallation();

            Assert::assertEquals($installation->getAuthKey(), $wpLoader->_getConfig('authKey'));
            Assert::assertEquals($installation->getSecureAuthKey(), $wpLoader->_getConfig('secureAuthKey'));
            Assert::assertEquals($installation->getLoggedInKey(), $wpLoader->_getConfig('loggedInKey'));
            Assert::assertEquals($installation->getNonceKey(), $wpLoader->_getConfig('nonceKey'));
            Assert::assertEquals($installation->getAuthSalt(), $wpLoader->_getConfig('authSalt'));
            Assert::assertEquals($installation->getSecureAuthSalt(), $wpLoader->_getConfig('secureAuthSalt'));
            Assert::assertEquals($installation->getLoggedInSalt(), $wpLoader->_getConfig('loggedInSalt'));
            Assert::assertEquals($installation->getNonceSalt(), $wpLoader->_getConfig('nonceSalt'));
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
                'authKey',
                'secureAuthKey',
                'loggedInKey',
                'nonceKey',
                'authSalt',
                'secureAuthSalt',
                'loggedInSalt',
                'nonceSalt',
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

    /**
     * It should load the core phpunit suite bootstrap file correctly
     *
     * @test
     * @skip
     */
    public function should_load_the_core_phpunit_suite_bootstrap_file_correctly(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbName' => Random::dbName(),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD')
        ];
        $corePhpunitUnitBootstrapFileContents = <<< PHP
<?php

putenv('CORE_PHPUNIT_BOOTSTRAP_FILE_LOADED=1');
PHP;
        MonkeyPatch::redirectFileContents(CorePHPUnit::bootstrapFile(), $corePhpunitUnitBootstrapFileContents);

        $wpLoader = $this->module();
        $wpLoader->_initialize();

        $this->assertEquals(1, getenv('CORE_PHPUNIT_BOOTSTRAP_FILE_LOADED'));
    }
}
