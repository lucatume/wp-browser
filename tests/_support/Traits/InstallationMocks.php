<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;

trait InstallationMocks
{
    use TmpFilesCleanup;

    /**
     * @return array{0: string, 1: string}
     */
    private function makeMockConfiguredInstallation(string $phpExtra = '', array $overrides = []): array
    {
        $dbUser = $overrides['dbUser'] ?? Env::get('WORDPRESS_DB_USER');
        $dbPassword = $overrides['dbPassword'] ?? Env::get('WORDPRESS_DB_PASSWORD');
        if(!isset($overrides['dbHost'])){
            $dbLocalhostPort = $overrides['dbLocalhostPort'] ?? Env::get('WORDPRESS_DB_LOCALHOST_PORT');
            $dbHost = '127.0.0.1:' . $dbLocalhostPort;
        } else {
            $dbHost = $overrides['dbHost'];
        }
        $dbName = $overrides['dbName'] ?? Env::get('WORDPRESS_DB_NAME');
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
            define('DB_HOST', '$dbHost');
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
            $phpExtra
            PHP,
            'wp-settings.php' => '<?php',
            'wp-load.php' => '<?php do_action("wp_loaded");',
        ]);
        $dbUrl = sprintf(
            'mysql://%s:%s@%s/%s',
            $dbUser,
            $dbPassword,
            $dbHost,
            $dbName
        );

        return [$wpRootFolder, $dbUrl];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function makeMockScaffoldedInstallation(): array
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

}
