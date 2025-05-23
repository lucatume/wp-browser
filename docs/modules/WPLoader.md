## WPLoader module

A module to load WordPress and make its code available in tests.

Depending on the value of the `loadOnly` configuration parameter, the module will behave differently:

* `loadOnly: false` - The module will load WordPress like [the Core PHPUnit suite][1] does to run integration tests in a
  controlled environment. Use the module in this mode with test cases generated
  using [the `generate:wpunit` command](./../commands.md#generatewpunit).
* `loadOnly: true` - The module will load WordPress to make it available in the context of tests. Use the module in this
  mode in [Cest][2], [Cept][3] and Codeception unit test cases.

## Configuration with loadOnly: false

The module will load WordPress like [the Core PHPUnit suite][1] does to run integration tests in a controlled
environment.  
Together with the test case generated by [the `generate:wpunit` command](./../commands.md#generatewpunit) the module
will:

* take care of running any test method in a database transaction rolled back after each test
* manage and clean up the global environment and context between tests

!!! note

    The module will set the environment variable `WPBROWSER_LOAD_ONLY=0` when running in this mode. This environment variable
    can be used to detect whether WordPress is being loaded by WPBrowser and in which mode.

When used in this mode, the module supports the following configuration parameters:

* `loadOnly` - `false` to load WordPress and run tests in a controlled environment.
* `wpRootFolder` - **required**; the path to the WordPress installation root folder. This can be a relative path to the
  codeception root directory, or an absolute path to the WordPress installation directory. The WordPress installation
  directory is the directory that contains the `wp-load.php` file.
* `dbUrl` - **required**; the URL to the database to use to run tests. The URL must be in the form
  `mysql://username:password@host:port/database` to use a MySQL database, or in the form `sqlite://path/to/database` to
  use a SQLite database. Alternatively, you can use the `dbName`, `dbUser`, `dbPassword`, `dbHost` configuration
  parameters to specify the database connection details.
* `dump` - the path to a database dump, or a set of database dumps, to load before running tests. The dump will be
  loaded only once, after the tests run.
* `tablePrefix` - the database table prefix to use when loading WordPress, defaults to `wp_`.
* `multisite` - a boolean value to indicate if WordPress should be loaded and initialized in multisite mode.
* `dbCharset` - the database charset to use when loading WordPress.
* `dbCollate` - the database collate to use when loading WordPress.
* `domain` - the domain to use when loading WordPress. Equivalent to defining the `WP_TESTS_DOMAIN` constant.
* `adminEmail` - the administrator email to use when loading WordPress. Equivalent to defining the `WP_TESTS_EMAIL`
  constant.
* `title` - the site title to use when loading WordPress. Equivalent to defining the `WP_TESTS_TITLE` constant.
* `phpBinary` - the path to the PHP binary to use to run tests. Defaults to the `WP_PHP_BINARY` constant.
* `language` - the language to use when loading WordPress. Equivalent to defining the `WPLANG` constant.
* `configFile` - a configuration file, or a set of configuration files, to load before the tests to further customize
  and control the WordPress testing environment. This file(s) will be loaded before the WordPress installation is loaded.
* `pluginsFolder` - the path to the plugins folder to use when loading WordPress. Equivalent to defining the
  `WP_PLUGIN_DIR` constant. If both this parameter and the `WP_PLUGIN_DIR` parameter are set, the `WP_PLUGIN_DIR`
  parameter will override the value of this one.
* `WP_CONTENT_DIR` - the path to the content folder to use when loading WordPress in the context of tests. If the
  installation used by the `WPLoader` module defines a `WP_CONTENT_DIR` constant in its `wp-config.php` file, the module
  will throw an exception if this parameter is set. Setting this parameter will affect the `WP_PLUGIN_DIR` and the `WPMU_PLUGIN_DIR`
  parameters.
* `WP_PLUGIN_DIR` - the path to the plugins folder to use when loading WordPress in the context of tests. If the
  installation used by the `WPLoader` module defines a `WP_PLUGIN_DIR` constant in its `wp-config.php` file, the module
  will throw an exception if this parameter is set.
* `WPMU_PLUGIN_DIR` - the path to the mu-plugins folder to use when loading WordPress in the context of tests. If the
  installation used by the `WPLoader` module defines a `WPMU_PLUGIN_DIR` constant in its `wp-config.php` file, the module
  will throw an exception if this parameter is set.
* `plugins` - a list of plugins to activate and load in the WordPress installation. If the plugin is located in the
  WordPress installation plugins directory, then the plugin name can be specified using the `directory/file.php` format.
  If the plugin is located in an arbitrary path inside or outiside of the WordPress installation or project, then the
  plugin name must be specified either as an absolute path or as a relative path to the project root folder.
* `silentlyActivatePlugins` - a list of plugins to activate **silently**, without firing their activation hooks.
  Depending on the plugin, a silent activation might cause the plugin to not work correctly. The list must be in the
  same format as the `plugins` parameter and plugin should be activated silently only if they are not working correctly
  during normal activation and are known to work correctly when activated silently. Plugin paths can be specified
  following the same format of the `plugins` parameter.
* `bootstrapActions` - a list of actions or callables to call **after** WordPress is loaded and before the tests run.
* `theme` - the theme to activate and load in the WordPress installation. The theme can be specified in slug format,
  e.g., `twentytwentythree`, to load it from the WordPress installation themes directory. Alternatively, the theme can
  be specified as an absolute or relative path to a theme folder, e.g., `/home/themes/my-theme`
  or `vendor/acme/vendor-theme`. To use both a parent and ha child theme from arbitrary absolute or relative paths,
  define the `theme` parameter as an array of theme paths, e.g., `['/home/themes/parent-theme', '.']`.
* `AUTH_KEY` - the `AUTH_KEY` constant value to use when loading WordPress. If the `wpRootFolder` path points at a
  configured installation, containing the `wp-config.php` file, then the value of the constant in the configuration file
  will be used, else it will be randomly generated.
* `SECURE_AUTH_KEY` - the `SECURE_AUTH_KEY` constant value to use when loading WordPress. If the `wpRootFolder` path
  points at a configured installation, containing the `wp-config.php` file, then the value of the constant in the
  configuration file will be used, else it will be randomly generated.
* `LOGGED_IN_KEY` - the `LOGGED_IN_KEY` constant value to use when loading WordPress. If the `wpRootFolder` path points
  at a configured installation, containing the `wp-config.php` file, then the value of the constant in the configuration
  file will be used, else it will be randomly generated.
* `NONCE_KEY` - the `NONCE_KEY` constant value to use when loading WordPress. If the `wpRootFolder` path points at a
  configured installation, containing the `wp-config.php` file, then the value of the constant in the configuration file
  will be used, else it will be randomly generated.
* `AUTH_SALT` - the `AUTH_SALT` constant value to use when loading WordPress. If the `wpRootFolder` path points at a
  configured installation, containing the `wp-config.php` file, then the value of the constant in the configuration file
  will be used, else it will be randomly generated.
* `SECURE_AUTH_SALT` - the `SECURE_AUTH_SALT` constant value to use when loading WordPress. If the `wpRootFolder` path
  points at a configured installation, containing the `wp-config.php` file, then the value of the constant in the
  configuration file will be used, else it will be randomly generated.
* `LOGGED_IN_SALT` - the `LOGGED_IN_SALT` constant value to use when loading WordPress. If the `wpRootFolder` path
  points at a configured installation, containing the `wp-config.php` file, then the value of the constant in the
  configuration file will be used, else it will be randomly generated.
* `NONCE_SALT` - the `NONCE_SALT` constant value to use when loading WordPress. If the `wpRootFolder` path points at a
  configured installation, containing the `wp-config.php` file, then the value of the constant in the configuration file
  will be used, else it will be randomly generated.
* `AUTOMATIC_UPDATER_DISABLED` - the `AUTOMATIC_UPDATER_DISABLED` constant value to use when loading WordPress. If
  the `wpRootFolder` path points at a configured installation, containing the `wp-config.php` file, then the value of
  the constant in the configuration file will be used, else it will be randomly generated.
* `WP_HTTP_BLOCK_EXTERNAL` - the `WP_HTTP_BLOCK_EXTERNAL` constant value to use when loading WordPress. If
  the `wpRootFolder` path points at a configured installation, containing the `wp-config.php` file, then the value of
  the constant in the configuration file will be used, else it will be randomly generated.
* `backupGlobals` - a boolean value to indicate if the global environment should be backed up before each test. Defaults
  to `true`. The globals' backup involves serialization of the global state, plugins or themes that define classes
  developed to prevent serialization of the global state will cause the tests to fail. Set this parameter to `false` to
  disable the global environment backup, or use a more refined approach setting the `backupGlobalsExcludeList` parameter
  below. Note that a test case that is explicitly setting the `backupGlobals` property will override this configuration
  parameter.
* `backupGlobalsExcludeList` - a list of global variables to exclude from the global environment backup. The list must
  be in the form of array, and it will be merged to the list of globals excluded by default.
* `backupStaticAttributes` - a boolean value to indicate if static attributes of classes should be backed up before each
  test. Defaults to `true`. The static attributes' backup involves serialization of the global state, plugins or themes
  that define classes developed to prevent serialization of the global state will cause the tests to fail. Set this
  parameter to `false` to disable the static attributes backup, or use a more refined approanch setting
  the `backupStaticAttributesExcludeList` parameter below. Note that a test case that is explicitly setting
  the `backupStaticAttributes` property will override this configuration parameter.
* `backupStaticAttributesExcludeList` - a list of classes to exclude from the static attributes backup. The list must be
  in the form of map from class names to the array of method names to exclude from the backup. See an example below.
* `skipInstall` - a boolean value to indicate if the WordPress installation should be skipped between runs, when already
  installed. Defaults to `false`. During boot, the `WPLoader` module will re-install WordPress and activate, on top of
  the fresh installation, any plugin and theme specified in the `plugins` and `theme` configuration parameters: this can
  be a time-consuming operation. Set this parameter to `true` to run the WordPress installation once and just load it on
  the following runs. To force the installation to run again, rerun the suite using the WPLoader module using
  the `--debug` flag or delete the `_wploader-state.sql` file in the suite directory. This configuration parameter is
  ignored when the `loadOnly` parameter is set to `true`.
* `beStrictAboutWpdbConnectionId` - a boolean value to indicate if the `WPTestCase` class should throw an exception if
  the database connection is closed during any `setUpBeforeClass` method; default is `true`.

This is an example of an integration suite configured to use the module:

```yaml
actor: IntegrationTester
bootstrap: _bootstrap.php
modules:
  enabled:
    - \Helper\Integration
    - lucatume\WPBrowser\Module\WPLoader:
        wpRootFolder: /var/wordpress
        dbUrl: mysql://root:root@mysql:3306/wordpress
        tablePrefix: test_
        domain: wordpress.test
        adminEmail: admin@wordpress.test
        title: 'Integration Tests'
        plugins:
          # This plugin will be loaded from the WordPress installation plugins directory.
          - hello.php
          # This plugin will be loaded from an arbitrary absolute path.
          - /home/plugins/woocommerce/woocommerce.php
          # This plugin will be loaded from an arbitrary relative path inside the project root folder.
          - vendor/acme/project/plugin.php
          # This plugin will be loaded from the project root folder.
          - my-plugin.php
        theme: twentytwentythree # Load the theme from the WordPress installation themes directory.
```

The following configuration uses [dynamic configuration parameters][3] to set the module configuration:

```yaml
actor: IntegrationTester
bootstrap: _bootstrap.php
modules:
  enabled:
    - \Helper\Integration
    - lucatume\WPBrowser\Module\WPLoader:
        wpRootFolder: '%WP_ROOT_FOLDER%'
        dbUrl: '%WP_DB_URL%'
        tablePrefix: '%WP_TABLE_PREFIX%'
        domain: '%WP_DOMAIN%'
        adminEmail: '%WP_ADMIN_EMAIL%'
        title: '%WP_TITLE%'
        plugins:
          - hello.php
          - /home/plugins/woocommerce/woocommerce.php
          - my-plugin.php
          - vendor/acme/project/plugin.php
        # Parent theme from the WordPress installation themes directory, child theme from absolute path.
        theme: [ twentytwentythree, /home/themes/my-theme ]
```

The following example configuration uses a SQLite database and loads a database fixture before the tests run:

```yaml
actor: IntegrationTester
bootstrap: _bootstrap.php
modules:
  enabled:
    - \Helper\Integration
    - lucatume\WPBrowser\Module\WPLoader:
        wpRootFolder: /var/wordpress
        dbUrl: sqlite:///var/wordpress/wp-tests.sqlite
        dump:
          - tests/_data/products.sql
          - tests/_data/users.sql
          - tests/_data/orders.sql
        tablePrefix: test_
        domain: wordpress.test
        adminEmail: admin@wordpress.test
        title: 'Integration Tests'
        plugins:
          - hello.php
          - woocommerce/woocommerce.php
          - my-plugin/my-plugin.php
        theme:
          # Parent theme from relative path.
          - vendor/acme/parent-theme
          # Child theme from the current working directory.
          - .
```

The follow example configuration prevents the backup of globals and static attributes in all the tests of the suite that
are not explicitly overriding the `backupGlobals` and `backupStaticAttributes` properties:

```yaml
actor: IntegrationTester
bootstrap: _bootstrap.php
modules:
  enabled:
    - \Helper\Integration
    - lucatume\WPBrowser\Module\WPLoader:
        wpRootFolder: /var/wordpress
        dbUrl: sqlite:///var/wordpress/wp-tests.sqlite
        dump:
          - tests/_data/products.sql
          - tests/_data/users.sql
          - tests/_data/orders.sql
        tablePrefix: test_
        domain: wordpress.test
        adminEmail: admin@wordpress.test
        title: 'Integration Tests'
        plugins:
          - hello.php
          - woocommerce/woocommerce.php
          - my-plugin/my-plugin.php
        theme: twentytwentythree
        backupGlobals: false
        backupStaticAttributes: false 
```

The following configuration prevents the backup of *some* globals and static attributes:

```yaml
actor: IntegrationTester
bootstrap: _bootstrap.php
modules:
  enabled:
    - \Helper\Integration
    - lucatume\WPBrowser\Module\WPLoader:
        wpRootFolder: /var/wordpress
        dbUrl: sqlite:///var/wordpress/wp-tests.sqlite
        dump:
          - tests/_data/products.sql
          - tests/_data/users.sql
          - tests/_data/orders.sql
        tablePrefix: test_
        domain: wordpress.test
        adminEmail: admin@wordpress.test
        title: 'Integration Tests'
        plugins:
          - hello.php
          - woocommerce/woocommerce.php
          - my-plugin/my-plugin.php
        theme: twentytwentythree
        backupGlobalsExcludeList:
          - my_plugin_will_explode_on_wakeup
          - another_problematic_global
        backupStaticAttributesExcludeList:
          - MyPlugin\MyClass:
              - instance
              - anotherStaticAttributeThatWillExplodeOnWakeup
          - AnotherPlugin\AnotherClass:
              - instance
              - yetAnotherStaticAttributeThatWillExplodeOnWakeup
```

### Handling a custom site structure

The setup process should _just work_ for standard and non-standard WordPress installations alike.

Even if you're working on a site project using a custom file structure, e.g. [Bedrock][4], you will be able to set up
your site to run tests using the default configuration based on PHP built-in server, Chromedriver and SQLite database.

## Configuration with loadOnly: true

The module will load WordPress from the location specified by the `wpRootFolder` parameter, relying
on [the WPDb module](WPDb.md) to manage the database state.

!!! note

    The module will set the environment variable `WPBROWSER_LOAD_ONLY=1` when running in this mode. This environment variable
    can be used to detect whether WordPress is being loaded by WPBrowser and in which mode.

When used in this mode, the module supports the following configuration parameters:

* `loadOnly` - `true` to load WordPress and make it available in the context of tests.
* `wpRootFolder` - **required**; the path to the WordPress installation root folder. This can be a relative path to the
  codeception root directory, or an absolute path to the WordPress installation directory. The WordPress installation
  directory is the directory that contains the `wp-load.php` file.
* `dbUrl` - **required**; the URL to the database to use to run tests. The URL must be in the form
  `mysql://username:password@host:port/database` to use a MySQL database, or in the form `sqlite://path/to/database` to
  use a SQLite database. Alternatively, you can use the `dbName`, `dbUser`, `dbPassword`, `dbHost` configuration
  parameters to specify the database connection details.
* `domain` - the domain to use when loading WordPress. Equivalent to defining the `WP_TESTS_DOMAIN` constant.
* `configFile` - a configuration file, or a set of configuration files, to load before the tests to further customize
  and control the WordPress testing environment. This file(s) will be loaded before the WordPress installation is loaded.

!!! note

    **The order of the modules matters.**  
    In your suite configuration file place the `WPDb` module **before** the `WPLoader` one to make sure the `WPDb` module will correctly set up the database fixture before the `WPLoader` modules attempts to load WordPress from it.

!!! warning

    The module will define the `DB_NAME`, `DB_USER`, `DB_PASSWORD` and `DB_HOST` constants in the context of loading WordPress.
    This is done to allow the WordPress database connection to be configured using the `dbUrl` configuration parameter.
    **The module will silence the warnings about the redeclaration of these constants**, but in some cases with stricter error
    checking (e.g. Bedrock) this may not be enough. In those cases, you can use the `WPBROWSER_LOAD_ONLY` environment
    variable to detect whether WordPress is being loaded by WPBrowser and in which mode and configured your installation
    accordingly.

The following is an example of the module configuration to run end-to-end tests on the site served
at `http://localhost:8080` URL and served from the  `/var/wordpress` directory:

```yaml
actor: EndToEndTester
bootstrap: _bootstrap.php
modules:
  enabled:
    - \Helper\Integration
    - lucatume\WPBrowser\Module\WPWebDriver:
        url: 'http://localhost:8080'
        adminUsername: 'admin'
        adminPassword: 'password'
        adminPath: '/wp-admin'
        browser: chrome
        host: 'localhost'
        port: '4444'
        path: '/'
        window_size: false
        capabilities:
          "goog:chromeOptions":
            args:
              - "--headless"
              - "--disable-gpu"
              - "--disable-dev-shm-usage"
              - "--proxy-server='direct://'"
              - "--proxy-bypass-list=*"
              - "--no-sandbox"
    - lucatume\WPBrowser\Module\WPDb: # WPDb module before the WPLoader one: position matters!
        dbUrl: 'mysql://root:password@localhost:3306/wordpress'
        url: 'http://localhost:8080'
        tablePrefix: 'wp_'
        dump: 'tests/_data/dump.sql'
        populate: true
        cleanup: true
        reconnect: false
        urlReplacement: true
        originalUrl: http://wordpress.test
        waitlock: 10
        createIfNotExists: true
    - lucatume\WPBrowser\Module\WPLoader:
        loadOnly: true
        wpRootFolder: /var/wordpress
        dbUrl: 'mysql://root:password@localhost:3306/wordpress'
        domain: wordpress.test
```

## Methods

The module provides the following methods:

<!-- methods -->

#### factory

Signature: `factory()` : `lucatume\WPBrowser\Module\WPLoader\FactoryStore`

Accessor method to get the object storing the factories for things.
This method gives access to the same factories provided by the
[Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/).

#### getContentFolder

Signature: `getContentFolder([string $path])` : `string`

Returns the absolute path to the WordPress content directory.

#### getInstallation

Signature: `getInstallation()` : `lucatume\WPBrowser\WordPress\Installation`

#### getPluginsFolder

Signature: `getPluginsFolder([string $path])` : `string`

Returns the absolute path to the plugins directory.

The value will first look at the `WP_PLUGIN_DIR` constant, then the `pluginsFolder` configuration parameter
and will, finally, look in the default path from the WordPress root directory.

#### getThemesFolder

Signature: `getThemesFolder([string $path])` : `string`

Returns the absolute path to the themes directory.

#### getWpRootFolder

Signature: `getWpRootFolder([?string $path])` : `string`

Returns the absolute path to the WordPress root folder or a path within it..
<!-- /methods -->

[1]: https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/

[2]: https://codeception.com/docs/AcceptanceTests

[3]: https://codeception.com/docs/AdvancedUsage#Cest-Classes

[4]: https://roots.io/bedrock/
