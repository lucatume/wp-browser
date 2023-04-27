<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Event\TestEvent;
use Codeception\Events;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\Utils\Filesystem as FS;

include_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

Dispatcher::addListener(Events::TEST_AFTER, static function (TestEvent $event) {
    if (!$event->getTest()->getResultAggregator()->wasSuccessful()) {
        return;
    }
    foreach (Installation::getScaffoldedInstallations() as $dir) {
        codecept_debug("Removing Installation at $dir after successful test ...");
        FS::rrmdir($dir);
    }
});
