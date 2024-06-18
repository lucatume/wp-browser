This extension connects the event dispatcher system provided by Codeception, and [normally available only through the
use of custom extensions][1], to make it available through calls to the `lucatume\WPBrowser\Events\Dispatcher` class 
API.

If not using this extension, then the only way to subscribe to events dispatched by Codeception is to use [custom
extensions][1].

### Configuration

The extension does not require configuration, it just needs to be enabled in the Codeception configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\EventDispatcherBridge"
```

### Usage

The extension will automatically hook into the event dispatcher system provided by Codeception, [normally available only
through the use of custom extensions][1], and inject user-defined event listeners in it.

Once the extension is enabled, you can use the `lucatume\WPBrowser\Events\Dispatcher` class to subscribe to Codeception 
events.  
This is typically be done in either the global bootstrap file, or in a suite bootstrap file.  

You can subscribe to the following events dispatched by Codeception in either the global bootstrap file 
(usually `tests/_bootstrap.php`), or in a suite bootstrap file (usually `tests/<suite>/_bootstrap.php`):

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

Due to order-of-operations, the earliest Codeception dispatched Event you can subscribe to is the `SUITE_BEFORE` one.  
To subscribe to the following earlier events, you must implement an extension following the [custom extension][1]
approach:

* `Codeception\Events::MODULE_INIT`
* `Codeception\Events::SUITE_INIT`

The [Dispatcher API][2] documentation provides more details about the events dispatched by Codeception and wp-browser
and examples on how to subscribe to them.

### Usage Examples

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

[Read more about the Dispatcher API here][2].

[1]: https://codeception.com/docs/Customization#Extension
[2]: ../DispatcherAPI.md
