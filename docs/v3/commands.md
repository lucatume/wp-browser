# Custom commands provided by wp-browser
The project comes with its own set of custom Codeception commands.  

The commands provide functionalities to quickly scaffold different types of tests.
Any other `codecept` command remains intact and available. 

## Adding the commands to an existing project
The commands are added to the main Codeception configuration file, `codeception.yml`, when scaffolding a project via the `codecept init wp-browser` command.  

They can be added to any existing project adding, or editing, the `commands` section of the configuration file:

```yaml
extensions:
    commands:
        - "Codeception\\Command\\GenerateWPUnit"
        - "Codeception\\Command\\GenerateWPRestApi"
        - "Codeception\\Command\\GenerateWPRestController"
        - "Codeception\\Command\\GenerateWPRestPostTypeController"
        - "Codeception\\Command\\GenerateWPAjax"
        - "Codeception\\Command\\GenerateWPCanonical"
        - "Codeception\\Command\\GenerateWPXMLRPC"
```

## Generation commands
The library provides commands to quickly scaffold **integration** test cases for specific types of WordPress components, see [levels of testing for more information](./../levels-of-testing.md).  

The tests are almost identical to the ones you could write in a [PHPUnit based Core suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/) with the exception of extending the `Codeception\TestCase\WPTestCase` test case.

### generate:wpunit
Generates a test case extending the `\Codeception\TestCase\WPTestCase` class using the

```sh
  codecept generate:wpunit suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

### generate:wprest
Generates a test case extending the `\Codeception\TestCase\WPRestApiTestCase` class using the

```sh
  codecept generate:wprest suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPRestApiTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

### generate:wprestcontroller
Generates a test case extending the `\Codeception\TestCase\WPRestControllerTestCase` class using the

```sh
  codecept generate:wprest suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPRestControllerTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```
### generate:wprestposttypecontroller
Generates a test case extending the `\Codeception\TestCase\WPRestPostTypeControllerTestCase` class using the

```sh
  codecept generate:wprest suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPRestPostTypeControllerTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

### generate:wpajax
Generates a test case extending the `\Codeception\TestCase\WPAjaxTestCase` class using the

```sh
  codecept generate:wpajax suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPAjaxTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

### generate:wpxmlrpc
Generates a test case extending the `\Codeception\TestCase\WPXMLRPCTestCase` class using the

```sh
  codecept generate:wpxmlrpc suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPXMLRPCTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

### generate:wpcanonical
Generates a test case extending the `\Codeception\TestCase\WPCanonicalTestCase` class using the

```sh
  codecept generate:wpcanonical suite SomeClass
```

The command will generate a skeleton test case like

```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPCanonicalTestCase
{
    public function setUp()
    {
      parent::setUp();
    }

    public function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```


