<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\Fork;
use lucatume\WPBrowser\Tests\Traits\InstallationMocks;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class WPLoaderLoadOnlyTest extends Unit
{
    use InstallationMocks;

    public function testWillLoadWordPressInBeforeSuiteWhenLoadOnlyIsTrue(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => true,
        ]);

        Fork::executeClosure(function () use ($module) {
            // WordPress' functions are stubbed by wordpress-stubs in unit tests: override them to do something.
            $did_actions = [];
            uopz_set_return('do_action', static function ($action) use (&$did_actions) {
                $did_actions[$action] = true;
            }, true);
            uopz_set_return('did_action', static function ($action) use (&$did_actions) {
                return isset($did_actions[$action]);
            }, true);
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                $this->fail('The WPLoader::installAndBootstrapInstallation method should not be called');
            }, true);

            $module->_initialize();

            $this->assertFalse($module->_didLoadWordPress());

            $module->_beforeSuite();

            $this->assertTrue($module->_didLoadWordPress());
        });
    }

    public function testWillLoadWordPressInInitializeWhenLoadOnlyIsFalse(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
        ]);

        Fork::executeClosure(function () use ($module) {
            // WordPress' functions are stubbed by wordpress-stubs in unit tests: override them to do something.
            $did_actions = [];
            uopz_set_return('do_action', static function ($action) use (&$did_actions) {
                $did_actions[$action] = true;
            }, true);
            uopz_set_return('did_action', static function ($action) use (&$did_actions) {
                return isset($did_actions[$action]);
            }, true);
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertTrue($module->_didLoadWordPress());

            $module->_beforeSuite();

            $this->assertTrue($module->_didLoadWordPress());
        });
    }

    public function testWillDefineDBConstantsWhenLoadOnlyTrue(): void{
        [$wpRootFolder] = $this->makeMockConfiguredInstallation('', [
            'dbUser' => 'production_user',
            'dbPassword' => 'production_password',
            'dbHost' => '10.0.0.1:8876',
            'dbName' => 'test_db',
        ]);
        file_put_contents($wpRootFolder . '/wp-load.php', '<?php include_once __DIR__ . "/wp-config.php"; do_action("wp_loaded");');
        $testDbUser = Env::get('WORDPRESS_DB_USER');
        $testDbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $testDbHost = '127.0.0.1:' . Env::get('WORDPRESS_DB_LOCALHOST_PORT');
        $testDbName = Env::get('WORDPRESS_DB_NAME');
        $testDbUrl = sprintf(
            'mysql://%s:%s@%s/%s',
            $testDbUser,
            $testDbPassword,
            $testDbHost,
            $testDbName
        );
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $testDbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => true,
        ]);

        Fork::executeClosure(function () use ($testDbName, $testDbHost, $testDbPassword, $testDbUser, $module) {
            // WordPress' functions are stubbed by wordpress-stubs in unit tests: override them to do something.
            $did_actions = [];
            uopz_set_return('do_action', static function ($action) use (&$did_actions) {
                $did_actions[$action] = true;
            }, true);
            uopz_set_return('did_action', static function ($action) use (&$did_actions) {
                return isset($did_actions[$action]);
            }, true);
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();
            $module->_beforeSuite();

            $this->assertTrue($module->_didLoadWordPress());
            $this->assertEquals($testDbUser, DB_USER);
            $this->assertEquals($testDbPassword, DB_PASSWORD);
            $this->assertEquals($testDbHost, DB_HOST);
            $this->assertEquals($testDbName, DB_NAME);
            $this->assertEquals('1', getenv('WPBROWSER_LOAD_ONLY'));
        });
    }

    public function testWillLoadConfigFileWhenLoadOnlyTrue(): void{
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $configDir = FS::tmpDir('config_', [
            'test-config.php' => '<?php define("TEST_CONFIG", true);'
        ]);
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => true,
            'configFile' => $configDir . '/test-config.php'
        ]);

        Fork::executeClosure(function () use ($module) {
            // WordPress' functions are stubbed by wordpress-stubs in unit tests: override them to do something.
            $did_actions = [];
            uopz_set_return('do_action', static function ($action) use (&$did_actions) {
                $did_actions[$action] = true;
            }, true);
            uopz_set_return('did_action', static function ($action) use (&$did_actions) {
                return isset($did_actions[$action]);
            }, true);
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();
            $module->_beforeSuite();

            $this->assertTrue($module->_didLoadWordPress());
            $this->assertTrue(defined('TEST_CONFIG'));
        });
    }

    public function testWillLoadMultipleConfigFilesWhenLoadOnlyTrue(): void{
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $configDir = FS::tmpDir('config_', [
            'test-config.php' => '<?php define("TEST_CONFIG", true);',
            'test-config2.php' => '<?php define("TEST_CONFIG2", true);'
        ]);
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => true,
            'configFile' => [$configDir . '/test-config.php', $configDir . '/test-config2.php']
        ]);

        Fork::executeClosure(function () use ($module) {
            // WordPress' functions are stubbed by wordpress-stubs in unit tests: override them to do something.
            $did_actions = [];
            uopz_set_return('do_action', static function ($action) use (&$did_actions) {
                $did_actions[$action] = true;
            }, true);
            uopz_set_return('did_action', static function ($action) use (&$did_actions) {
                return isset($did_actions[$action]);
            }, true);
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();
            $module->_beforeSuite();

            $this->assertTrue($module->_didLoadWordPress());
            $this->assertTrue(defined('TEST_CONFIG'));
            $this->assertTrue(defined('TEST_CONFIG2'));
        });
    }
}
