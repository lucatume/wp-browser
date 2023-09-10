## Codeception commands provided by the library

The library provides some custom commands that can be added to the project Codeception configuration file (
either `codeception.yml` or `codeception.dist.yml`).

### `run` and `codeception:run`

Enable the commands with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\RunOriginal"
    - "lucatume\\WPBrowser\\Command\\RunAll"
```

WordPress extensive use of global variables, constants and side effectes makes it difficult to run multiple test suites
in the same process without running into conflicts due to leaking state and side effects.
For this reason the project replaces Codeception `run` command with one that will run each suite in a separate process.
You can invoke the original Codeception command using the `codeception:run` command.
Just like [the original][1], the `run` command accepts all the arguments and options of the original Codeception
command.

Run all the suites, each one in a separate process:

```bash
vendor/bin/codecept run
```

Run only the `Integration` suite:

```bash
vendor/bin/codecept run Integration
```

Run a specific test file:

```bash
vendor/bin/codecept run Integration tests/Integration/MyTest.php
```

Run a specific test method:

```bash
vendor/bin/codecept run Integration tests/Integration/MyTest.php:testMyMethod
```

Read the [Codeception documentation][1] for more information about the `run` command.

[1]: https://codeception.com/docs/reference/Commands#Run

### `dev:start`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\DevStart"
```

If not already running, start the services required to run the tests.
The started services are read from the Codeception configuration file (either `codeception.yml`
or `codeception.dist.yml`), from the `extensions` section, under the `config` key.

Given the following configuration:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\ChromeDriverController
    - lucatume\WPBrowser\Extension\BuiltInServerController
    - lucatume\WPBrowser\Extension\DockerComposeController
  config:
    "lucatume\\WPBrowser\\Extension\\ChromeDriverController":
      port: '%CHROMEDRIVER_PORT%'
    "lucatume\\WPBrowser\\Extension\\BuiltInServerController":
      docroot: '%WORDPRESS_ROOT_DIR%'
      workers: 5
      port: '%BUILT_IN_SERVER_PORT%'
    "lucatume\\WPBrowser\\Extension\\DockerComposeController":
      compose-file: 'tests/docker-compose.yml'
      env-file: 'tests/.env'
```

Running the command will start ChromeDriver, the built-in PHP server and Docker Compose.

### `dev:stop`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\DevStop"
```

If running, stop the services required to run the tests.
The stopped services are read from the Codeception configuration file (either `codeception.yml`
or `codeception.dist.yml`), from the `extensions` section, under the `config` key.

Given the following configuration:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\ChromeDriverController"
    - "lucatume\\WPBrowser\\Extension\\BuiltInServerController"
    - "lucatume\\WPBrowser\\Extension\\DockerComposeController"
  config:
    "lucatume\\WPBrowser\\Extension\\ChromeDriverController":
      port: '%CHROMEDRIVER_PORT%'
    "lucatume\\WPBrowser\\Extension\\BuiltInServerController":
      docroot: '%WORDPRESS_ROOT_DIR%'
      workers: 5
      port: '%BUILT_IN_SERVER_PORT%'
    "lucatume\\WPBrowser\\Extension\\DockerComposeController":
      compose-file: 'tests/docker-compose.yml'
      env-file: 'tests/.env'
```

Running the command will stop ChromeDriver, the built-in PHP server and Docker Compose.

### `dev:restart`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\DevRestart"
```

This command is just a shortcut to run `dev:stop` and `dev:start` in sequence.

### `dev:info`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\DevInfo"
```

Provides information about the local testing stack managed by
the [DockerComposeController](extensions.md#dockercomposecontroller), [BuiltInServerController](extensions.md#builtinservercontroller)
and [ChromeDriverController](extensions.md#chromedrivercontroller) extensions.

### `wp:db:import`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\DbImport"
```

You can use [WP CLI][1] to interact with your WordPress installation, but WP CLI does not support SQLite databases in
the context of the `wp db import` command.
This command fills that gap by providing a database dump file import command that will support MySQL and SQLite
databases.

### `wp:db:export`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\DbExport"
```

You can use [WP CLI][1] to interact with your WordPress installation, but WP CLI does not support SQLite databases in
the context of the `wp db export` command.
This command fills that gap by providing a database dump file export command that will support MySQL and SQLite
databases.

### `chromedriver:update`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\ChromedriverUpdate"
```

If you're using Chromedriver as a binary installed in the Composer vendor directory (`vendor/bin` by default), you can
use this command to update it.
This command will download the latest version of Chromedriver compatible with the Chrome version installed on your
machine in the Composer vendor directory.

### `generate:wpunit`

Enable the command with:

```yaml
extensions:
  commands:
    - "lucatume\\WPBrowser\\Command\\GenerateWPUnit"
```

Generate a test case extending the `lucatume\WPBrowser\TestCase\WPTestCase` class.
The class incorporates the WordPress test case from [the `wordpress-develop`][2] repository and adds some utility
methods to make testing easier in the context of Codeception.

The `lucatume\WPBrowser\TestCase\WPTestCase` class is the one that should be used when writing tests for WordPress
code when using the `WPLoader` module.

Together with the `WPLoader` module, the `WPTestCase` class provides a number of functionalities to clean up the
database
after each test method and to reset the global state of WordPress.

#### Every test method runs in a transaction

Database queries running in the context of test methods of a test case extending the `WPTestCase` class will run in a
transaction that is rolled back after the test method is run. This means that any database change happening in the
context of a test method will not appear in the database while the test is running and after the test is run.

[1]: https://wp-cli.org/

[2]: https://github.com/WordPress/wordpress-develop/tree/trunk/tests/phpunit
