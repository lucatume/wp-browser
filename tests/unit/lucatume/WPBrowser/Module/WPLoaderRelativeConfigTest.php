<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\Env;

class WPLoaderRelativeConfigTest extends Unit
{
    private string $originalWorkingDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalWorkingDirectory = getcwd();
    }

    protected function tearDown(): void
    {
        chdir($this->originalWorkingDirectory);
        parent::tearDown();
    }

    public function testRelativeContentDirWithSiteProject(): void
    {
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dbLocalhostPort = Env::get('WORDPRESS_DB_LOCALHOST_PORT');
        $dbName = Env::get('WORDPRESS_DB_NAME');
        $dbUrl = sprintf(
            'mysql://%s:%s@127.0.0.1:%d/%s',
            $dbUser,
            $dbPassword,
            $dbLocalhostPort,
            $dbName
        );
        $moduleContainer = new ModuleContainer(new Di(), []);

        // Change the root to another directory that is not the Codeception root.
        chdir(codecept_data_dir('root-dirs/some/nested/dir'));

        $module = new WPLoader($moduleContainer, [
            'wpRootFolder' => codecept_root_dir('var/wordpress'),
            'dbUrl' => $dbUrl,
            // Set the content dir to a path that will not resolve relative to the current working directory.
            'WP_CONTENT_DIR' => 'tests/_data/_content',
        ]);

        $this->assertEquals(
            codecept_root_dir('tests/_data/_content'),
            $module->_getConfig('WP_CONTENT_DIR')
        );
    }

    public function testRelativePluginDirWithSiteProject(): void
    {
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dbLocalhostPort = Env::get('WORDPRESS_DB_LOCALHOST_PORT');
        $dbName = Env::get('WORDPRESS_DB_NAME');
        $dbUrl = sprintf(
            'mysql://%s:%s@127.0.0.1:%d/%s',
            $dbUser,
            $dbPassword,
            $dbLocalhostPort,
            $dbName
        );
        $moduleContainer = new ModuleContainer(new Di(), []);

        // Change the root to another directory that is not the Codeception root.
        chdir(codecept_data_dir('root-dirs/some/nested/dir'));

        $module = new WPLoader($moduleContainer, [
            'wpRootFolder' => codecept_root_dir('var/wordpress'),
            'dbUrl' => $dbUrl,
            // Set the plugin dir to a path that will not resolve relative to the current working directory.
            'WP_PLUGIN_DIR' => 'tests/_data/_plugins',
        ]);

        $this->assertEquals(
            codecept_root_dir('tests/_data/_plugins'),
            $module->_getConfig('WP_PLUGIN_DIR')
        );
    }

    public function testRelativeMuPluginDirWithSiteProject(): void
    {
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dbLocalhostPort = Env::get('WORDPRESS_DB_LOCALHOST_PORT');
        $dbName = Env::get('WORDPRESS_DB_NAME');
        $dbUrl = sprintf(
            'mysql://%s:%s@127.0.0.1:%d/%s',
            $dbUser,
            $dbPassword,
            $dbLocalhostPort,
            $dbName
        );
        $moduleContainer = new ModuleContainer(new Di(), []);

        // Change the root to another directory that is not the Codeception root.
        chdir(codecept_data_dir('root-dirs/some/nested/dir'));

        $module = new WPLoader($moduleContainer, [
            'wpRootFolder' => codecept_root_dir('var/wordpress'),
            'dbUrl' => $dbUrl,
            // Set the mu-plugin dir to a path that will not resolve relative to the current working directory.
            'WPMU_PLUGIN_DIR' => 'tests/_data/_mu-plugins',
        ]);

        $this->assertEquals(
            codecept_root_dir('tests/_data/_mu-plugins'),
            $module->_getConfig('WPMU_PLUGIN_DIR')
        );
    }
}
