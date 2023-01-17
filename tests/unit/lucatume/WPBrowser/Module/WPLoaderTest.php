<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use lucatume\WPBrowser\MonkeyPatch\MonkeyPatchingAssertions;
use lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class WPLoaderTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;
    use DatabaseAssertions;

    protected $backupGlobals = false;
    /**
     * @var \UnitTester
     */
    protected \UnitTester $tester;

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

        $this->module()->_initialize(false);
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

        $this->module()->_initialize(false);
    }

    /**
     * It should allow specifying the wpRootFolder as a relative path to cwd or abspath
     *
     * @test
     */
    public function should_allow_specifying_the_wp_root_folder_as_a_relative_path_to_cwd_or_abspath(): void
    {
        $rootDir = FS::tmpDir('wploader_', ['test' => ['wordpress' => []]]);
        Installation::scaffold($rootDir, '6.1.1');
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
        $wpLoader1->_initialize(false);

        $this->assertEquals('test/wordpress', $wpLoader1->_getConfig('wpRootFolder'));
        $this->assertEquals($rootDir . '/test/wordpress/', $wpLoader1->getWpRootFolder());

        $this->config = [
            'wpRootFolder' => $rootDir . '/test/wordpress',
            'dbName' => $dbName,
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
        ];

        $wpLoader2 = $this->module();
        $wpLoader2->_initialize(false);

        $this->assertEquals($rootDir . '/test/wordpress', $wpLoader2->_getConfig('wpRootFolder'));
        $this->assertEquals($rootDir . '/test/wordpress/', $wpLoader2->getWpRootFolder());
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
        $wpLoader->_initialize(false);

        $this->assertEquals('~/projects/work/acme/wordpress', $wpLoader->_getConfig('wpRootFolder'));
        $this->assertEquals($homeDir . '/projects/work/acme/wordpress/', $wpLoader->getWpRootFolder());
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
        $wpLoader->_initialize(false);

        $this->assertEquals($wpRootDir, $wpLoader->_getConfig('wpRootFolder'));
        $this->assertEquals($wpRootDir . '/', $wpLoader->getWpRootFolder());
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
        $wpLoader->_initialize(false);

        $this->assertEquals($wpRootDir . '/Word\ Press', $wpLoader->_getConfig('wpRootFolder'));
        $this->assertEquals($wpRootDir . '/Word Press/', $wpLoader->getWpRootFolder());
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
        $wpLoader->_initialize(false);

        $this->assertInstanceOf(Scaffolded::class, $wpLoader->getInstallation()->getState());
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
        $wpLoader->_initialize(false);
        $installation = $wpLoader->getInstallation();

        $this->assertEquals($installation->getAuthKey(), $wpLoader->_getConfig('authKey'));
        $this->assertEquals($installation->getSecureAuthKey(), $wpLoader->_getConfig('secureAuthKey'));
        $this->assertEquals($installation->getLoggedInKey(), $wpLoader->_getConfig('loggedInKey'));
        $this->assertEquals($installation->getNonceKey(), $wpLoader->_getConfig('nonceKey'));
        $this->assertEquals($installation->getAuthSalt(), $wpLoader->_getConfig('authSalt'));
        $this->assertEquals($installation->getSecureAuthSalt(), $wpLoader->_getConfig('secureAuthSalt'));
        $this->assertEquals($installation->getLoggedInSalt(), $wpLoader->_getConfig('loggedInSalt'));
        $this->assertEquals($installation->getNonceSalt(), $wpLoader->_getConfig('nonceSalt'));
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
        $wpLoader->_initialize(false);

        $this->assertEquals($wpRootDir . '/foo-bar', $wpLoader->getWpRootFolder('foo-bar'));
        $this->assertEquals($wpRootDir . '/foo-bar/baz', $wpLoader->getWpRootFolder('foo-bar/baz'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $wpLoader->getWpRootFolder('wp-config.php'));
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
        $wpLoader->_initialize(false);

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
                $this->assertNotEquals($var[$i - 1], $wpLoader->_getConfig($key));
            }
            $this->assertEquals(64, strlen($wpLoader->_getConfig($key)));
        }
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
        // Null the Core PHPUnit bootstrap file to avoid loading it.
        MonkeyPatch::redirectFileToFile(CorePHPUnit::bootstrapFile(), MonkeyPatch::dudFile());

        $wpLoader = $this->module();
        $wpLoader->_initialize(true);

        $this->assertDatabaseExists($dbHost, $dbUser, $dbPassword, $dbName);
    }

    /**
     * It should load the core phpunit suite bootstrap file correctly
     *
     * @test
     */
    public function should_load_the_core_phpunit_suite_bootstrap_file_correctly(): void
    {
        $this->config = [
            'wpRootFolder' => FS::tmpDir('wploader_'),
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
        $wpLoader->_initialize(true);

        $this->assertEquals(1, getenv('CORE_PHPUNIT_BOOTSTRAP_FILE_LOADED'));
    }

    /////////////////////////////////// old test code //////////////////////////

//    /**
//     * @test
//     * it should set the template and stylesheet options when passed a `theme` config parameter
//     */
//    public function it_should_set_the_template_and_stylesheet_options_when_passed_a_theme_config_parameter()
//    {
//        $this->config['theme'] = 'foo';
//
//        $sut = $this->module();
//        $sut->setActiveTheme();
//
//        global $wp_tests_options;
//        $this->assertNotEmpty($wp_tests_options['template']);
//        $this->assertEquals('foo', $wp_tests_options['template']);
//        $this->assertNotEmpty($wp_tests_options['stylesheet']);
//        $this->assertEquals('foo', $wp_tests_options['stylesheet']);
//    }
//
//    /**
//     * @test
//     * it should allow to set a child theme passing an array of parent, child
//     */
//    public function it_should_allow_to_set_a_child_theme_passing_an_array_of_parent_child()
//    {
//        $this->config['theme'] = ['foo', 'bar'];
//
//        $sut = $this->module();
//        $sut->setActiveTheme();
//
//        global $wp_tests_options;
//        $this->assertNotEmpty($wp_tests_options['template']);
//        $this->assertEquals('foo', $wp_tests_options['template']);
//        $this->assertNotEmpty($wp_tests_options['stylesheet']);
//        $this->assertEquals('bar', $wp_tests_options['stylesheet']);
//    }
//
//    /**
//     * @test
//     * it should switch to theme if set
//     */
//    public function it_should_switch_to_theme_if_set()
//    {
//        $this->config['theme'] = ['foo', 'bar'];
//        $this->wp->switch_theme('bar')->shouldBeCalled();
//        $this->wp->getWpContentDir()->willReturn('');
//        $this->wp->do_action('after_switch_theme', 'bar')->shouldBeCalled();
//
//        $sut = $this->module();
//        $sut->switchTheme();
//    }
//
//    /**
//     * @test
//     * it should switch theme to just stylesheet if no template
//     */
//    public function it_should_switch_theme_to_just_stylesheet_if_no_template()
//    {
//        $this->config['theme'] = 'foo';
//        $this->wp->switch_theme('foo')->shouldBeCalled();
//        $this->wp->getWpContentDir()->willReturn('');
//        $this->wp->do_action('after_switch_theme', 'foo')->shouldBeCalled();
//
//        $sut = $this->module();
//        $sut->switchTheme();
//    }
//
//    /**
//     * @test
//     * it should not switch to theme if not set
//     */
//    public function it_should_not_switch_to_theme_if_not_set()
//    {
//        unset($this->config['theme']);
//        $this->wp->switch_theme(Arg::type('string'))->shouldNotBeCalled();
//
//        $sut = $this->module();
//        $sut->switchTheme();
//    }
//
//    public function exitMessagesCombos()
//    {
//        return [
//            'no_db_module_loadOnly_true' => [false, true],
//            'no_db_module_loadOnly_false' => [false, false],
//            'WPDb_module_loadOnly_true' => ['WPDb', true],
//            'WPDb_module_loadOnly_false' => ['WPDb', false],
//            'Db_module_loadOnly_true' => ['Db', true],
//            'Db_module_loadOnly_false' => ['Db', false],
//        ];
//    }
//
//    /**
//     * Test exit messages
//     *
//     * @dataProvider exitMessagesCombos
//     */
//    public function test_exit_messages($dbModule, $loadOnly)
//    {
//        $this->moduleContainer->hasModule('WPDb')->willReturn($dbModule === 'WPDb');
//        $this->moduleContainer->hasModule('Db')->willReturn($dbModule === 'Db');
//        $sut = $this->module();
//        $output = new BufferedOutput();
//        $sut->_setConfig(array_merge($sut->_getConfig(), [
//            'loadOnly' => $loadOnly
//        ]));
//
//        $sut->_wordPressExitHandler($output);
//
//        $this->assertMatchesStringSnapshot($output->fetch());
//    }
//
//    protected function _before()
//    {
//        $this->moduleContainer = $this->stubProphecy(ModuleContainer::class);
//        $this->config = [
//            'wpRootFolder' => codecept_data_dir('folder-structures/default-wp'),
//            'dbName' => 'someDb',
//            'dbHost' => 'localhost',
//            'dbUser' => 'somePass',
//            'dbPassword' => 'somePass',
//        ];
//        $this->wp = $this->stubProphecy(WP::class);
//    }
//
//    /**
//     * It should accept absolute paths in the pluginsDir parameter
//     *
//     * @test
//     */
//    public function should_accept_absolute_paths_in_the_plugins_dir_parameter()
//    {
//        $this->config['pluginsFolder'] = __DIR__;
//
//        $wpLoader = $this->module();
//
//        $this->assertEquals(__DIR__, $wpLoader->getPluginsFolder());
//        $this->assertEquals(__DIR__ . '/foo/bar', $wpLoader->getPluginsFolder('foo/bar'));
//    }
//
//    /**
//     * It should throw if absolute path for pluginsFolder does not exist
//     *
//     * @test
//     */
//    public function should_throw_if_absolute_path_for_plugins_folder_does_not_exist()
//    {
//        $pluginsRoot = __DIR__ . '/foo/bar';
//
//        $this->config['pluginsFolder'] = $pluginsRoot . '/plugins';
//
//        $wpLoader = $this->module();
//
//        $this->expectException(ModuleConfigException::class);
//
//        $wpLoader->getPluginsFolder();
//    }
//
//
//    /**
//     * It should throw if WP_PLUGINS_DIR does not exist
//     *
//     * @test
//     */
//    public function should_throw_if_wp_plugins_dir_does_not_exist()
//    {
//        if (!extension_loaded('uopz')) {
//            $this->markTestSkipped('This test cannot run without the uopz extension');
//        }
//
//        if (PHP_VERSION_ID <= 70000) {
//            $this->markTestSkipped('Due to a uopz bug on PHP 5.6.');
//        }
//
//        uopz_redefine('WP_PLUGIN_DIR', '/foo/bar/baz');
//
//        $wpLoader = $this->module();
//
//        $this->expectException(ModuleConfigException::class);
//
//        $wpLoader->getPluginsFolder();
//    }
//
//    /**
//     * It should correctly build paths when the WP_PLUGIN_DIR constant is defined
//     *
//     * @test
//     */
//    public function should_correctly_build_paths_when_the_wp_plugin_dir_const_is_defined()
//    {
//        if (!extension_loaded('uopz')) {
//            $this->markTestSkipped('This test cannot run without the uopz extension');
//        }
//
//        if (PHP_VERSION_ID <= 70000) {
//            $this->markTestSkipped('Due to a uopz bug on PHP 5.6.');
//        }
//
//        uopz_redefine('WP_PLUGIN_DIR', __DIR__);
//
//        $wpLoader = $this->module();
//
//        $this->assertEquals(__DIR__, $wpLoader->getPluginsFolder());
//        $this->assertEquals(__DIR__ . '/foo/bar', $wpLoader->getPluginsFolder('foo/bar'));
//    }
//
//    /**
//     * It should handle absolute path for configFile parameter
//     *
//     * @test
//     */
//    public function should_handle_absolute_path_for_config_file_parameter()
//    {
//        $configFile = __FILE__;
//        $this->config['configFile'] = $configFile;
//
//        $wpLoader = $this->module();
//
//        $this->assertEquals([__FILE__], $wpLoader->_getConfigFiles());
//    }
//
//    /**
//     * It should handle multiple absolute and relative paths for config files
//     *
//     * @test
//     */
//    public function should_handle_multiple_absolute_and_relative_paths_for_config_files()
//    {
//        $filesHere = glob(__DIR__ . '/*.php');
//        $configFiles = [
//            basename(__FILE__),
//            reset($filesHere),
//            __FILE__
//        ];
//        $this->config['configFile'] = $configFiles;
//
//        $wpLoader = $this->module();
//
//        $this->assertEquals([
//            __FILE__,
//            reset($filesHere)
//        ], $wpLoader->_getConfigFiles(__DIR__));
//    }
//
//    /**
//     * It should allow setting the content folder from the module configuration
//     *
//     * @test
//     */
//    public function should_allow_setting_the_content_folder_from_the_module_configuration()
//    {
//        if (!extension_loaded('uopz')) {
//            $this->markTestSkipped('This test requires the uopz extension.');
//        }
//
//        if (PHP_VERSION_ID <= 70000) {
//            $this->markTestSkipped('Due to a uopz bug on PHP 5.6.');
//        }
//
//        uopz_undefine('WP_CONTENT_DIR');
//        uopz_undefine('WP_PLUGIN_DIR');
//
//        $wpRootDir = codecept_data_dir('folder-structures/wp-root-folder-2/wp');
//        $contentDir = codecept_data_dir('folder-structures/wp-root-folder-2/content');
//
//        $this->config['wpRootFolder '] = $wpRootDir;
//        $this->config['contentFolder'] = $contentDir;
//
//        $wpLoader = $this->module();
//        $constants = $wpLoader->_getConstants();
//
//        $this->assertArrayHasKey('WP_CONTENT_DIR', $constants);
//        $this->assertArrayNotHasKey('WP_PLUGIN_DIR', $constants);
//        $this->assertEquals($contentDir, $constants['WP_CONTENT_DIR']);
//        $this->assertEquals($contentDir . '/plugins', $wpLoader->getPluginsFolder());
//    }
//
//    /**
//     * It should get the content directory path from constant if set
//     *
//     * @test
//     */
//    public function should_get_the_content_directory_path_from_constant_if_set()
//    {
//        if (!extension_loaded('uopz')) {
//            $this->markTestSkipped('This test requires the uopz extension.');
//        }
//
//        if (PHP_VERSION_ID <= 70000) {
//            $this->markTestSkipped('Due to a uopz bug on PHP 5.6.');
//        }
//
//        $contentDir = codecept_data_dir('folder-structures/wp-root-folder-2/content');
//
//        uopz_redefine('WP_CONTENT_DIR', $contentDir);
//        uopz_undefine('WP_PLUGIN_DIR');
//
//        $wpRootDir = codecept_data_dir('folder-structures/wp-root-folder-2/wp');
//
//        $this->config['wpRootFolder '] = $wpRootDir;
//        $this->config['contentFolder'] = $contentDir;
//
//        $wpLoader = $this->module();
//        $constants = $wpLoader->_getConstants();
//
//        $this->assertArrayNotHasKey('WP_CONTENT_DIR', $constants);
//        $this->assertArrayNotHasKey('WP_PLUGIN_DIR', $constants);
//        $this->assertEquals($contentDir, $wpLoader->getContentFolder());
//        $this->assertEquals($contentDir . '/plugins', $wpLoader->getPluginsFolder());
//    }
//
//    /**
//     * It should allow setting content and pluging dir independently
//     *
//     * @test
//     */
//    public function should_allow_setting_content_and_pluging_dir_independently()
//    {
//        if (!extension_loaded('uopz')) {
//            $this->markTestSkipped('This test requires the uopz extension.');
//        }
//
//        if (PHP_VERSION_ID <= 70000) {
//            $this->markTestSkipped('Due to a uopz bug on PHP 5.6.');
//        }
//
//        uopz_undefine('WP_CONTENT_DIR');
//        uopz_undefine('WP_PLUGIN_DIR');
//
//        $wpRootDir = codecept_data_dir('folder-structures/wp-root-folder-2/wp');
//        $contentDir = codecept_data_dir('folder-structures/wp-root-folder-2/content');
//        $pluginsDir = __DIR__;
//
//        $this->config['wpRootFolder '] = $wpRootDir;
//        $this->config['contentFolder'] = $contentDir;
//        $this->config['pluginsFolder'] = $pluginsDir;
//
//        $wpLoader = $this->module();
//        $constants = $wpLoader->_getConstants();
//
//        $this->assertArrayHasKey('WP_CONTENT_DIR', $constants);
//        $this->assertArrayHasKey('WP_PLUGIN_DIR', $constants);
//        $this->assertEquals($contentDir, $wpLoader->getContentFolder());
//        $this->assertEquals($pluginsDir, $wpLoader->getPluginsFolder());
//    }
//
//    /**
//     * It should allow setting content and plugin dir w/ constants
//     *
//     * @test
//     */
//    public function should_allow_setting_content_and_plugin_dir_w_constants()
//    {
//        if (!extension_loaded('uopz')) {
//            $this->markTestSkipped('This test requires the uopz extension.');
//        }
//
//        if (PHP_VERSION_ID <= 70000) {
//            $this->markTestSkipped('Due to a uopz bug on PHP 5.6.');
//        }
//
//        $wpRootDir = codecept_data_dir('folder-structures/wp-root-folder-2/wp');
//        $contentDir = codecept_data_dir('folder-structures/wp-root-folder-2/content');
//        $pluginsDir = __DIR__;
//
//        uopz_undefine('WP_CONTENT_DIR');
//        uopz_redefine('WP_CONTENT_DIR', $contentDir);
//        $this->assertEquals($contentDir, WP_CONTENT_DIR);
//        uopz_undefine('WP_PLUGIN_DIR');
//        uopz_redefine('WP_PLUGIN_DIR', $pluginsDir);
//        $this->assertEquals($pluginsDir, WP_PLUGIN_DIR);
//
//        $this->config['wpRootFolder '] = $wpRootDir;
//
//        $wpLoader = $this->module();
//        $constants = $wpLoader->_getConstants();
//
//        $this->assertArrayNotHasKey('WP_CONTENT_DIR', $constants);
//        $this->assertArrayNotHasKey('WP_PLUGIN_DIR', $constants);
//        $this->assertEquals($contentDir, $wpLoader->getContentFolder());
//        $this->assertEquals($pluginsDir, $wpLoader->getPluginsFolder());
//    }
}
