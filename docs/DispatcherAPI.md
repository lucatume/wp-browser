An API to dispatch and subscribe to events fired during tests execution.

### Events dispatched by Codeception

You can subscribe to the following events dispatched by Codeception in either the global bootstrap file
(usually `tests/_bootstrap.php`), or in a suite bootstrap file (usually `tests/<suite>/_bootstrap.php`) using the
[Event Dispatcher Bridge extension][1]:

* `Codeception\Events::SUITE_BEFORE`
* `Codeception\Events::SUITE_AFTER`
* `CodeceptionventsEvents::TEST_START`
* `CodeceptionventsEvents::TEST_BEFORE`
* `CodeceptionventsEvents::STEP_BEFORE`
* `CodeceptionventsEvents::STEP_AFTER`
* `CodeceptionventsEvents::TEST_FAIL`
* `CodeceptionventsEvents::TEST_ERROR`
* `CodeceptionventsEvents::TEST_PARSED`
* `CodeceptionventsEvents::TEST_INCOMPLETE`
* `CodeceptionventsEvents::TEST_SKIPPED`
* `CodeceptionventsEvents::TEST_WARNING`
* `CodeceptionventsEvents::TEST_USELESS`
* `CodeceptionventsEvents::TEST_SUCCESS`
* `CodeceptionventsEvents::TEST_AFTER`
* `CodeceptionventsEvents::TEST_END`
* `CodeceptionventsEvents::TEST_FAIL_PRINT`
* `CodeceptionventsEvents::RESULT_PRINT_AFTER`

In the global bootstrap file (usually `tests/_bootstrap.php`), or the suite bootstrap file (usually
`tests/<suite>/_bootstrap.php`), subscribe to the Codeception events by providing a callback function that will accept
different parameters depending on the event being dispatched:

```php
<?php

use Codeception\Events;
use Codeception\Event\SuiteEvent
use Codeception\Event\TestEvent;
use Codeception\Event\StepEvent;
use Codeception\Event\PrintResultEvent;
use lucatume\WPBrowser\Events\Dispatcher;

Dispatcher::addListener(Events::SUITE_BEFORE, function (SuiteEvent $suiteEvent) {
    codecept_debug('Running on SUITE BEFORE');
});

Dispatcher::addListener(Events::SUITE_AFTER, function (SuiteEvent $suiteEvent) {
    codecept_debug('Running on SUITE AFTER');
});

Dispatcher::addListener(Events::TEST_START, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST START');
});

Dispatcher::addListener(Events::TEST_BEFORE, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST BEFORE');
});

Dispatcher::addListener(Events::STEP_BEFORE, function (StepEvent $stepEvent) {
    codecept_debug('Running on STEP BEFORE');
});

Dispatcher::addListener(Events::STEP_AFTER, function (StepEvent $stepEvent) {
    codecept_debug('Running on STEP AFTER');
});

Dispatcher::addListener(Events::TEST_FAIL, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST FAIL');
});

Dispatcher::addListener(Events::TEST_ERROR, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST ERROR');
});

Dispatcher::addListener(Events::TEST_PARSED, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST PARSED');
});

Dispatcher::addListener(Events::TEST_INCOMPLETE, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST INCOMPLETE');
});

Dispatcher::addListener(Events::TEST_SKIPPED, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST SKIPPED');
});

Dispatcher::addListener(Events::TEST_WARNING, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST WARNING');
});

Dispatcher::addListener(Events::TEST_USELESS, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST USELESS');
});

Dispatcher::addListener(Events::TEST_SUCCESS, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST SUCCESS');
});

Dispatcher::addListener(Events::TEST_AFTER, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST AFTER');
});

Dispatcher::addListener(Events::TEST_END, function (TestEvent $testEvent) {
    codecept_debug('Running on TEST END');
});

Dispatcher::addListener(Events::TEST_FAIL_PRINT, function (PrintResultEvent $printResultEvent) {
    codecept_debug('Running on TEST FAIL PRINT');
});

Dispatcher::addListener(Events::RESULT_PRINT_AFTER, function (PrintResultEvent $printResultEvent) {
    codecept_debug('Running on RESULT PRINT AFTER');
});
```

### Events dispatched by wp-browser

The project dispatches its own events, allowing you to subscribe to them to control the test state and execution.  
To subscribe to the events dispatched by wp-browser, you **do not need** to use the
[Event Dispatcher Bridge extension][1].

#### Events dispatched by the WPLoader module

The [`WPLoader` module][2] will dispatch events during its initialization.  

##### EVENT_BEFORE_LOADONLY

This event fires before WordPress is loaded by the `WPLoader` module when `loadOnly` is set to `true`.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

WordPress is not loaded yet, so you cannot use functions or classes defined by WordPress, themes or plugins here.  
You can interact with the WordPress installation and use the `lucatume\WPBrowser\WordPress\PreloadFilters` class to
hook on actions and filters that will be fired by WordPress once loaded.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPLoader::EVENT_BEFORE_LOADONLY, function (Event $event) {
    /** @var WPLoader $wpLoader */
    $wpLoader = $event->getOrigin();
    
    codecept_debug('Running on EVENT_BEFORE_LOADONLY');
   
    // Interact with the WordPress installation, its filesystem and database.
    $installation = $wpLoader->getInstallation();
    $pluginsDir = $installation->getPluginsDir();
    $db = $installation->getDb();
    $db->import(codecept_data_dir('some-dump.sql'));
    
    // Use the PreloadFilters class to hook on WordPress actions and filters.
    PreloadFilters::addFilter('init', fn() => update_option('some_option', 'some_value'));
    PreloadFilters::addFilter('pre_option_some_option', fn() => 'some_value');
});
```

##### EVENT_AFTER_LOADONLY

This event fires after WordPress is loaded by the `WPLoader` module when `loadOnly` is set to `true`.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

At this point, WordPress has been loaded.
You can interact with the WordPress installation using functions and classes defined by WordPress, themes or plugins.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPLoader::EVENT_AFTER_LOADONLY, function (Event $event) {
    /** @var WPLoader $wpLoader */
    $wpLoader = $event->getOrigin();
    
    codecept_debug('Running on EVENT_AFTER_LOADONLY');
           
    // Interact with the WordPress installation, its filesystem and database.    
    $installation = $wpLoader->getInstallation();
    $pluginsDir = $installation->getPluginsDir();
    $db = $installation->getDb();
    $db->import(codecept_data_dir('some-dump.sql'));
    
    // Use WordPress functions and classes.
    update_option('some_option', 'some_value');
});
```

##### EVENT_BEFORE_INSTALL

This event fires before WordPress is installed by the `WPLoader` module when `loadOnly` is set to `false`.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

WordPress is not loaded yet, so you cannot use functions or classes defined by WordPress, themes or plugins here.  
You can interact with the WordPress installation and use the `lucatume\WPBrowser\WordPress\PreloadFilters` class to
hook on actions and filters that will be fired by WordPress once loaded.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPLoader::EVENT_BEFORE_INSTALL, function (Event $event) {
    /** @var WPLoader $wpLoader */
    $wpLoader = $event->getOrigin();
    
    codecept_debug('Running on EVENT_BEFORE_INSTALL');
    
    // Interact with the WordPress installation, its filesystem and database.
    $installation = $wpLoader->getInstallation();
    $pluginsDir = $installation->getPluginsDir();
    $db = $installation->getDb();
    $db->import(codecept_data_dir('some-dump.sql'));
    
    // Use the PreloadFilters class to hook on WordPress actions and filters.
    PreloadFilters::addFilter('init', fn() => update_option('some_option', 'some_value'));
    PreloadFilters::addFilter('pre_option_some_option', fn() => 'some_value');
});
```

##### EVENT_AFTER_INSTALL

This event fires after WordPress is installed by the `WPLoader` module when `loadOnly` is set to `false`.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

At this point, WordPress has been installed and loaded.
You can interact with the WordPress installation using functions and classes defined by WordPress, themes or plugins.
This event fires before dump files specified in the `dump` configuration parameter of the `WPLoader` module are
imported.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function (Event $event) {
    /** @var WPLoader $wpLoader */
    $wpLoader = $event->getOrigin();
    
    codecept_debug('Running on EVENT_AFTER_INSTALL');
           
    // Interact with the WordPress installation, its filesystem and database.    
    $installation = $wpLoader->getInstallation();
    $pluginsDir = $installation->getPluginsDir();
    $db = $installation->getDb();
    $db->import(codecept_data_dir('some-dump.sql'));
    
    // Use WordPress functions and classes.    
    update_option('some_option', 'some_value');
});
```

##### EVENT_AFTER_INSTALL

This event fires after WordPress is installed by the `WPLoader` module when `loadOnly` is set to `false`. 

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

At this point, WordPress has been installed and loaded.
You can interact with the WordPress installation using functions and classes defined by WordPress, themes or plugins.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPLoader::EVENT_AFTER_INSTALL, function (Event $event) {
    /** @var WPLoader $wpLoader */
    $wpLoader = $event->getOrigin();
    
    codecept_debug('Running on EVENT_AFTER_INSTALL');
           
    // Interact with the WordPress installation, its filesystem and database.    
    $installation = $wpLoader->getInstallation();
    $pluginsDir = $installation->getPluginsDir();
    $db = $installation->getDb();
    $db->import(codecept_data_dir('some-dump.sql'));
    
    // Use WordPress functions and classes.
    update_option('some_option', 'some_value');
});
```

##### EVENT_AFTER_BOOTSTRAP

This event fires after the `WPLoader` module has finished bootstrapping the WordPress installation and dump files
specified in the `dump` configuration parameter of the `WPLoader` module are imported.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

You can interact with the WordPress installation using functions and classes defined by WordPress, themes or plugins.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPLoader::EVENT_AFTER_BOOTSTRAP, function (Event $event) {
    /** @var WPLoader $wpLoader */
    $wpLoader = $event->getOrigin();
    
    codecept_debug('Running on EVENT_AFTER_BOOTSTRAP');
    
    // Interact with the WordPress installation, its filesystem and database.    
    $installation = $wpLoader->getInstallation();
    $pluginsDir = $installation->getPluginsDir();
    $db = $installation->getDb();
    $db->import(codecept_data_dir('some-dump.sql'));
    
    // Use WordPress functions and classes.
    update_option('some_option', 'some_value');
});
```

#### Events dispatched by the WPDb module

##### EVENT_BEFORE_SUITE

This event fires after the `WPDb` module has run its `_beforeSuite` method.  

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

At this point the module has connected to the database, cleaned up and populated the database with the dump files.

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPDb;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPDb::EVENT_BEFORE_SUITE, function (Event $event) {
    /** @var WPDb $wpDb */
    $wpDb = $event->getOrigin();    
    
    codecept_debug('Running on EVENT_BEFORE_SUIT
    E');
});
```     

##### EVENT_BEFORE_INITIALIZE

This event fires before the `WPDb` module initializes.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPDb;
use lucatume\WPBrowser\Events\Event;

Dispatcher::addListener(WPDb::EVENT_BEFORE_INITIALIZE, function (Event $event) {
    /** @var WPDb $wpDb */
    $wpDb = $event->getOrigin();    
    
    codecept_debug('Running on EVENT_BEFORE_INITIALIZE');
});
``` 

##### EVENT_AFTER_INITIALIZE

This event fires after the `WPDb` module has initialized.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;  
use lucatume\WPBrowser\Module\WPDb;  
use lucatume\WPBrowser\Events\Event;  

Dispatcher::addListener(WPDb::EVENT_AFTER_INITIALIZE, function (Event $event) {
    /** @var WPDb $wpDb */
    $wpDb = $event->getOrigin();    
    
    codecept_debug('Running on EVENT_AFTER_INITIALIZE');
});
```

##### EVENT_AFTER_DB_PREPARE

This event fires after the `WPDb` module has prepared the database setting up some default values for
quality-of-life improvements.

**Due to order-of-operations, you can hook on this event only in the global bootstrap file (usually `tests/_bootstrap.php`).**

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;  
use lucatume\WPBrowser\Module\WPDb;  
use lucatume\WPBrowser\Events\Event;  

Dispatcher::addListener(WPDb::EVENT_AFTER_DB_PREPARE, function (Event $event) {
    /** @var WPDb $wpDb */
    $wpDb = $event->getOrigin();    
    
    codecept_debug('Running on EVENT_AFTER_DB_PREPARE');
});
```     

### Dispatching custom events

You can use the `Dispatcher::dispatch` method to dispatch and subscribe to custom events:

```php
<?php

use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Events\Event;  

Dispatcher::dispatch('my-event', 'my-origin', ['foo' => 'bar']);

// Some other code...
Dispatcher::addListener('my-event', function (Event $event) {
    $origin = $event->getOrigin();
    $foo = $event->get('foo');
    codecept_debug('Running on my custom event');
});
```

[1]: extensions/EventDispatcherBridge.md

[2]: modules/WPLoader.md

[3]: modules/WPDb.md

