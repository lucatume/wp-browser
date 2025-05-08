Depending on your project PHP compatibility, you have three options to choose from:

* Your project supports PHP `7.1` to `7.4`: [migrate to wp-browser version `3.5`](#migrate-from-version-3-to-35)
* Your project supports PHP `8.0` or above: [migrate to wp-browser version `4.0`](#migrate-from-version-3-to-4)
* You cannot, or do not want, to migrate from version `3` of wp-browser to a new
  version: [see how you can lock your reuirements to avoid the upgrade](#staying-on-version-3-lower-than-35)

## Version 3.5 and 4.0

Version `3.5` and `4.0` are the latest versions of wp-browser.  
Version `3.5` is a transpile of version `4` to be compatible with PHP `7.1` to `7.4` that contains, for all intents and
purposes, the same facilities and systems contained in version `4` of wp-browser adapter to work on lower PHP versions
and version `4` of Codeception.

Future development of wp-browser will happen on version `4` and will be transpiled to version `3.5` for
back-compatibility purposes.

## Migrate from version 3 to 3.5

1. Update the required version of `wp-browser` in your `composer.json` file to `3.5`:
   ```json
   {
     "require-dev": {
       "lucatume/wp-browser": "^3.5"
     }
   }
   ```
2. See the [changes common to version 3.5 and 4.0](#changes-common-to-version-35-and-40)

## Migrate from version 3 to 4

1. Update the required version of `wp-browser` in your `composer.json` file to `4.0`:
    ```json
    {
      "require-dev": {
        "lucatume/wp-browser": "^4.0"
      }
    }
    ```
2. See the [changes common to version 3.5 and 4.0](#changes-common-to-version-35-and-40)

## Changes common to version 3.5 and 4.0

1. Update your main Codeception configuration file (e.g. `codeception.yml`) to enable the new commands:
   
   ```yaml
   extensions:
     commands:
       - "lucatume\\WPBrowser\\Command\\RunOriginal"
       - "lucatume\\WPBrowser\\Command\\RunAll"
       - "lucatume\\WPBrowser\\Command\\DbExport"
       - "lucatume\\WPBrowser\\Command\\DbImport"
       - "lucatume\\WPBrowser\\Command\\MonkeyCachePath"
       - "lucatume\\WPBrowser\\Command\\MonkeyCacheClear"
   ```
2. Along with the new commands, update the existing commands to use the `lucatume\WPBrowser\Command\` namespace:

   ```diff
   extensions:
     commands:
   -    - "Codeception\\Command\\GenerateWPUnit"
   -    - "Codeception\\Command\\GenerateWPRestApi"
   -    - "Codeception\\Command\\GenerateWPRestController"
   -    - "Codeception\\Command\\GenerateWPRestPostTypeController"
   -    - "Codeception\\Command\\GenerateWPAjax"
   -    - "Codeception\\Command\\GenerateWPCanonical"
   -    - "Codeception\\Command\\GenerateWPXMLRPC"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPUnit"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPRestApi"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPRestController"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPRestPostTypeController"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPAjax"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPCanonical"
   +    - "lucatume\\WPBrowser\\Command\\GenerateWPXMLRPC"
   ```
3. If your test code is loading deprecated functions, arguments, classes, files, or hooks, you need to update your test
   code to let the test case know using the `setExpectedDeprecated` method:
    ```php
    <?php
   
    use lucatume\WPBrowser\TestCase\WPTestCase;
   
    class MyTestUsingDeprecatedCode extends WPTestCase {
        public function test_deprecated_function() {
            // add_filter( 'deprecated_function_trigger_error', '__return_false' );
            $this->setExpectedDeprecated( 'my_deprecated_function' );
            my_deprecated_function();
        }
   
        public function test_deprecated_class(){
            // add_filter( 'deprecated_class_trigger_error', '__return_false' );
            $this->setExpectedDeprecated( 'MyDeprecatedClass' );
            new MyDeprecatedClass();
        }
   
        public function test_deprecated_file(){
            // add_filter( 'deprecated_file_trigger_error', '__return_false' );
            $this->setExpectedDeprecated( '/path/to/my_deprecated_file.php' );
            require_once 'my_deprecated_file.php';
        }
   
        public function test_deprecated_hook(){
            // add_filter( 'deprecated_hook_trigger_error', '__return_false' );
            $this->setExpectedDeprecated( 'my_deprecated_hook' );
            do_action( 'my_deprecated_hook' );
        }
    }
    ```
   Previously, your code could just filter
   the `deprecated_function_trigger_error`, `deprecated_argument_trigger_error`, `deprecated_class_trigger_error`, `deprecated_file_trigger_error`, and `deprecated_hook_trigger_error`, hooks to return `false` to tolerate the deprecation notices in tests.  
4. If your test code is directly modifying properties like `$expected_deprecated` or `$expected_doing_it_wrong` directly, you need to update your test code to use the `setExpectedDeprecated` and `setExpectedIncorrectUsage` methods:
    ```php
    <?php
   
    use lucatume\WPBrowser\TestCase\WPTestCase;
    class MyTestUsingDeprecatedCode extends WPTestCase {
        public function test_deprecated_function() {
            // $this->expected_deprecated[] = 'my_deprecated_function';
            $this->setExpectedDeprecated( 'my_deprecated_function' );
            my_deprecated_function();
        }
   
        public function test_doing_it_wrong(){
            // $this->expected_doing_it_wrong[] = 'my_doing_it_wrong';
            $this->setExpectedIncorrectUsage( 'my_doing_it_wrong' );
            my_doing_it_wrong();
        }
    }
    ```  
5. If your test code is knowingly triggering doing-it-wrong notices, you need to update your test code to let the test
   case know using the `setExpectedIncorrectUsage` method:
    ```php
    <?php
   
    use lucatume\WPBrowser\TestCase\WPTestCase;
    class MyTestUsingDoingItWrongTest extends WPTestCase {
        public function test_it_can_use_doing_it_wrong() {
            $this->setExpectedIncorrectUsage( 'my_doing_it_wrong' );
            my_doing_it_wrong();
        }
    }
    ```
   Previously, your code could just filter the `doing_it_wrong_trigger_error` hook to return `false` to tolerate the
   doing-it-wrong notices in tests.  
6. Some assertion methods have, in more recent versions of the PHPUnit core suite, adopted stricter type checks when it comes to comparison. E.g., the `assertEqualFields` will now check the object to check the fields on is actually an object. Depending on how loose your use of assertions was before, you might have to update your work to make it pass the stricter checks:
   ```php
   <?php
   
   use lucatume\WPBrowser\TestCase\WPTestCase;
   
    class MyTestUsingAssertEqualFields extends WPTestCase {
         public function test_it_can_use_assert_equal_fields() {
            // Cast the array to an object explicitly.
            $this->assertEqualFields( (object) [ 'a' => 1 ], [ 'a' => 1 ] );
         }
    }
   ```   
7. Other updates to the Core PHPUnit test case will report use of deprecated functions more promptly; if your code is using deprecated functions that might have escaped previous versions of wp-browser, you will need to update them.  
8. If you're using the `@runInSeparateProcess` annotation for tests in your suite, you will need to enable the `IsolationSupport` extension in your suite configuration file:
   
   ```yaml
   actor: MySuiteTester
   bootstrap: _bootstrap.php
   extensions:
   enabled:
      - "lucatume\\WPBrowser\\Extension\\IsolationSupport"
   modules: # The rest of the module configuration ...
   ```
   
   Alternatively, you can enable the extension in the Codeception main configuration file (e.g. `codeception.yml`).
    
   Read more about the extension in the [Isolation Support extension documentation](extensions.md#isolationsupport).  
9. Global space cleanup between test is more thorough in more recent versions of the Core PHPUnit test suite. Due to this you might see failures in tests that were passing in previous versions due to a "leaking" global state. Examples of this might be nonces being set by previous tests and not being reset. Update your tests to explicitly set all the global stat variables you require for the test to run.

## Staying on version 3, lower than 3.5

Update your `composer.json` file to lock the required version of `wp-browser` to a version less than `3.5`:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "<3.5"
  }
}
```
