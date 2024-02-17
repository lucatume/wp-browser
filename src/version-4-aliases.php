<?php

namespace lucatume\WPBrowser;

use lucatume\WPBrowser\Command\GenerateWPAjax;
use lucatume\WPBrowser\Command\GenerateWPCanonical;
use lucatume\WPBrowser\Command\GenerateWPRestApi;
use lucatume\WPBrowser\Command\GenerateWPRestController;
use lucatume\WPBrowser\Command\GenerateWPRestPostTypeController;
use lucatume\WPBrowser\Command\GenerateWPUnit;
use lucatume\WPBrowser\Command\GenerateWPXMLRPC;
use lucatume\WPBrowser\Extension\EventDispatcherBridge;
use lucatume\WPBrowser\Extension\IsolationSupport;
use lucatume\WPBrowser\Module\WPBrowser;
use lucatume\WPBrowser\Module\WPBrowserMethods;
use lucatume\WPBrowser\Module\WPCLI;
use lucatume\WPBrowser\Module\WPDb;
use lucatume\WPBrowser\Module\WPFilesystem;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Module\WPQueries;
use lucatume\WPBrowser\Module\WPWebDriver;
use lucatume\WPBrowser\Template\Wpbrowser as WpbrowserInitTemplate;
use lucatume\WPBrowser\TestCase\WPAjaxTestCase;
use lucatume\WPBrowser\TestCase\WPCanonicalTestCase;
use lucatume\WPBrowser\TestCase\WPRestApiTestCase;
use lucatume\WPBrowser\TestCase\WPRestControllerTestCase;
use lucatume\WPBrowser\TestCase\WPRestPostTypeControllerTestCase;
use lucatume\WPBrowser\TestCase\WPTestCase;
use lucatume\WPBrowser\TestCase\WPXMLRPCTestCase;

use function class_alias;

/**
 * Defines a set of class aliases to allow referencing the framework classes with their previous versions' names.
 * Calls to `class_alias` will immediately autoload the class in a eager fashion.
 * Using an autoloader will load them lazily.
 */
$deprecatedAutoloader = static function (string $class) use (&$deprecatedAutoloader): void {
    $deprecated = [
        'Codeception\\Module\\WPBrowser' => WPBrowser::class,
        'Codeception\\Module\\WPBrowserMethods' => WPBrowserMethods::class,
        'Codeception\\Module\\WPCLI' => WPCLI::class,
        'Codeception\\Module\\WPDb' => WPDb::class,
        'Codeception\\Module\\WPFilesystem' => WPFilesystem::class,
        'Codeception\\Module\\WPLoader' => WPLoader::class,
        'Codeception\\Module\\WPQueries' => WPQueries::class,
        'Codeception\\Module\\WPWebDriver' => WPWebDriver::class,
        'Codeception\\Command\\GenerateWPUnit' => GenerateWPUnit::class,
        'Codeception\\Command\\GenerateWPRestApi' => GenerateWPRestApi::class,
        'Codeception\\Command\\GenerateWPRestController' => GenerateWPRestController::class,
        'Codeception\\Command\\GenerateWPRestPostTypeController' => GenerateWPRestPostTypeController::class,
        'Codeception\\Command\\GenerateWPAjax' => GenerateWPAjax::class,
        'Codeception\\Command\\GenerateWPCanonical' => GenerateWPCanonical::class,
        'Codeception\\Command\\GenerateWPXMLRPC' => GenerateWPXMLRPC::class,
        'Codeception\\Template\\Wpbrowser' => WpbrowserInitTemplate::class,
        'Codeception\\TestCase\\WPTestCase' => WPTestCase::class,
        'Codeception\\TestCase\\WPAjaxTestCase' => WPAjaxTestCase::class,
        'Codeception\\TestCase\\WPCanonicalTestCase' => WPCanonicalTestCase::class,
        'Codeception\\TestCase\\WPRestApiTestCase' => WPRestApiTestCase::class,
        'Codeception\\TestCase\\WPRestControllerTestCase' => WPRestControllerTestCase::class,
        'Codeception\\TestCase\\WPRestPostTypeControllerTestCase' => WPRestPostTypeControllerTestCase::class,
        'Codeception\\TestCase\\WPXMLRPCTestCase' => WPXMLRPCTestCase::class,
        'tad\\WPBrowser\\Extension\\Events' => EventDispatcherBridge::class,
        'Codeception\\Extension\\IsolationSupport' => IsolationSupport::class,
        'tad\\WPBrowser\\Module\\WPLoader\\FactoryStore' => WPLoader\FactoryStore::class,
         /* WordPress PHPUnit compatibility layer will require these classes removed in PHPUnit 10 */
        'PHPUnit\\Framework\\Error\\Deprecated' => RemovedInPHPUnitVersion10::class,
        'PHPUnit\\Framework\\Error\\Notice' => RemovedInPHPUnitVersion10::class,
        'PHPUnit\\Framework\\Error\\Warning' => RemovedInPHPUnitVersion10::class,
        'PHPUnit\\Framework\\Warning' => RemovedInPHPUnitVersion10::class,
        'PHPUnit\\Framework\\TestListener' => RemovedInPHPUnitVersion10::class
    ];
    $countDeprecated = count($deprecated);
    static $hits = 0;

    if (isset($deprecated[$class])) {
        class_alias($deprecated[$class], $class);
        $hits++;
    }

    if ($hits === $countDeprecated) {
        // Job done, do not keep loading.
        spl_autoload_unregister($deprecatedAutoloader);
    }
};

spl_autoload_register($deprecatedAutoloader);
