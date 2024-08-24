<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\Fork;
use lucatume\WPBrowser\Tests\Traits\InstallationMocks;

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
}
