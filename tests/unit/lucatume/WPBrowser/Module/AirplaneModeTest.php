<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Filesystem;

class AirplaneModeTest extends Unit
{
    use UopzFunctions;

    private ModuleContainer $mockModuleContainer;

    private function module(array $config = []): AirplaneMode
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), []);

        return new AirplaneMode($this->mockModuleContainer, $config);
    }

    /**
     * It should throw if muPluginsDir is not a string
     *
     * @test
     * @dataProvider notStringDataProvider
     */
    public function should_throw_if_mu_plugins_dir_is_not_a_string(mixed $muPluginsDir): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The muPluginsDir configuration parameter must be a string.');

        $module = $this->module(['muPluginsDir' => $muPluginsDir]);
    }

    /**
     * It should throw if muPluginsDir is a file
     *
     * @test
     */
    public function should_throw_if_mu_plugins_dir_is_a_file(): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The muPluginsDir configuration parameter must be a directory.');

        $module = $this->module(['muPluginsDir' => __FILE__]);
    }

    /**
     * It should throw if muPluginsDir does not exist and cannot be created at init time
     *
     * @test
     */
    public function should_throw_if_mu_plugins_dir_does_not_exist_and_cannot_be_created_at_init_time(): void
    {
        $this->setFunctionReturn('mkdir', false);

        $module = $this->module(['muPluginsDir' => __DIR__ . '/not-existing']);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The muPluginsDir configuration parameter is not a directory and cannot be created.'
        );

        $module->_initialize();
    }

    /**
     * It should throw if the plugin cannot be copied to the mu-plugins directory
     *
     * @test
     */
    public function should_throw_if_the_plugin_cannot_be_copied_to_the_mu_plugins_directory(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');
        $module = $this->module(['muPluginsDir' => $tmpDir]);

        $this->setMethodReturn(Filesystem::class, 'recurseCopy', false);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The airplane-mode plugin could not be copied to the mu-plugins directory.'
        );

        $module->_initialize();
    }

    /**
     * It should throw if the loader cannot be moved in the mu-plugins directory
     *
     * @test
     */
    public function should_throw_if_the_loader_cannot_be_moved_in_the_mu_plugins_directory(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');
        $module = $this->module(['muPluginsDir' => $tmpDir]);

        $this->setFunctionReturn('rename', false);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The airplane-mode loader could not be moved to the mu-plugins directory.'
        );

        $module->_initialize();
    }

    /**
     * It should copy the plugin to the mu-plugins directory if symlink parameter not set
     *
     * @test
     */
    public function should_copy_the_plugin_to_the_mu_plugins_directory_if_symlink_parameter_not_set(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');

        $module = $this->module(['muPluginsDir' => $tmpDir . '/mu-plugins']);
        $module->_initialize();

        $this->assertTrue(is_dir($tmpDir . '/mu-plugins/airplane-mode'));
        $this->assertTrue(is_file($tmpDir . '/mu-plugins/airplane-mode-loader.php'));
        $this->assertFalse(is_link($tmpDir . '/mu-plugins/airplane-mode'));

        $module->_afterSuite();

        $this->assertFalse(is_dir($tmpDir . '/mu-plugins/airplane-mode'));
        $this->assertFalse(is_file($tmpDir . '/mu-plugins/airplane-mode-loader.php'));
        $this->assertTrue(is_dir($tmpDir . '/mu-plugins'));
    }

    /**
     * It should throw if the plugin cannot be symlinked to the mu-plugins directory
     *
     * @test
     */
    public function should_throw_if_the_plugin_cannot_be_symlinked_to_the_mu_plugins_directory(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');
        $module = $this->module(['muPluginsDir' => $tmpDir, 'symlink' => true]);

        $this->setFunctionReturn('symlink', function ($target, $link) use ($tmpDir) {
            return $link === $tmpDir . '/airplane-mode' ? false : symlink($target, $link);
        }, true);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The airplane-mode plugin could not be symlinked to the mu-plugins directory.'
        );

        $module->_initialize();
    }

    /**
     * It should throw if the loader cannot be symlinked to the mu-plugins directory
     *
     * @test
     */
    public function should_throw_if_the_loader_cannot_be_symlinked_to_the_mu_plugins_directory(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');
        $module = $this->module(['muPluginsDir' => $tmpDir, 'symlink' => true]);

        $this->setFunctionReturn('symlink', function ($target, $link) use ($tmpDir) {
            return $link === $tmpDir . '/airplane-mode-loader.php' ? false : symlink($target, $link);
        }, true);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The airplane-mode loader could not be symlinked to the mu-plugins directory.'
        );

        $module->_initialize();
    }

    /**
     * It should symlink the plugin to the mu-plugins directory if set to symlink
     *
     * @test
     */
    public function should_symlink_the_plugin_to_the_mu_plugins_directory_if_set_to_symlink(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');

        $module = $this->module(['muPluginsDir' => $tmpDir . '/mu-plugins', 'symlink' => true]);
        $module->_initialize();

        $this->assertTrue(is_dir($tmpDir . '/mu-plugins/airplane-mode'));
        $this->assertTrue(is_file($tmpDir . '/mu-plugins/airplane-mode-loader.php'));
        $this->assertTrue(is_link($tmpDir . '/mu-plugins/airplane-mode'));
        $this->assertTrue(is_link($tmpDir . '/mu-plugins/airplane-mode-loader.php'));

        $module->_afterSuite();

        $this->assertFalse(is_dir($tmpDir . '/mu-plugins/airplane-mode'));
        $this->assertFalse(is_link($tmpDir . '/mu-plugins/airplane-mode'));
        $this->assertFalse(is_file($tmpDir . '/mu-plugins/airplane-mode-loader.php'));
        $this->assertFalse(is_link($tmpDir . '/mu-plugins/airplane-mode-loader.php'));
        $this->assertTrue(is_dir($tmpDir . '/mu-plugins'));
    }

    /**
     * It should throw if plugin cannot be removed from the mu-plugins directory
     *
     * @test
     */
    public function should_throw_if_plugin_cannot_be_removed_from_the_mu_plugins_directory(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');
        $module = $this->module(['muPluginsDir' => $tmpDir]);

        $module->_initialize();

        $this->setMethodReturn(Filesystem::class, 'rrmdir', false);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The airplane-mode plugin could not be removed from the mu-plugins directory.'
        );

        $module->_afterSuite();
    }

    /**
     * It should throw if loader cannot be removed from the mu-plugins directory
     *
     * @test
     */
    public function should_throw_if_loader_cannot_be_removed_from_the_mu_plugins_directory(): void
    {
        $tmpDir = Filesystem::tmpDir('airplane-mode_');
        $module = $this->module(['muPluginsDir' => $tmpDir]);

        $module->_initialize();

        $this->setFunctionReturn('unlink', function (string $file) use ($tmpDir) {
            if ($file === $tmpDir . '/airplane-mode-loader.php') {
                return false;
            }
            return unlink($file);
        }, true);

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            'The airplane-mode loader could not be removed from the mu-plugins directory.'
        );

        $module->_afterSuite();
    }

    public function notStringDataProvider(): array
    {
        return [
            'null' => [null],
            'int' => [1],
            'float' => [1.1],
            'array' => [[]],
            'object' => [new \stdClass()],
            'bool' => [true],
        ];
    }
}
