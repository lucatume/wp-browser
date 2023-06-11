<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Event\TestEvent;
use Codeception\Events;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Installation;

Dispatcher::addListener(Events::TEST_AFTER, static function (TestEvent $event) {
    $isCI = getenv('CI') === 'true';
    if (!($isCI || $event->getTest()->getResultAggregator()->wasSuccessful())) {
        return;
    }

    foreach (Installation::getScaffoldedInstallations() as $dir) {
        codecept_debug("Removing Installation at $dir after successful test ...");
        FS::rrmdir($dir);
        Installation::forgetScaffoldedInstallation($dir);
    }
});

require_once dirname(__DIR__, 2) . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php';
