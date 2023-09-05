## Codeception Extensions provided by the library

The library provides some custom Codeception extensions that can be added to the project Codeception configuration file,
in the `extensions` section.

### `BuiltInServerController`

This extension will start and stop the PHP built-in web server before and after the tests run.

The extension can be configured with the following parameters:

* required
    * `docRoot` - the document root to use for the PHP Built-in server; it can be either an absolute path or a path
      relative to the Codeception root directory.
* optional
    * `suites` - an array of Codeception suites to run the server for; if not set the server will be started for all the
      suites.
    * `port` - the port to use for the PHP Built-in server, if not set the server will use port `2389`.
    * `workers` - the number of workers to use for the PHP Built-in server, if not set the server will use `5` workers.
      This is the equivalent of the `PHP_CLI_SERVER_WORKERS` environment variable.

> Note: if you run PHP built-in server on Windows, the `workers` parameter will be ignored and the server will always
> run with a single worker. This limit is not present in WSL.

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\BuiltInServerController
  config:
    lucatume\WPBrowser\Extension\BuiltInServerController:
      docRoot: /var/www/html
      workers: 5
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\BuiltInServerController
  config:
    lucatume\WPBrowser\Extension\BuiltInServerController:
      suites:
        - EndToEnd
        - WebApp
      docRoot: '%WORDPRESS_ROOT_DIR%'
      port: '%BUILT_IN_SERVER_PORT%'
      workers: '%BUILT_IN_SERVER_WORKERS%'
```

This is a service extension that will be started and stopped by [the `dev:start`](commands.md#devstart)
and [`dev:stop`](commands.md#devstop) commands.

### `ChromeDriverController`

This extension will start and stop the ChromeDriver before and after the tests are run.

The extension can be configured with the following parameters:

* optional
    * `suites` - an array of Codeception suites to run the server for; if not set the server will be started for all the
      suites.
    * `port` - the port to use for the ChromeDriver, if not set the server will use port `9515`.
    * `binary` - the path to the ChromeDriver binary, if not set the server will use the `chromedriver` binary in the
      Composer `bin` directory.

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\ChromeDriverController
  config:
    lucatume\WPBrowser\Extension\ChromeDriverController:
      port: 4444
      binary: /usr/local/bin/chromedriver
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\ChromeDriverController
  config:
    suites:
      - EndToEnd
      - WebApp
    lucatume\WPBrowser\Extension\ChromeDriverController:
      port: '%CHROMEDRIVER_PORT%'
      binary: '%CHROMEDRIVER_BINARY%'
```

You can use [the `chromedriver:update` command](commands.md#chromedriverupdate) to download the latest version of
ChromeDriver
compatible with your Chrome browser version and place it in the Composer `bin` directory.

This is a service extension that will be started and stopped by [the `dev:start`](commands.md#devstart)
and [`dev:stop`](commands.md#devstop) commands.

### `DockerComposeController`

This extension will start and stop [a `docker compose` stack][1] before and after the tests are run.

The extension can be configured with the following parameters:

* required
    * `compose-file` - the path to the `docker compose` file to use; it can be either an absolute path or a path
      relative to the Codeception root directory.
* optional
    * `env-file`- the path to the environment file to use; it can be either an absolute path or a path.

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\DockerComposeController
  config:
    lucatume\WPBrowser\Extension\DockerComposeController:
      compose-file: /var/www/html/docker-compose.yml
      env-file: /var/www/html/.env
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - lucatume\WPBrowser\Extension\DockerComposeController
  config:
    suites:
      - EndToEnd
      - WebApp
    lucatume\WPBrowser\Extension\DockerComposeController:
      compose-file: '%DOCKER_COMPOSE_FILE%'
      env-file: '%DOCKER_COMPOSE_ENV_FILE%'
```

This is a service extension that will be started and stopped by [the `dev:start`](commands.md#devstart)
and [`wp:dev-stop`](commands.md#devstop) commands.

[1]: https://docs.docker.com
