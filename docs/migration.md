Depending on your project PHP compatibility, you have three options to choose from:

* Your project supports PHP `7.1` to `7.4`: [migrate to wp-browser version `3.5`](#migrate-from-version-3-to-35)
* Your project supports PHP `8.0` or above: [migrate to wp-browser version `4.0`](#migrate-from-version-3-to-4)
* You cannot, or do not want, to migrate from version `3` of wp-browser to a new
  version: [see how you can lock your reuirements to avoid the upgrade](#staying-on-version-lower-than-35)

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

### Changes common to version 3.5 and 4.0

1. If your test code is loading deprecated functions, arguments, classes, files, or hooks, you need to update your test
   code to let the test case know using the `setExpectedDeprecated` method:
    ```php
    use lucatume\WPBrowser\TestCase\WPTestCase;
    class MyTestUsingDeprecatedFunctionTest extends WPTestCase {
        public function test_it_can_use_deprecated_function() {
            $this->setExpectedDeprecated( 'my_deprecated_function' );
            my_deprecated_function();
        }
    }
    ```
   Previously, your code could just filter
   the `deprecated_function_trigger_error`, `deprecated_argument_trigger_error`, `deprecated_class_trigger_error`, `deprecated_file_trigger_error`, `deprecated_hook_trigger_error`,
   and `doing_it_wrong_trigger_error` hooks to return `false` to tolerate the deprecation notices in tests.

2. If your test code is knowingly triggering doing-it-wrong notices, you need to update your test code to let the test
   case know using the `setExpectedIncorrectUsage` method:
    ```php
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

## Staying on version lower than 3.5

Update your `composer.json` file to lock the required version of `wp-browser` to a version less than `3.5`:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "<3.5"
  }
}
```
