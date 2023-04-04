<?php
/**
 * WordPress Core PHPUnit test suite configuration file modified to work with wp-browser and Codeception.
 *
 * @var array<string,mixed> $wpLoaderConfig A map of configuration values hydrated by the WPLoader module.
 */

use lucatume\WPBrowser\Utils\CorePHPUnit;

$didReadConfigFromEnvVar = false;

$wpInstalling = defined('WP_INSTALLING') && WP_INSTALLING;

if (!isset($wpLoaderConfig)) {
    if (!($wpInstalling)) {
        throw new RuntimeException('This file should be included from either the WPLoader Module or the ' .
            'WordPress Core PHUnit suite bootstrap file.');
    }

    $wpLoaderEnvVar = getenv('WPLOADER_CONFIG');
    $decodedWpLoaderEnvVar = base64_decode($wpLoaderEnvVar, true);

    if ($decodedWpLoaderEnvVar === false) {
        throw new RuntimeException('The WPLOADER_CONFIG environment variable is empty or not a valid base64 ' .
            'encoded string.');
    }

    $wpLoaderConfig = unserialize($decodedWpLoaderEnvVar, ['allowed_classes' => false]);

    if ($wpLoaderConfig === false || !is_array($wpLoaderConfig)) {
        throw new RuntimeException('The WPLOADER_CONFIG environment variable is not a valid' .
            ' serialized array.');
    }

    // Clean up.
    unset($wpLoaderEnvVar);

    $didReadConfigFromEnvVar = true;
}

// This file will be loaded by both the bootstrap and the installation file: do not include on the latter.
if (!$wpInstalling && empty($wpLoaderIncludeWpSettings)) {
    /**
     * Codeception 5 requires a minimum PHPUnit version of 9, Yoast PHPUnit polyfills are not required
     * but the bootstrap file will look for them. Here we set up the context to make sure that check
     * will pass.
     */
    require_once CorePHPUnit::path('/yoast-phpunit-polyfills-autoload-stub.php');
    require_once CorePHPUnit::path('/yoast-phpunit-polyfills-testcase-stub.php');
}

$abspath = rtrim($wpLoaderConfig['wpRootFolder'], '\\/') . '/';

foreach ([
             'ABSPATH' => $abspath,
             'WP_DEFAULT_THEME' => $wpLoaderConfig['theme'],
             'WP_TESTS_MULTISITE' => $wpLoaderConfig['multisite'],
             'WP_DEBUG' => true,
             'DB_NAME' => $wpLoaderConfig['dbName'],
             'DB_USER' => $wpLoaderConfig['dbUser'],
             'DB_PASSWORD' => $wpLoaderConfig['dbPassword'],
             'DB_HOST' => $wpLoaderConfig['dbHost'],
             'DB_CHARSET' => $wpLoaderConfig['dbCharset'] ?? 'utf8',
             'DB_COLLATE' => $wpLoaderConfig['dbCollate'] ?? '',
             'AUTH_KEY' => $wpLoaderConfig['AUTH_KEY'],
             'SECURE_AUTH_KEY' => $wpLoaderConfig['SECURE_AUTH_KEY'],
             'LOGGED_IN_KEY' => $wpLoaderConfig['LOGGED_IN_KEY'],
             'NONCE_KEY' => $wpLoaderConfig['NONCE_KEY'],
             'AUTH_SALT' => $wpLoaderConfig['AUTH_SALT'],
             'SECURE_AUTH_SALT' => $wpLoaderConfig['SECURE_AUTH_SALT'],
             'LOGGED_IN_SALT' => $wpLoaderConfig['LOGGED_IN_SALT'],
             'NONCE_SALT' => $wpLoaderConfig['NONCE_SALT'],
             'WP_TESTS_DOMAIN' => $wpLoaderConfig['domain'],
             'WP_TESTS_EMAIL' => $wpLoaderConfig['adminEmail'],
             'WP_TESTS_TITLE' => $wpLoaderConfig['title'],
             'WP_PHP_BINARY' => $wpLoaderConfig['phpBinary'],
             'WPLANG' => $wpLoaderConfig['language'] ?? '',
             'WP_RUN_CORE_TESTS' => false,
             'WP_TESTS_FORCE_KNOWN_BUGS' => false,
         ] as $const => $value) {
    if (!defined($const)) {
        define($const, $value);
    }
}
unset($const);

$table_prefix = $wpLoaderConfig['tablePrefix'];

/*
 * The `WP_MULTISITE` constant should not be defined at this stage: it will be picked up by the scripts from
 * environment variables and defined in the tests bootstrap scripts.
 */
foreach ([
             'WP_MULTISITE' => (int)$wpLoaderConfig['multisite'],
             'WP_TESTS_SKIP_INSTALL' => 0
         ] as $envVar => $value) {
    putenv($envVar . '=' . $value);
}
unset($envVar);

/*
 * This file will be included a first time by the Core PHPUnit suite bootstrap file, and then
 * a second time by the install script. We define an environment variable the first time
 * it's included to read it (see above) the second time it's included.
 */
if (!$didReadConfigFromEnvVar) {
    putenv('WPLOADER_CONFIG=' . base64_encode(serialize($wpLoaderConfig)));
}

// Clean up.
unset($abspath, $didReadConfigFromEnvVar, $wpLoaderConfig);

if (!empty($wpLoaderIncludeWpSettings)) {
    global $_wp_menu_nopriv, $_wp_submenu_nopriv;
    $_wp_menu_nopriv = $_wp_submenu_nopriv = [];
    require_once ABSPATH . 'wp-settings.php';
    unset($wpLoaderIncludeWpSettings);
}
