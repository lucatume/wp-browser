<?php

use Codeception\Configuration;
use lucatume\WPBrowser\ManagedProcess\MysqlServer;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationState\Configured;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;

require_once dirname(__DIR__) . '/vendor/autoload.php';

global $_composer_autoload_path, $_composer_bin_dir;
$_composer_autoload_path = $_composer_autoload_path ?: (dirname(__DIR__) . '/vendor/autoload.php');
$_composer_bin_dir = $_composer_bin_dir ?: (dirname(__DIR__) . '/vendor/bin');
Configuration::append(['paths' => ['output' => dirname(__DIR__) . '/var/_output']]);

// Source the tests/.env file.
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__) . '/tests');
$env = $dotenv->load();

$mysqlServer = new MysqlServer(
    codecept_output_dir('_mysql_server'),
    $_ENV['WORDPRESS_DB_LOCALHOST_PORT'],
    $_ENV['WORDPRESS_DB_NAME'],
    $_ENV['WORDPRESS_DB_USER'],
    $_ENV['WORDPRESS_DB_PASSWORD']
);
$mysqlServer->start();

$wpRootDir = $env['WORDPRESS_ROOT_DIR'];

echo "Checking WordPress directory $wpRootDir ...\n";
if (!is_dir($wpRootDir) && mkdir($wpRootDir, 0777, true) && !is_dir($wpRootDir)) {
    throw new InvalidArgumentException("The WordPress root directory $wpRootDir does not exist.");
}

$installation = new Installation($wpRootDir);

echo "Checking WordPress installation in $wpRootDir ...\n";
if ($installation->getState() instanceof EmptyDir) {
    echo "Scaffolding WordPress in $wpRootDir ...\n";
    $installation = Installation::scaffold($wpRootDir);
}

echo "Creating database {$env['WORDPRESS_DB_NAME']} ...\n";
$db = new MysqlDatabase(
    $env['WORDPRESS_DB_NAME'],
    $env['WORDPRESS_DB_USER'],
    $env['WORDPRESS_DB_PASSWORD'],
    $env['WORDPRESS_DB_HOST'],
    $env['WORDPRESS_TABLE_PREFIX']
);
$db->create();

echo "Checking WordPress configuration in $wpRootDir ...\n";
if ($installation->getState() instanceof Scaffolded) {
    echo "Configuring WordPress in $wpRootDir ...\n";
    $configData = new ConfigurationData();
    $extraPHP = <<< PHP
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'DISABLE_WP_CRON', true );
PHP;
    $configData->setExtraPHP($extraPHP);
    $installation = $installation->configure(
        $db,
        InstallationStateInterface::MULTISITE_SUBFOLDER,
        $configData
    );
}

echo "Checking WordPress installation in $wpRootDir ...\n";
if ($installation->getState() instanceof Configured) {
    $db->drop()->create();
    echo "Installing WordPress in $wpRootDir ...\n";
    $installation = $installation->install(
        $env['WORDPRESS_URL'],
        $env['WORDPRESS_ADMIN_USER'],
        $env['WORDPRESS_ADMIN_PASSWORD'],
        $env['WORDPRESS_ADMIN_USER'] . '@example.com',
        'TEST'
    );
}

echo "Allowing external connections in $wpRootDir ...\n";
$installation->runWpCliCommand(['config', 'delete', 'WP_HTTP_BLOCK_EXTERNAL']);

foreach (['test_subdir', 'test_subdomain', 'test_empty'] as $dbName) {
    echo "Creating database $dbName ...\n";
    $db->query("CREATE DATABASE IF NOT EXISTS `$dbName`");
}

echo "Checking TwentyTwenty theme in $wpRootDir ...\n";
if (!is_dir($wpRootDir . '/wp-content/themes/twentytwenty')) {
    echo "Installing TwentyTwenty theme in $wpRootDir ...\n";
    $installation->runWpCliCommandOrThrow(['theme', 'install', 'twentytwenty', '--activate']);
}

echo "Checking TwentyTwentyOne theme in $wpRootDir ...\n";
if (!is_dir($wpRootDir . '/wp-content/themes/twentytwentyone')) {
    echo "Installing TwentyTwentyOne theme in $wpRootDir ...\n";
    $installation->runWpCliCommandOrThrow(['theme', 'install', 'twentytwentyone', '--activate']);
}

echo "Installing required test plugins in $wpRootDir ...\n";
$plugins = ['akismet', 'hello-dolly'];
$installation->runWpCliCommandOrThrow(array_merge(['plugin', 'install'], $plugins));
// Depending on the PHP version, install a different version of WooCommerce.
$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
switch ($phpVersion) {
    case '7.1':
        $installation->runWpCliCommandOrThrow(['plugin', 'install', 'woocommerce', '--force', '--version=6.4.1']);
        break;
    case '7.2':
        $installation->runWpCliCommandOrThrow(['plugin', 'install', 'woocommerce', '--force', '--version=7.6.1']);
        break;
    case '7.3':
        $installation->runWpCliCommandOrThrow(['plugin', 'install', 'woocommerce', '--force', '--version=8.1.1']);
        break;
    case '7.4':
        $installation->runWpCliCommandOrThrow(['plugin', 'install', 'woocommerce', '--force']);
        break;
}

echo "Blocking external connections in $wpRootDir ...\n";
$installation->runWpCliCommandOrThrow(['config', 'set', 'WP_HTTP_BLOCK_EXTERNAL', 'true']);

echo "Copying over dummy theme to $wpRootDir ...\n";
if (!is_dir('var/wordpress/wp-content/themes/dummy')
    && !Filesystem::recurseCopy('tests/_data/themes/dummy', 'var/wordpress/wp-content/themes/dummy')) {
    throw new RuntimeException('Could not copy dummy theme.');
}

echo "Copying over mu-plugin-1 to $wpRootDir ...\n";
if (!is_dir($wpRootDir . '/wp-content/plugins/mu-plugin-1')
    && !Filesystem::recurseCopy('tests/_data/plugins/mu-plugin-1', $wpRootDir . '/wp-content/plugins/mu-plugin-1')) {
    throw new RuntimeException('Could not copy mu-plugin-1.');
}

if (!is_file($wpRootDir . '/wp-cli.yml')) {
    $url = $env['WORDPRESS_URL'];
    if (!file_put_contents(
        $wpRootDir . '/wp-cli.yml',
        <<<YAML
url: $url 
YAML
    )) {
        throw new RuntimeException('Could not create wp-cli.yml file.');
    }
}
