---
title: Automatically change database during acceptance and functional tests
---

**You should always back up any site you run tests on if you care about the site content.**

Now this disclaimer has been made *ad nauseam*; there's a simple way to use a different database when during tests.

## Identifying requests

The first component of this solution is identifying the source of the current HTTP request.  
WordPress makes this identification before deciding which database to use.  

To provide the WordPress installation with this information, you can set the `headers` entry of the `WPBrowser` or `WPWebDriver` module in the suite configuration file.

As an example here is an `acceptance` suite configuration file setting two custom headers, `X_WPBROWSER_REQUEST` and `X_TEST_REQUEST`, on each request sent by the `WPWebDriver` module:

```yaml
actor: AcceptanceTester
modules:
    enabled:
        - WPDb
        - WPBrowser
        - \Helper\Acceptance
    config:
        WPDb:
            dsn: 'mysql:host=localhost;dbname=tests'
            user: 'root'
            password: 'root'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            waitlock: 10
            url: 'http://wp.test'
            urlReplacement: true
            tablePrefix: 'wp_'
        WPBrowser:
            url: 'http://wp.test'
            adminUsername: 'admin'
            adminPassword: 'admin'
            adminPath: '/wp-admin'
            headers:
                X_WPBROWSER_REQUEST: 1
                X_TEST_REQUEST: 1
```

> The two headers are sent on each HTTP request type, not just on `GET` type requests.

## Using a different database to handle test requests

Now that each request made by the `WPWebDriver` module contains those two headers, it's time for WordPress to check those and change the database to use accordingly.

The database to use is set by the `DB_NAME` constant that is, in turn, set in the `wp-config.php` file.  
Different setups could involve more complex configurations for the `wp-config.php` file but, for the sake of simplicity, I assume the default WordPress `wp-config.php` file structure.  
In the example below, the default database name is `wordpress`, while the name of the test database is `tests`.

```diff
- define( 'DB_NAME', 'wordpress' );
+ if( isset( $_SERVER['HTTP_X_TEST_REQUEST'] ) && $_SERVER['HTTP_X_TEST_REQUEST'] ) {
+     define( 'DB_NAME', 'tests' );
+ } else {
+     define( 'DB_NAME', 'wordpress' );
+ }
```

The diff shows the replacement done in the WordPress installation `wp-config.php` file.

For copy-and-paste pleasure, replace the line starting with:

```php
define( 'DB_NAME', 'default_db_name' );
```

With this snippet:

```php
if( isset( $_SERVER['HTTP_X_TEST_REQUEST'] ) && $_SERVER['HTTP_X_TEST_REQUEST'] ) {
      define( 'DB_NAME', 'test_db_name' );
} else {
      define( 'DB_NAME', 'default_db_name' );
}
```

Where `default_db_name` is the name of the database your test WordPress installation normally uses.

Happy, and safer, testing.
