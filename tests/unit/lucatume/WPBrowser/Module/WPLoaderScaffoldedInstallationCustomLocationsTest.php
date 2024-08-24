<?php

namespace unit\lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Tests\Traits\Fork;
use lucatume\WPBrowser\Tests\Traits\InstallationMocks;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\InstallationState\Configured;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;

class WPLoaderScaffoldedInstallationCustomLocationsTest extends Unit
{
    use InstallationMocks;

    public function testUsesDefaultContentLocationInConfiguredInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $module) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Configured::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/some-path', $module->getContentFolder('some-path'));
            $this->assertEquals(
                $wpRootFolder . '/wp-content/some/other/path/',
                $module->getContentFolder('/some/other/path/')
            );
            $this->assertEquals($wpRootFolder . '/wp-content/plugins/', $module->getPluginsFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomContentLocationFromConfigConstantInConfiguredInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $contentDir = FS::tmpDir('custom-content-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WP_CONTENT_DIR' => $contentDir
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $contentDir, $module) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Configured::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($contentDir . '/', $module->getContentFolder());
            $this->assertEquals($contentDir . '/some-path', $module->getContentFolder('some-path'));
            $this->assertEquals($contentDir . '/some/other/path/', $module->getContentFolder('/some/other/path/'));
            $this->assertEquals($contentDir . '/plugins/', $module->getPluginsFolder());
            $this->assertEquals($contentDir . '/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomPluginsLocationFromConfigParameterInConfiguredInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $pluginsDir = FS::tmpDir('custom-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'pluginsFolder' => $pluginsDir
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $pluginsDir, $module) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Configured::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($pluginsDir . '/', $module->getPluginsFolder());
            $this->assertEquals($pluginsDir . '/some-path', $module->getPluginsFolder('some-path'));
            $this->assertEquals($pluginsDir . '/some/other/path/', $module->getPluginsFolder('/some/other/path/'));
            $this->assertEquals($wpRootFolder . '/wp-content/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomPluginsLocationFromConfigConstantInConfiguredInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $pluginsDir = FS::tmpDir('custom-plugins-dir');
        $pluginsDir2 = FS::tmpDir('custom-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WP_PLUGIN_DIR' => $pluginsDir,
            'pluginsFolder' => $pluginsDir2
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $pluginsDir, $module) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Configured::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($pluginsDir . '/', $module->getPluginsFolder());
            $this->assertEquals($pluginsDir . '/some-path', $module->getPluginsFolder('some-path'));
            $this->assertEquals($pluginsDir . '/some/other/path/', $module->getPluginsFolder('/some/other/path/'));
            $this->assertEquals($wpRootFolder . '/wp-content/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomMuPluginsLocationFromConfigConstantInConfiguredInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation();
        $muPluginsDir = FS::tmpDir('custom-plugins-dir');
        $contentDir = FS::tmpDir('custom-content-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WPMU_PLUGIN_DIR' => $muPluginsDir,
            'WP_CONTENT_DIR' => $contentDir
        ]);

        Fork::executeClosure(function () use ($contentDir, $wpRootFolder, $muPluginsDir, $module) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Configured::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($contentDir . '/', $module->getContentFolder());
            $this->assertEquals($contentDir . '/plugins/', $module->getPluginsFolder());
            $this->assertEquals($muPluginsDir . '/', $module->getMuPluginsFolder());
            $this->assertEquals($muPluginsDir . '/some-path', $module->getMuPluginsFolder('some-path'));
            $this->assertEquals($muPluginsDir . '/some/other/path/', $module->getMuPluginsFolder('/some/other/path/'));
        });
    }

    public function testUsesDefaultContentLocationInScaffoldedInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockScaffoldedInstallation();
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $module) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Scaffolded::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/some-path', $module->getContentFolder('some-path'));
            $this->assertEquals(
                $wpRootFolder . '/wp-content/some/other/path/',
                $module->getContentFolder('/some/other/path/')
            );
            $this->assertEquals($wpRootFolder . '/wp-content/plugins/', $module->getPluginsFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomContentLocationFromConfigConstantInScaffoldedInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockScaffoldedInstallation();
        $contentDir = FS::tmpDir('custom-content-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WP_CONTENT_DIR' => $contentDir
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $module, $contentDir) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Scaffolded::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($contentDir . '/', $module->getContentFolder());
            $this->assertEquals($contentDir . '/some-path', $module->getContentFolder('some-path'));
            $this->assertEquals($contentDir . '/some/other/path/', $module->getContentFolder('/some/other/path/'));
            $this->assertEquals($contentDir . '/plugins/', $module->getPluginsFolder());
            $this->assertEquals($contentDir . '/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomPluginsLocationFromConfigParameterInScaffoldedInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockScaffoldedInstallation();
        $pluginsDir = FS::tmpDir('custom-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'pluginsFolder' => $pluginsDir
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $module, $pluginsDir) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Scaffolded::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($pluginsDir . '/', $module->getPluginsFolder());
            $this->assertEquals($pluginsDir . '/some-path', $module->getPluginsFolder('some-path'));
            $this->assertEquals($pluginsDir . '/some/other/path/', $module->getPluginsFolder('/some/other/path/'));
            $this->assertEquals($wpRootFolder . '/wp-content/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomPluginsLocationFromConfigConstantInScaffoldedInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockScaffoldedInstallation();
        $pluginsDir = FS::tmpDir('custom-plugins-dir');
        $pluginsDir2 = FS::tmpDir('custom-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'pluginsFolder' => $pluginsDir,
            'WP_PLUGIN_DIR' => $pluginsDir2
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $module, $pluginsDir2) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Scaffolded::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($pluginsDir2 . '/', $module->getPluginsFolder());
            $this->assertEquals($pluginsDir2 . '/some-path', $module->getPluginsFolder('some-path'));
            $this->assertEquals($pluginsDir2 . '/some/other/path/', $module->getPluginsFolder('/some/other/path/'));
            $this->assertEquals($wpRootFolder . '/wp-content/mu-plugins/', $module->getMuPluginsFolder());
        });
    }

    public function testUsesCustomMuPluginsLocationFromConfigConstantInScaffoldedInstallation(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockScaffoldedInstallation();
        $muPluginsDir = FS::tmpDir('custom-mu-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WPMU_PLUGIN_DIR' => $muPluginsDir
        ]);

        Fork::executeClosure(function () use ($wpRootFolder, $module, $muPluginsDir) {
            // Partial mocking the function that would load WordPress.
            uopz_set_return(WPLoader::class, 'installAndBootstrapInstallation', function () {
                return true;
            }, true);

            $module->_initialize();

            $this->assertInstanceOf(Scaffolded::class, $module->getInstallation()->getState());
            $this->assertEquals($wpRootFolder . '/', $module->getWpRootFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/', $module->getContentFolder());
            $this->assertEquals($wpRootFolder . '/wp-content/plugins/', $module->getPluginsFolder());
            $this->assertEquals($muPluginsDir . '/', $module->getMuPluginsFolder());
            $this->assertEquals($muPluginsDir . '/some-path', $module->getMuPluginsFolder('some-path'));
            $this->assertEquals($muPluginsDir . '/some/other/path/', $module->getMuPluginsFolder('/some/other/path/'));
        });
    }

    public function testThrowsIfContentDirConstantIsSetInWpConfigInConfiguredInstallation(): void{
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation(
            <<< PHP
            define('WP_CONTENT_DIR', '/some/other/path');
            PHP
        );
        $contentDir = FS::tmpDir('custom-content-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WP_CONTENT_DIR' => $contentDir
        ]);

        Fork::executeClosure(function () use ($module) {
            try {
                $module->_initialize();
            } catch (\Throwable $e) {
                $this->assertInstanceOf(ModuleConfigException::class, $e);
                $this->assertStringContainsString(
                    'Both the installation wp-config.php file and the module configuration define a WP_CONTENT_DIR constant: only one can be set.',
                    $e->getMessage()
                );
            }
        });
    }

    public function testThrowsIfPluginsDirConstantIsSetInWpConfigInConfiguredInstallation(): void{
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation(
            <<< PHP
            define('WP_PLUGIN_DIR', '/some/other/path');
            PHP
        );
        $pluginsDir = FS::tmpDir('custom-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WP_PLUGIN_DIR' => $pluginsDir
        ]);

        Fork::executeClosure(function () use ($module) {
            try {
                $module->_initialize();
            } catch (\Throwable $e) {
                $this->assertInstanceOf(ModuleConfigException::class, $e);
                $this->assertStringContainsString(
                    'Both the installation wp-config.php file and the module configuration define a WP_PLUGIN_DIR constant: only one can be set.',
                    $e->getMessage()
                );
            }
        });
    }

    public function testThrowsIfMuPluginsDirConstantIsSetInWpConfigInConfiguredInstallation(): void{
        [$wpRootFolder, $dbUrl] = $this->makeMockConfiguredInstallation(
            <<< PHP
            define('WPMU_PLUGIN_DIR', '/some/other/path');
            PHP
        );
        $muPluginsDir = FS::tmpDir('custom-mu-plugins-dir');
        $moduleContainer = new ModuleContainer(new Di(), []);
        $module = new WPLoader($moduleContainer, [
            'dbUrl' => $dbUrl,
            'wpRootFolder' => $wpRootFolder,
            'loadOnly' => false,
            'WPMU_PLUGIN_DIR' => $muPluginsDir
        ]);

        Fork::executeClosure(function () use ($module) {
            try {
                $module->_initialize();
            } catch (\Throwable $e) {
                $this->assertInstanceOf(ModuleConfigException::class, $e);
                $this->assertStringContainsString(
                    'Both the installation wp-config.php file and the module configuration define a WPMU_PLUGIN_DIR constant: only one can be set.',
                    $e->getMessage()
                );
            }
        });
    }
}
