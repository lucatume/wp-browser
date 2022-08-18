<?php
/**
 * WordPress Core PHPUnit test suite configuration file modified to work with wp-browser and Codeception.
 *
 * @var array<string,mixed> $wpLoaderConfig A map of configuration values hydrated by the WPLoader module.
 */

$didReadConfigFromEnvVar = false;

if (!isset($wpLoaderConfig)) {
    if (!(defined('WP_INSTALLING') && WP_INSTALLING)) {
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
if (!(defined('WP_INSTALLING') && WP_INSTALLING)) {
    /**
     * Codeception 5 requires a minimum PHPUnit version of 9, Yoast PHPUnit polyfills are not required
     * but the bootstrap file will look for them. Here we set up the context to make sure that check
     * will pass.
     */
    require_once __DIR__ . '/stubs/yoast-phpunit-polyfills-autoload.php';
    require_once __DIR__ . '/stubs/yoast-phpunit-polyfills-testcase.php';
}

// Resolve the ABSPATH to a real file path, if possible, and format it correctly.
$abspath = realpath($wpLoaderConfig['wpRootFolder']);
$abspath = $abspath ?? $wpLoaderConfig['wpRootFolder'];
$abspath = rtrim($abspath, '\\/') . '/';

foreach ([
             'ABSPATH' => $abspath,
             'WP_DEFAULT_THEME' => $wpLoaderConfig['theme'],
             'WP_TESTS_MULTISITE' => $wpLoaderConfig['multisite'],
             'WP_MULTISITE' => $wpLoaderConfig['multisite'],
             'MULTISITE' => $wpLoaderConfig['multisite'],
             'WP_DEBUG' => $wpLoaderConfig['wpDebug'],
             'DB_NAME' => $wpLoaderConfig['dbName'],
             'DB_USER' => $wpLoaderConfig['dbUser'],
             'DB_PASSWORD' => $wpLoaderConfig['dbPassword'],
             'DB_HOST' => $wpLoaderConfig['dbHost'],
             'DB_CHARSET' => $wpLoaderConfig['dbCharset'] ?? 'utf8',
             'DB_COLLATE' => $wpLoaderConfig['dbCollate'] ?? '',
             'AUTH_KEY' => $wpLoaderConfig['authKey'],
             'SECURE_AUTH_KEY' => $wpLoaderConfig['secureAuthKey'],
             'LOGGED_IN_KEY' => $wpLoaderConfig['loggedInKey'],
             'NONCE_KEY' => $wpLoaderConfig['nonceKey'],
             'AUTH_SALT' => $wpLoaderConfig['authSalt'],
             'SECURE_AUTH_SALT' => $wpLoaderConfig['secureAuthSalt'],
             'LOGGED_IN_SALT' => $wpLoaderConfig['loggedInSalt'],
             'NONCE_SALT' => $wpLoaderConfig['nonceSalt'],
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
