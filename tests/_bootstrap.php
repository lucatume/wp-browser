<?php
// This is global bootstrap for autoloading.
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Util\Autoload;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Utils\Db;
use lucatume\WPBrowser\Utils\Env;

function createTestDatabasesIfNotExist(): void
{
    $env = Env::envFile('tests/.env');
    $host = $env['WORDPRESS_DB_HOST'];
    $user = $env['WORDPRESS_DB_USER'];
    $pass = $env['WORDPRESS_DB_PASSWORD'];
    try {
        $db = Db::db('mysql:host=' . $host, $user, $pass);
        $db('CREATE DATABASE IF NOT EXISTS ' . $env['WORDPRESS_SUBDIR_DB_NAME']);
        $db('CREATE DATABASE IF NOT EXISTS ' . $env['WORDPRESS_SUBDOMAIN_DB_NAME']);
        $db('CREATE DATABASE IF NOT EXISTS ' . $env['WORDPRESS_EMPTY_DB_NAME']);
    } catch (Exception $e) {
        codecept_debug('Could not connect to the database: ' . $e->getMessage());
    }
}

createTestDatabasesIfNotExist();

// Make sure traits can be autoloaded from tests/_support/Traits
Autoload::addNamespace('\lucatume\WPBrowser\Tests\Traits', codecept_root_dir('tests/_support/Traits'));

// If the `uopz` extension is installed, then ensure `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}

// This is here to test the EventDispatcherBridge extension.
Dispatcher::addListener(Events::MODULE_INIT, function (SuiteEvent $suiteEvent) {
    $suiteName = $suiteEvent->getSuite()?->getName();
    codecept_debug('Suite name: ' . $suiteName);
});
