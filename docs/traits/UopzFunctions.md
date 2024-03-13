This trait provides a set of methods to manipulate functions, methods and class attributes using the [uopz][1] PHP
extension.

!!! warning

    This test trait requires the `uopz` PHP extension.  

    See the [Installing the extension locally](#installing-the-extension-locally) section of this page for more information about how to do that.  
    If you need to install the extension in a CI environment, see the [Installing the extension in CI](#installing-the-extension-in-ci) section of this page.

    If the `uopz` extension is not installed, test methods using methods from the `UopzFunctions` trait will be marked as skipped.

### Why require an extension?

Why use a PHP extension instead of a user-land solution, i.e. a PHP library that does not require installing an
extension?

I've written such a solution myself, [function-mocker][3], but have grown frustrated with its limitations, and the
limitation of other similar solutions.

All user-land, monkey-patching, pure PHP solutions rely on [stream-wrapping][4].  
This is a very powerful feature that this project uses for some of its functionality, but it has a drawbacks when used
extensively for monkey-patching functions and methods:

* the files patch must be included **after** the library loaded
* the files have to patched, or patched and cached, on each run
* there are some random and difficult to track issues introduced by how function and method patching works; e.g.
  functions manipulating values by reference will not work as expected
* some constants like `__METHOD__` and `__FUNCTION__` will not work as expected in the patched files
* monkey-patching code will be "inserted" in the function stack, lengthening the stack trace and making it very
  difficult to debug
* all this processing together with [XDebug][9] spells doom for the performance of the test suite

The `uopz` extension is a **solid and fast** solution that has been created and maintained by people that know PHP
internals and the PHP language very well that has **none of the drawbacks** of the above-mentioned solutions.

It is just a better tool for the job.

### Installing the extension locally

=== "Windows"

    * Locate your `php.ini` file:
        ```powershell
        php --ini
        ```
    * Download the latest `DLL` stable version of the extension from the [releases page][2]. You'll likely need the `NTS x64` version.
    * Unzip the file and copy the `php_uopz.dll` file to the `ext` folder of your PHP installation. If your `php.ini` file is located at `C:\tools\php81\php.ini`, the extensions directory will be located at `C:\tools\php81\ext`.
    * Edit your `php.ini` file and add the following line to enable and configure the extension:
        ```ini
        extension=uopz
        uopz.exit=1
        ```
    * Make sure the extension is correctly installed by running `php -m` and making sure the `uopz` extension appears in the list of extensions.
    
    You can find more information about installing PHP extensions on Windows in the [PHP manual][6] and in [the `uopz` extension install guide][7].

=== "Linux"

    * Use the `pecl` command to install the extension:
        ```bash
        pecl install uopz
        ```
    * Configure the extension to ensure it will allow `exit` and `die` calls to terminate the script execution.  
      Add the following line to either the main PHP configuration file (`php.ini`), or a dedicated configuration file:
        ```ini
        uopz.exit=1
        ```
    * Make sure the extension is correctly installed by running `php -m` and making sure the `uopz` extension appears in the list of extensions.

    Alternatively, you can build the extension from source as detailed in [the `uopz` extension install guide][7].

=== "MacOS"

    * Use the `pecl` command to install the extension:
        ```bash
        pecl install uopz
        ```
    * Configure the extension to ensure it will allow `exit` and `die` calls to terminate the script execution.  
      Add the following line to either the main PHP configuration file (`php.ini`), or a dedicated configuration file:
        ```ini
        uopz.exit=1
        ```
    * Make sure the extension is correctly installed by running `php -m` and making sure the `uopz` extension appears in the list of extensions.

    Alternatively, you can build the extension from source as detailed in [the `uopz` extension install guide][7].

### Installing the extension in CI

Depending on your Continuous Integration (CI) solution of choice, the configuration required to install and set up
the `uopz` extensions will be different.

As an example, here is how you can set up the `uopz` extension in a GitHub Actions job:

```yaml
- name: Setup PHP 8.1 with uopz
uses: shivammathur/setup-php@v2
with:
  php-version: 8.1
  extensions: uopz
  ini-values: uopz.exit=1
```

[This project uses the very same setup][8].

Most CI systems are based on Linux OSes: if you're not using GitHub Actions, you can reference to the
Linux [local installation instructions](#installing-the-extension-locally) to set up and install the extension for your
CI solution of choice.

### Usage

Include the `UopzFunctions` trait in your test class and use the methods provided by the trait to manipulate functions,
methods and class attributes.

```php
<?php

use lucatume\WPBrowser\WPTestCase;
use lucatume\WPBrowser\Traits\UopzFunctions;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_mock_wp_create_nonce()
    {
        $this->setFunctionReturn('wp_create_nonce', 'super-secret-nonce');

        $this->assertEquals('super-secret-nonce', wp_create_nonce('some-action'));
    }
}
```

The trait will take care of cleaning up all the modifications made to the functions, methods and class attributes after
each test.

You can use the `UopzFunctions` trait in test cases extending the `PHUnit\Framework\TestCase` class as well:

```php
<?php

use PHPUnit\Framework\TestCase;
use lucatume\WPBrowser\Traits\UopzFunctions;

class MyTest extends TestCase
{
    use UopzFunctions;

    public function test_can_mock_my_function()
    {
        $this->setFunctionReturn('someFunction', 'mocked-value');

        $this->assertEquals('mocked-value', someFunction());
    }
}
```

### Methods

The `UopzFunctions` trait provides the following methods:

#### setFunctionReturn

`setFunctionReturn(string $function, mixed $value, bool $execute = false): void`

Set the return value for the function `$function` to `$value`.

If `$value` is a closure and `$execute` is `true`, then the return value will be the return value of the closure.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_function_return()
    {
        $this->setFunctionReturn('wp_generate_nonce', 'super-secret-nonce');

        $this->assertEquals('super-secret-nonce', wp_create_nonce('some-action'));
    }
}
```

If `$value` is a closure, the original function can be called within the closure to relay the original return value:

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_function_return_with_closure()
    {
        $this->setFunctionReturn(
            'wp_generate_nonce',
            fn(string $action) => $action === 'test' ? 'test-nonce' : wp_create_nonce($action),
            true
        );

        $this->assertEquals('test-nonce', wp_create_nonce('test'));
        $this->assertNotEquals('test-nonce', wp_create_nonce('some-other-action'));
    }
}
```

#### unsetFunctionReturn

`unsetFunctionReturn(string $function): void`

Unset the return value for the function `$function` previously set with [`setFunctionReturn`](#setfunctionreturn).

You do not need to unset the return value for a function that was set with [`setFunctionReturn`](#setfunctionreturn)
using `unsetFunctionReturn` explicitly: the trait will take care of cleaning up all the modifications made to the
functions, methods and class attributes after each test.

#### setMethodReturn

`setMethodReturn(string $class, string $method, mixed $value, bool $execute = false): void`

Sets the return value for the static or instance method `$method` of the class `$class` to `$value`.

If `$value` is a closure and `$execute` is `true`, then the return value will be the return value of the closure.

Magic methods like `__construct`, `__destruct`, `__call` and so on cannot be mocked using this method.
See the [`setClassMock`](#setclassmock) method for more information about how to mock magic class methods.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SomeLegacyClass {
    public static function staticMethod(){
        return 'some-static-value';
    }

    public function instanceMethod(){
        return 'some-instance-value';
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_method_return()
    {
        $this->setMethodReturn(SomeLegacyClass::class, 'staticMethod', 'STATIC');
        $this->setMethodReturn(SomeLegacyClass::class, 'instanceMethod', 'TEST');

        $legacyClass = new SomeLegacyClass();

        $this->assertEquals('STATIC', SomeLegacyClass::staticMethod());
        $this->assertEquals('TEST', $legacyClass->instanceMethod());
    }
}
```

If `$value` is a closure, the original static or instance method can be called within the closure, with correctly
bound `self` and `$this` context, to relay the original return value:

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SomeLegacyClass {
    public static function raiseStaticFlag(bool $flag = false){
        return $flag ? 'static-flag-raised' : 'static-flag-lowered';
    }

    public function raiseFlag(bool $flag = false){
        return $flag ? 'flag-raised' : 'flag-lowered';
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_method_return_with_closure()
    {
        $this->setMethodReturn(
            SomeLegacyClass::class,
            'raiseStaticFlag',
            fn(bool $flag) => $flag ? 'STATIC' : self::raiseStaticFlag($flag),
            true
        );
        $this->setMethodReturn(
            SomeLegacyClass::class,
            'raiseFlag',
            fn(bool $flag) => $flag ? 'TEST' : $this->raiseFlag($flag),
            true
        );

        $legacyClass = new SomeLegacyClass();

        $this->assertEquals('STATIC', SomeLegacyClass::raiseStaticFlag(true));
        $this->assertEquals('static-flag-lowered', SomeLegacyClass::raiseStaticFlag(false));
        $this->assertEquals('TEST', $legacyClass->raiseFlag(true));
        $this->assertEquals('flag-lowered', $legacyClass->raiseFlag(false));
    }
}
```

#### unsetmethodreturn

`unsetmethodreturn(string $class, string $method): void`

Unset the return value for the static or instance method `$method` of the class `$class` previously set
with [`setMethodReturn`](#setmethodreturn).

You do not need to unset the return value for a method that was set with [`setMethodReturn`](#setmethodreturn)
using `unsetMethodReturn` explicitly: the trait will take care of cleaning up all the modifications made to the
functions, methods and class attributes after each test.

#### setFunctionHook

`setFunctionHook(string $function, Closure $hook): void`

Execute `$hook` when entering the function `$function`.

Hooks can be set on both internal and user-defined functions.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_hook()
    {
        $log = [];
        $this->setFunctionHook(
            'header', 
            function($header, bool $replace = true, int $response_code = 0) use (&$log): void {
                $log[] = $header;
            }
        );

        header('X-Plugin-Version: 1.0.0');
        header('X-Plugin-REST-Enabled: 1');
        header('X-Plugin-GraphQL-Enabled: 0');

        $this->assertEquals([
            [
                'X-Plugin-Version' => '1.0.0',
                'X-Plugin-REST-Enabled' => '1',
                'X-Plugin-GraphQL-Enabled' => '0'
        ], $log);
    }
}
```

#### unsetFunctionHook

`unsetFunctionHook(string $function): void`

Unset the hook for the function `$function` previously set with [`setFunctionHook`](#setfunctionhook).

You do not need to unset the hook for a function that was set with [`setFunctionHook`](#setfunctionhook)
using `unsetFunctionHook` explicitly: the trait will take care of cleaning up all the modifications made to the
functions, methods and class attributes after each test.

#### setMethodHook

`setMethodHook(string $class, string $method, Closure $hook): void`

Execute `$hook` when entering the static or instance method `$method` of the class `$class`.

The keywords `self` and `$this` will be correctly bound to the class and the class instance respectively.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class LegacyApiController {
    private static array $connections = [];
    private ?array $cachedItems = null;

    public static function connect(): self {
        $connected = new self;
        self::$connections[] = $connected;
        return $connected;
    }

    public function getItems(int $count, int $from = 0): array {
        if($this->cachedItems === null){
            $this->cachedItems = wp_remote_get('https://example.com/items');
        }

        return array_slice($this->cachedItems, $from, $count);
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_method_hook()
    {
        $connections = 0;
        $this->setMethodHook(
            LegacyApiController::class, 
            'connect', 
            function() use (&$connections): void {
                $connections  = count(self::$connections) + 1;
            }
        );
        $itemsCacheHits = 0;
        $this->setMethodHook(
            LegacyApiController::class, 
            'getItems', 
            function(int $count, int $from = 0) use (&$itemsCacheHit): bool {
                if($this->cachedItems !== null){
                    $itemsCacheHits++;
                }
            }
        );

        $connectedController1 = LegacyApiController::connect();
        $connectedController2 = LegacyApiController::connect();
        $connectedController1->getItems(10, 0);
        $connectedController1->getItems(10, 10);
        $connectedController2->getItems(10, 0);
        $connectedController2->getItems(10, 10);

        $this->assertEquals(2, $connections);
        $this->assertEquals(4, $itemsCacheHits);
    }
}
```

#### unsetMethodHook

`unsetMethodHook(string $class, string $method): void`

Unset the hook for the static or instance method `$method` of the class `$class` previously set
with [`setMethodHook`](#setmethodhook).

You do not need to unset the hook for a method that was set with [`seMethodHook`](#setmethodhook)
using `unsetClassMethodHook` explicitly: the trait will take care of cleaning up all the modifications made to the
functions, methods and class attributes after each test.

#### setConstant

`setConstant(string $constant, mixed $value): void`

Set the constant `$constant` to the value `$value`.

If the constant is not already defined, it will be defined and set to the value `$value`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_constant()
    {
        $this->setconstant('WP_ADMIN', true);
        $this->setconstant('TEST_CONST', 23);

        $this->assertTrue(wp_is_admin());
        $this->assertEquals(23, TEST_CONST);
    }
}
```

#### unsetConstant

`unsetConstant(string $constant): void`

Unset an existing constant or restores the original value of the constant if set with [`setConstant`](#setconstant).

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_unset_constant()
    {
        // The test is starting in Admin context.
        $this->assertTrue(is_admin());

        $this->unsetConstant('WP_ADMIN');

        $this->assertFalse(is_admin());
    }
}
```

You do not need to undefine a constant defined with [`setConstant`](#setconstant) using `unsetConstant` explicitly: the
trait will take care of cleaning up all the modifications made to the functions, methods and class attributes after each
test.

#### setClassConstant

`setClassConstant(string $class, string $constant, mixed $value): void`

Set the constant `$constant` of the class `$class` to the value `$value`.

If the class constant is not already defined, it will be defined and set to the value `$value`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyPlugin {
    const VERSION = '89.0.0';
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_class_constant()
    {
        $this->setClassConstant(MyPlugin::class, 'VERSION', '23.89.0');
        $this->setClassConstant(MyPlugin::class, 'NOT_EXISTING', 'TEST');

        $this->assertEquals('23.89.0', MyPlugin::VERSION);
        $this->assertEquals('TEST', MyPlugin::NOT_EXISTING);
    }
}
```

#### unsetClassConstant

`unsetClassConstant(string $class, string $constant): void`

Restore the constant `$constant` of the class `$class` to its original value or removes it if it was not defined.

You do not need to undefine a constant defined with [`setClassConstant`](#setclassconstant)
using `undefineClassConstant` explicitly: the trait will take care of cleaning up all the modifications made to the
functions, methods and class attributes after each test.

#### setClassMock

`setClassMock(string $class, string|object $mock): void`

Use `$mock` instead of `$class` when creating new instances of the class `$class`.

This method allows you to override magic methods as well as you would do with a normal class extension.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MockPaymentApi extends PaymentApi {
    public static function version($name, $arguments){
        return '23.89.0';
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_class_mock()
    {
        $this->setClassMock(PaymentApi::class, MockPaymentApi::class);

        $paymentApi = new PaymentApi();
        $this->assertInstanceOf(MyPluginMock::class, $paymentApi);
        $this->assertSame('23.89.0', $paymentApi::version());
    }
}
```

If you set the `$mock` to an object, then the same mock object will be used for all the new instances of the
class `$class`:

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MockPaymentApi extends PaymentApi {
    public function getIds($name, $arguments){
        return [1, 23, 89];
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_class_mock()
    {
        $mockPaymentApi = new MockPaymentApi();

        $this->setClassMock(PaymentApi::class, $mockPaymentApi);

        $api1 = new PaymentApi();
        $this->assertSame($mockPaymentApi, $api1);
        $this->assertSame([1, 23, 89], $api1->getIds());
        $api2 = new PaymentApi();
        $this->assertSame($mockPaymentApi, $api2);
        $this->assertSame([1, 23, 89], $api2->getIds());
    }
}
```

The `$mock` class, or instance, is **not** required to be a subclass of the class `$class` by the trait; although it
might be required from the code you're testing by means of type hinting.

If the class or method you would like to set a mock for is `final`, then you can combine this method with
the [`unsetClassFinalAttribute`](#unsetclassfinalattribute)
and [`unsetMethodFinalAttribute`](#unsetmethodfinalattribute) methods to avoid the final attribute being set on the
class:

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

final class LegacyPaymentApi {
    public function getIds(){
        // ... fetch ids from a real external API ...
    }
}

class LegacyCacheController {
    protected final function get(string $key){
        // ... fetch data from a real cache ...
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_class_mock()
    {
        $this->unsetClassFinalAttribute(LegacyPaymentApi::class);
        $mockPaymentApi = new class extends LegacyPaymentApi {
            public function getIds(){
                return [1, 23, 89];
            }
        };
        $this->setClassMock(LegacyPaymentApi::class, $mockPaymentApi);
        $this->unsetMethodFinalAttribute(LegacyCacheController::class, 'get');
        $mockCacheController = new class extends LegacyCacheController {
            public function get(string $key){
                return 'some-value';
            }
        };

        $paymentApi = new LegacyPaymentApi();

        $this->assertSame($mockPaymentApi, $paymentApi);
        $this->assertSame([1, 23, 89], $paymentApi->getIds());

        $cacheController = new LegacyCacheController();

        $this->assertSame($mockCacheController, $cacheController);
        $this->assertSame('some-value', $cacheController->get('some-key'));
    }
}
```

#### unsetClassMock

`unsetClassMock(string $class): void`

Remove the mock for the class `$class` previously set with `setMock`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_unset_class_mock()
    {
        $this->setClassMock(MyPlugin::class, new MyPluginMock());

        $this->assertInstanceOf(MyPluginMock::class, new MyPlugin());

        $this->unsetClassMock(MyPlugin::class);

        $this->assertInstanceOf(MyPlugin::class, new MyPlugin());
    }
}
```

You do not need to unset the mock for a class that was set with [`setClassMock`](#setclassmock) using `unsetClassMock`
explicitly: the trait will take care of cleaning up all the modifications made to the functions, methods and class
attributes after each test.

#### unsetClassFinalAttribute

`unsetClassFinalAttribute(string $class): void`

Remove the `final` attribute from the class `$class`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

final class LegacyPaymentApi {}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_remove_class_final_attribute()
    {
        $post = static::factory()->post->createAndGet();

        $this->unsetClassFinalAttribute(LegacyPaymentApi::class);

        // The class is not final anymore; it can be extended for testing purposes.
        $mockPaymentApi = new class extends LegacyPaymentApi {
            public function getIds(){
                return [1, 23, 89];
            }
        };

        $this->assertSame([1, 23, 89], $mockPaymentApi->getIds());
    }
}
```

#### resetClassFinalAttribute

`resetClassFinalAttribute(string $class): void`

Reset the `final` attribute of the class `$class` previously removed with
the [`unsetClassFinalAttribute`](#unsetclassfinalattribute) method.

You do not need to restore the class final attribute for a class that was set
with [`unsetClassFinalAttribute`](#unsetclassfinalattribute) using `setClassFinalAttribute` explicitly: the trait will
take care of cleaning up all the modifications made to the functions, methods and class attributes after each test.

#### unsetMethodFinalAttribute

`unsetMethodFinalAttribute(string $class, string $method): void`

Remove the `final` attribute from the static or instance method `$method` of the class `$class`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_remove_method_final_attribute()
    {
        $this->unsetMethodFinalAttribute(LegacyAjaxController::class, 'printResponseAndExit');
        
        // Build a class to avoid the `printResponseAndExit` method from exiting.
        $testLegacyAdminController = new class extends LegacyAjaxController {
            public string $response = '';

            public function printResponseAndExit(){
                $this->response = $this->template->render('list', return: true);
                return;
            }
        };

        // Set up things for the test ...

        $testLegacyAjaxController->printResponseAndExit();

        $this->assertEquals('<ul><li>Item One</li><li>Item Two</li></ul>', $testLegacyAjaxController->response);
    }
}
```

#### restoreMethodFinalAttribute

`restoreMethodFinalAttribute(string $class, string $method): void`

Restore the `final` attribute of the method static or instance `$method` of the class `$class` previously removed with
the [`unsetMethodFinalAttribute`](#unsetmethodfinalattribute) method.

You do not need to restore the method final attribute for a method that was set
with [`unsetMethodFinalAttribute`](#unsetmethodfinalattribute) using `restoreMethodFinalAttribute` explicitly: the trait
will take care of cleaning up all the modifications made to the functions, methods and class attributes after each test.

#### addClassMethod

`addClassMethod(string $class, string $method, Closure $closure, bool $static = false): void`

Add a `public` static (`$static = true`) or instance (`$static = false`) method to the class `$class` with the
name `$method` and the code provided by the closure `$closure`.

Differently from the [`setClassMock`](#setclassmock) method, this method will work on **already existing instances** of
the class `$class`, not just new instances.

The closure `$this` will be bound to the class instance.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class LegacySingletonController {
    private static $instance;
    private array $cache = null;
    private int $cacheCount = 0;

    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getItems(int $count, int $from = 0){
        if($this->cache === null){
            $this->cache = wp_remote_get('https://example.com/items');
            $this->cacheCount = count($cache);
        }

        return array_slice($this->cache, $from, $count);
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_add_class_method()
    {
        $controller = LegacySingletonController::getInstance();

        $this->addClassMethod(
            LegacySingletonController::class, 
            'setCache',
            function(array $cache): void {
                $this->cache = $cache;
                $this->cacheCount = count($cache);
            }
        );

        // Set the singletong instance cache for testing purposes.
        $controller->setCache(range(1,100));

        $this->assertEquals([1,2,3], $controller->getItems(3, 0));
    }
```

#### removeClassMethod

`removeClassMethod(string $class, string $method): void`

Remove the static or instance method `$method` added with [`addClassMethod`](#addclassmethod) from the class `$class`.

You do not need to remove a method added with [`addClassMethod`](#addclassmethod),
or [`addClassStaticMethod`](#addclassstaticmethod), using `removeClassMethod` explicitly: the trait will take care of
cleaning up all the modifications made to the functions, methods and class attributes after each test.

#### setObjectProperty

`setObjectProperty(string|object $classOrObject, string $property, mixed $value): void`

If `$classOrInstance` is a string, set the property `$property` of the class `$classOrObject` to the value `$value`.
If `$classOrInstance` is an object, set the property `$property` of the object `$classOrObject` to the value `$value`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class Payment {
    private string $uuid;
    private string $from;
    private string $to;

    public function __construct(string $from, $string $to){
        $this->uuid = UUID::generate();
    }

    public function getHash(): string {
        return wp_hash(serialize([
            'uuid' => $this->uuid
            'from' => $this->from,
            'to' => $this->to
        ]));
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_object_property()
    {
        $payment = new Payment('Bob', 'Alice');

        $this->setObjectProperty($payment, 'uuid', '550e8400-e29b-41d4-a716-446655440000');

        $this->assertEquals(wp_hash(serialize([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'from' => 'Bob',
            'to' => 'Alice'
        ])), $payment->getHash());
    }
}
```

You do not need to reset the property of an object that was set with `setObjectProperty` explicitly: the trait will take
care of cleaning up all the modifications made to the functions, methods and class attributes after each test.

#### getObjectProperty

`getObjectProperty(object $object, string $property): mixed`

Get the value of the static or instance property `$property` of the object `$object`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class LegacyController {
    private Template $template;

    public function __construct(){
        $this->template = new Template();
    }

    // ...
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_get_object_property()
    {
        $controller = new LegacyController();

        $templateEngine $this->getObjectProperty($controller, 'template'));

        // ... do something with the template ...
    }
}
```

#### resetObjectProperty

`resetObjectProperty(string|object $classOrObject, string $property): void`

Reset the property `$property` of the class `$class` or object `$object` to its original value.

You do not need to reset the property of an object that was set with `setObjectProperty` explicitly: the trait will take
care of cleaning up all the modifications made to the functions, methods and class attributes after each test.

#### getMethodStaticVariables

`getMethodStaticVariables(string $class, string $method): array`

Get the value of the static variables of the class `$class` and method `$method`.

The method will work for both static and instance methods of the class `$class`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class RequestLogger {
    public function log(int $code, string $response):void {
        static $requestId;
        
        if($requestId === null){
            $requestId = md5(microtime());
        }

        printf("Request %s: %d %s\n", $requestId, $code, $response);
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_get_class_method_static_variables()
    {
        $requestLogger = new RequestLogger();

        ob_start();
        $requestLogger->log(200, 'OK');
        $requestLogger->log(403, 'Forbidden');
        $requestLogger->log(200, 'OK');
        $buffer = ob_get_clean();

        $requestId = $this->getClassMethodStaticVariables(RequestLogger::class, 'log')['requestId'];

        $this->assertEquals("Request $requestId: 200 OK\nRequest $requestId: 403 Forbidden\nRequest $requestId: 200 OK\n", $buffer);
}
```

#### setMethodStaticVariables

`setMethodStaticVariables(string $class, string $method, array $values): void`

Set the static variablesof the class `$class` and method `$method` to the values `$values`.

This will work on both static and instance methods.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class ListComponent {
    public function render(){
        static $hash;

        if(!$hash){
            $hash = md5(microtime());
        }

        return '<ul screen=' . $hash . '><li>Item One</li><li>Item Two</li></ul>';
    }
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_class_method_static_variables()
    {
        $newValues = array_merge(
            $this->getMethodStaticVariables(ListComponent::class, 'render'),
            ['hash' => 'some-hash']
        );
        $this->setClassMethodStaticVariables( ListComponent::class, 'render', [
          'hash' => 'some-hash'
        ]);

        $component = new ListComponent();

        $this->assertEquals(
            '<ul data-screen="some-hash"><li>Item One</li><li>Item Two</li></ul>', 
            $component->render()
        );
    }
}
```

You do not need to reset the static variable of a class method that was set with `setMethodStaticVariables` explicitly
using `resetMethodStaticVariables` explicitly: the trait will take care of cleaning up all the modifications made to the
functions, methods and class attributes after each test.

#### resetMethodStaticVariables

`resetMethodStaticVariables(string $class, string $method): void`

Resets the static variables of the class `$class` method `$method` to their original values.

#### setFunctionStaticVariable

`setFunctionStaticVariables(string $function, string $variable, mixed $value): void`

Set the static variable `$variable` of the function `$function` to the value `$value`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

function renderScreen(): string{
    static $rendered;

    if($rendered){
        return;
    }

    $html = '<p>Some HTML</p>';

    $rendered = true;

    return $html;
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_function_static_variables()
    {
        $this->setFunctionStaticVariables('renderScreen', ['rendered' => false]);

        $this->assertEquals('<p>Some HTML</p>', renderScreen());
    }
}
```

You do not need to reset the value of a function static variable set using the `resetFunctionStaticVariables` method
explicitly: the trait will take care of cleaning up all the modifications made to the functions, methods and class
attributes after each test.

#### getFunctionStaticVariables

`getFunctionStaticVariables(string $function, ): array`

Get the value of the static variable `$variable` of the function `$function`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

function renderScreen(): string {
    static $screenHash;

    if(!$screenHash){
        $screenHash = md5(microtime());
    }
    
    return '<p data-screen="' . $screenHash . '">Some HTML</p>';
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_set_function_static_variables()
    {
        $screenHash = $this->getFunctionStaticVariables('renderScreen')['screenHash'];

        $this->assertEquals('<p data-screen="' . $screenHash . '">Some HTML</p>', renderScreen());
    }
}
```

#### resetFunctionStaticVariables

`resetFunctionStaticVariables(string $function): void`

Resets the static variables of the function `$function` set with the `setFunctionStaticVariables` method.

#### addFunction

`addFunction(string $function, Closure $closure): void`

Add a global or namespaced function to the current scope.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_add_function()
    {
        $this->addFunction('myGlobalFunction', fn() => 23);
        $this->addFunction('Acme\Project\namespacedFunction', fn() => 89);

        $this->assertEquals(23, myGlobalFunction());
        $this->assertEquals(89, Acme\Project\namespacedFunction());
    }
}
```

#### removeFunction

`removeFunction(string $function): void`

Removes the global or namespaced function `$function` from the current scope.
This will work for functions defined using the [`addFunction`](#addfunction) method or defined elsewhere.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_add_function()
    {
        $this->addFunction('myGlobalFunction', fn() => 23);
        $this->addFunction('Acme\Project\namespacedFunction', fn() => 89);

        $this->removeFunction('some_plugin_function');
        $this->removeFunction('Acme\Project\namespacedFunction');

        // Added with addFunction.
        $this->assertFalse(function_exists('myGlobalFunction');
        $this->assertFalse(function_exists('Acme\Project\namespacedFunction');

        $this->assertFalse(function_exists('some_plugin_function');
        $this->assertFalse(function_exists('Another\Plugin\some_function');
    }
}
```

You do not need to remove a function added with [`addFunction`](#addfunction) using `removeFunction` explicitly: the
trait will take care of cleaning up all the modifications made to the functions, methods and class attributes after each
test.

#### preventExit

`preventExit(): void`

Prevents `exit` or `die` calls executed after the method from terminating the PHP process calling `exit` or `die`.

```php
<?php

use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\TestCase\WPTestCase;

function printAndDie(): void{
    print 'Some HTML';
    die();
}

class MyTest extends WPTestCase
{
    use UopzFunctions;

    public function test_can_prevent_exit()
    {
        $this->preventExit();

        ob_start();
        printAndDie();
        $buffer = ob_get_clean();

        $this->assertEquals('Some HTML', $buffer);
    }
}
```

#### restoreExit

`allowExit(): void`

Restores the original behavior of the `exit` and `die` functions.

You do not need to restore the exit behavior for a exit that was prevented using [`preventExit`](#preventexit)
using `allowExit` explicitly: the trait will take care of cleaning up all the modifications made to the functions,
methods and class attributes after each test.

[1]: https://www.php.net/manual/en/book.uopz.php

[2]: https://pecl.php.net/package/uopz

[3]: https://github.com/lucatume/function-mocker

[4]: https://www.php.net/manual/en/class.streamwrapper.php

[5]: https://blog.benoitblanchon.fr/build-php-extension-on-windows/

[6]: https://www.php.net/manual/en/install.pecl.windows.php

[7]: https://github.com/krakjoe/uopz/blob/master/INSTALL.md

[8]: https://github.com/lucatume/wp-browser/blob/78a5b5a691170a27b807a75ae063131e1ba3a87e/.github/workflows/test.yaml

[9]: https://xdebug.org
