## Isolation Support Extension

This extension provides support for the PHPUnit annotations `@runInSeparateProcess` and `@runTestsInSeparateProcesses`,
and the PHPUnit attributes (PHPUnit 10+) `#[RunInSeparateProcess]` and `#[RunTestsInSeparateProcesses]`.  
You can read more about these annotations and attributes in the [PHPUnit documentation about test isolation][1].

Codeception does not natively support these annotations and attributes, this extension provides support for them.

### Configuration

You can enable the extension in the Codeception configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\IsolationSupport"
```

In your tests, you can use the annotations or attributes as you would in a PHPUnit test:

```php
<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class IsolationExampleTest extends WPTestCase {
    /**
     * @runInSeparateProcess
     */
    public function test_in_admin_context() {
        define('WP_ADMIN', true);
        
        $this->assertTrue(is_admin());
    }
 
    #[RunTestsInSeparateProcesses]
    public function test_in_admin_context_with_attribute() {
        define('WP_ADMIN', true);
        
        $this->assertTrue(is_admin());
    }
    
    public function test_constant_is_not_set() {
        $this->assertFalse(defined('WP_ADMIN'));
    }
}

#[RunTestsInSeparateProcesses]
class RunAllTestsInSeparateProcesses extends WPTestCase {
    public function test_one() {
        definen('TEST_CONST', 'one');
        
        $this->assertEquals('one', TEST_CONST);
    }
    
    public function test_two() {
        definen('TEST_CONST', 'two');
        
        $this->assertEquals('two', TEST_CONST);
    }
}
```

> Previous versions of the test isolation support **required** the `@backupGlobals disabled` annotation to be used when
> running tests in isolation. This is no longer required.

Isolation support is based around monkey-patching the file at runtime. Look into the [`monkey:cache:clear`][2]
and [`monkey:cache:path`][3] commands to manage the monkey-patching cache.


[1]: https://docs.phpunit.de/en/10.5/attributes.html#test-isolation
[2]: commands.md#monkeycacheclear
[3]:commands.md#monkeycachepath
