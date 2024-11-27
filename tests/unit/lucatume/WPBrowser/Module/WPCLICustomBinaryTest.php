<?php

namespace unit\lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPCLI;
use lucatume\WPBrowser\Utils\Filesystem;

class WPCLICustomBinaryTest extends Unit
{
    private ?string $homeBackup = null;
    private string|null|false $envCacheDirValue = null;
    private string $wpCliCacheDir;
    private string $wpCliCacheDirBackup;

    public function setUp(): void
    {
        parent::setUp();
        if (isset($_SERVER['HOME'])) {
            $this->homeBackup = $_SERVER['HOME'];
        }
        $this->wpCliCacheDir = Filesystem::cacheDir() . '/wp-cli';
        $this->wpCliCacheDirBackup = dirname($this->wpCliCacheDir) . '/wp-cli-cache-dir-backup';
        if (is_dir($this->wpCliCacheDirBackup)) {
            Filesystem::rrmdir($this->wpCliCacheDirBackup);
        }
        if (is_dir($this->wpCliCacheDir)) {
            rename($this->wpCliCacheDir, $this->wpCliCacheDirBackup);
        }
        $this->assertDirectoryDoesNotExist($this->wpCliCacheDir);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if ($this->homeBackup !== null) {
            $_SERVER['HOME'] = $this->homeBackup;
        }
        if (is_dir($this->wpCliCacheDir)) {
            Filesystem::rrmdir($this->wpCliCacheDir);
        }
        if (is_dir($this->wpCliCacheDirBackup)) {
            rename($this->wpCliCacheDirBackup, $this->wpCliCacheDir);
        }
    }

    public function test_configuration_allows_custom_binary(): void
    {
        $binary = codecept_data_dir('bins/wp-cli-custom-bin');
        $moduleContainer = new ModuleContainer(new Di(), []);

        $module = new WPCLI($moduleContainer, [
            'path' => 'var/wordpress',
            'bin' => $binary,
        ]);
        $module->cli(['core', 'version']);

        $this->assertEquals(
            'Hello from wp-cli custom binary',
            $module->grabLastShellOutput()
        );
        $this->assertDirectoryDoesNotExist($this->wpCliCacheDir);
    }

    public function test_configuration_supports_tilde_for_home_in_custom_binary(): void
    {
        $_SERVER['HOME'] = codecept_data_dir();
        $binary = '~/bins/wp-cli-custom-bin';
        $binaryPath = codecept_data_dir('bins/wp-cli-custom-bin');
        $moduleContainer = new ModuleContainer(new Di(), []);
        // Sanity check.
        $this->assertEquals(rtrim(codecept_data_dir(), '\\/'), Filesystem::homeDir());

        $module = new WPCLI($moduleContainer, [
            'path' => 'var/wordpress',
            'bin' => $binary,
        ]);
        $module->cli(['core', 'version']);

        $this->assertEquals(
            'Hello from wp-cli custom binary',
            $module->grabLastShellOutput()
        );
        $this->assertDirectoryDoesNotExist($this->wpCliCacheDir);
    }

    public function test_throws_if_custom_binary_does_not_exist(): void
    {
        $binary = codecept_data_dir('bins/not-a-bin');
        $moduleContainer = new ModuleContainer(new Di(), []);

        $this->expectException(ModuleConfigException::class);

        $module = new WPCLI($moduleContainer, [
            'path' => 'var/wordpress',
            'bin' => $binary,
        ]);
        $module->cli(['core', 'version']);
    }

    public function test_throws_if_custom_binary_is_not_executable(): void
    {
        $binary = codecept_data_dir('bins/not-executable');
        $moduleContainer = new ModuleContainer(new Di(), []);

        $this->expectException(ModuleConfigException::class);

        $module = new WPCLI($moduleContainer, [
            'path' => 'var/wordpress',
            'bin' => $binary,
        ]);
        $module->cli(['core', 'version']);
    }
}
