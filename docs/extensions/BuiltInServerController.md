This extension will start and stop the PHP built-in web server before and after the tests run.

### Configuration

The extension can be configured with the following parameters:

* required
    * `docroot` - the document root to use for the PHP Built-in server; it can be either an absolute path or a path
      relative to the Codeception root directory. Note the lowercase `r` in the parameter name.
* optional
    * `suites` - an array of Codeception suites to run the server for; if not set the server will be started for all the
      suites.
    * `port` - the port to use for the PHP Built-in server, if not set the server will use port `2389`.
    * `workers` - the number of workers to use for the PHP Built-in server, if not set the server will use `5` workers.
      This is the equivalent of the `PHP_CLI_SERVER_WORKERS` environment variable.

> Note: if you run PHP built-in server on Windows, the `workers` parameter will be ignored and the server will always
> run with a single worker. This limit is not present in WSL.

### Configuration Examples

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\BuiltInServerController"
  config:
    "lucatume\\WPBrowser\\Extension\\BuiltInServerController":
      docroot: /var/www/html
      workers: 5
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\BuiltInServerController"
  config:
    "lucatume\\WPBrowser\\Extension\\BuiltInServerController":
      suites:
        - EndToEnd
        - WebApp
      docroot: '%WORDPRESS_ROOT_DIR%'
      port: '%BUILT_IN_SERVER_PORT%'
      workers: '%BUILT_IN_SERVER_WORKERS%'
```

### This is a service extension

This is a service extension that will be started and stopped by [the `dev:start`](../commands.md#devstart)
and [`dev:stop`](../commands.md#devstop) commands.

