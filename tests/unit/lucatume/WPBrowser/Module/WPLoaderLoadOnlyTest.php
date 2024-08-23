<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\Fork;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class WPLoaderLoadOnlyTest extends Unit
{
    use TmpFilesCleanup;

    private function makeMockWordPressInstallation(): array
    {
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dbLocalhostPort = Env::get('WORDPRESS_DB_LOCALHOST_PORT');
        $dbName = Env::get('WORDPRESS_DB_NAME');
        $wpRootFolder = FS::tmpDir('wploader_', [
            'wp-includes' => [
                'version.php' => <<< PHP
                <?php
                \$wp_version = '6.5';
                \$wp_db_version = 57155;
                \$tinymce_version = '49110-20201110';
                \$required_php_version = '7.0.0';
                \$required_mysql_version = '5.5.5';
                PHP
            ],
            'wp-config.php' => <<< PHP
            <?php
            define('DB_NAME', '$dbName');
            define('DB_USER', '$dbUser');
            define('DB_PASSWORD', '$dbPassword');
            define('DB_HOST', '127.0.0.1:$dbLocalhostPort');
            define('DB_CHARSET', 'utf8');
            define('DB_COLLATE', '');
            global \$table_prefix;
            \$table_prefix = 'wp_';
            define('AUTH_KEY', 'auth-key-salt');
            define('SECURE_AUTH_KEY', 'secure-auth-key-salt');
            define('LOGGED_IN_KEY', 'logged-in-key-salt');
            define('NONCE_KEY', 'nonce-key-salt');
            define('AUTH_SALT', 'auth-salt');
            define('SECURE_AUTH_SALT', 'secure-auth-salt');
            define('LOGGED_IN_SALT', 'logged-in-salt');
            define('NONCE_SALT', 'nonce-salt');
            PHP,
            'wp-settings.php' => '<?php',
            'wp-load.php' => '<?php do_action("wp_loaded");',
        ]);
        $dbUrl = sprintf(
            'mysql://%s:%s@127.0.0.1:%d/%s',
            $dbUser,
            $dbPassword,
            $dbLocalhostPort,
            $dbName
        );

        return [$wpRootFolder, $dbUrl];
    }

    public function testWillLoadWordPressInBeforeSuiteWhenLoadOnlyIsTrue(): void
    {
        [$wpRootFolder, $dbUrl] = $this->makeMockWordPressInstallation();
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
        [$wpRootFolder, $dbUrl] = $this->makeMockWordPressInstallation();
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
