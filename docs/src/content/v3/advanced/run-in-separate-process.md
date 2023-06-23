---
title: Running tests in separate processes
---

PHPUnit offers the possibility to run tests [in a separate PHP process][1]; Codeception does not officially support the option as of version 4.0.

The wp-browser project tries to fill that gap by supporting the `@runInSeparateProcess` annotation.  
This support comes with some caveats, though:

1. The support is only for test cases extending the `lucatume\WPBrowser\TestCase\WPTestCase` class (the base test case for integration or "WordPress unit" tests)
2. The support wp-browser provides only supports the `@preserveGlobalState` annotation with the `disabled` value; this means there is no support for preserving global state between tests.

Read more about what this means in PHPUnit documentation.

### Why run tests in a separate PHP process?
    
One main reason: isolation.

What does "isolation" means?

Before answering that question, it's essential to understand, via an example, why a lack of isolation might be an issue.

I want to test the `get_api` function. The function will return the correct singleton instance of an API handling class: an instance of `Api` when the function is called in non-admin context, and an instance of `AdminApi` when the function is called in admin context. The `get_api` function is acting as a service locator.

```php
<?php
function get_api(){
    static $api;

    if(null !== $api){
        return $api;
    }

    if( is_admin() ) {
        $api = new Admin_Api();
    } else {
        $api = new Api();
    }

    return $api;
}
```

There are two challenges to testing this function:

1. The `is_admin` function, defined by WordPress, looks up a `WP_ADMIN` constant to know if the context of the current request is an administration UI one or not.
2. The `get_api` function will check for the context and resolve and build the correct instance only once, the first time it's called in the context of a request. 

There are some possible solutions to this problem:

a. Refactor the `get_api` function into a method of an `Api_Factory` object taking the context as a dependency, thus allowing injection of the "context" (which implies the creation of a Context adapter that will proxy its `is_admin` method to the `is_admin` function). You can find the code for such refactoring in the [OOP refactoring of get_api](#oop-refactoring-of-get_api) section.
b. Refactor the `get_api` function to accept the current `is_admin` value as an input argument, `get_api( $is_admin )`, this refactoring moves part of the complexity of getting hold of the correct instance of the API handler on the client code. Adding more build condition and checks, e.g., if the current request is a REST request or not or some tests on the user authorizations, then, requires adding more input arguments to the `get_api` function: the knowledge of the implementation of the `get_api` method will "leak" to the client code having to replicate complexity throughout the system.

I want to layout possible solutions to the problem to show there is **always** a design alternative to make code testable that might or might not fit the current time or scope constraint.  

In this example, I've inherited the `get_api` function from the existing code, and it cannot be changed, yet I want to test it dealing with the two problems outlined above.

### Running tests in separate PHP processes

To test the `get_api` function shown above I've created a new `wpunit` type of test:

```bash
vendor/bin/codecept g:wpunit integration "api"
```

The command scaffolds a `test/integration/apiTest.php` file that I've modified to ensure full coverage of the `get_api` function:

```php
<?php

class apiTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    public function test_get_api_exists()
    {
        $this->assertTrue(function_exists('get_api'));
    }

    public function test_get_api_will_cache()
    {
        $this->assertSame(get_api(), get_api());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_return_api_if_not_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', false);

        $api = get_api();

        $this->assertInstanceOf(Api::class, $api);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_cache_api_if_not_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', false);

        $api = get_api();

        $this->assertSame(get_api(), $api);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_return_api_if_is_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', true);

        $api = get_api();

        $this->assertInstanceOf(AdminApi::class, $api);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_cache_api_if_is_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', true);

        $api = get_api();

        $this->assertSame(get_api(), $api);
    }
}
```

Some pieces of this code are worth pointing out:

1. There are two test methods, `test_get_api_exists` and `test_get_api_will_cache` that are **not** running in a separate process. Running tests in a separate process provide isolation at the cost of speed, **only tests that require isolation should run in a separate PHP process**.  
2. I instruct the Codeception and PHPUnit test runner to run a test method in a different process by adding two annotations that are both required ** precisely as shown**:
    ```php
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    ```
3. The isolation part of this testing approach shines through when I `define`, in the last four tests, the `WP_ADMIN` constant multiple times. If I try to do that in test code running in the same PHP process, then the second `define` call would cause a fatal error.
4. The isolation has also taken care of the second issue where the `get_api` function caches the `$api` instance after its first resolution in a `static` variable: since each test happens in a self-contained, dedicated PHP process, the `static $api` variable will be `null` at the start of each test.

#### Can I run some tests in the same process and some in a separate process?

Yes. In the example test code in the previous section, the `test_get_api_exists` and `test_get_api_will_cache` test methods are not running in separate processes.  

In your test cases extending the `lucatume\WPBrowser\TestCase\WPTestCase`, you can mix test methods running in the primary PHP process and those running in a separate PHP process without issues.

### OOP refactoring of get_api

In the [Why run tests in a separate PHP process?](#why-run-tests-in-separate-process) section I've outlined a possible refactoring of the `get_api` function to make it testable without requiring the use of separate PHP processes.

I'm providing this refactoring code below for the sake of completeness, the judgment of which approach is "better" is up to the reader.

```php
<?php

class Context_Adapter{

    public function is_admin(){
        return \is_admin();
    }

}

class Api_Factory{
    
    private $api;
    private $context;

    public function __construct(Context_Adapter $context){
        $this->context = $context;
    }

    public function getApi(){
        if(null !== $this->api){
            return $this->api;    
        }

        if($this->context->is_admin()){
            $api = new Admin_Api;
        } else {
            $api = new Api;
        }
        
        return $api;
    }
}
```

Now the `Api_Factory` class can be injected by injecting a mocked `Context_Adapter` class, modifying the return value of the `Context_Adapter::is_admin` method.  

Due to the supposed requirement of the API instance being a singleton, this solution will also require some container or service-locator to ensure at most only one instance of the `Api_Factory` exists at any given time in the context of a request.

[1]: https://phpunit.readthedocs.io/en/9.2/annotations.html?highlight=separate#runtestsinseparateprocesses

