## ChromeDriver Controller Extension

This extension will start and stop the ChromeDriver before and after the tests are run.

### Configuration

The extension can be configured with the following parameters:

* optional
    * `suites` - an array of Codeception suites to run the server for; if not set the server will be started for all the
      suites.
    * `port` - the port to use for the ChromeDriver, if not set the server will use port `9515`.
    * `binary` - the path to the ChromeDriver binary, if not set the server will use the `chromedriver` binary in the
      Composer `bin` directory.

### Configuration Examples

Example configuration starting the server for all suites:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\ChromeDriverController"
  config:
    "lucatume\\WPBrowser\\Extension\\ChromeDriverController":
      port: 4444
      binary: /usr/local/bin/chromedriver
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\ChromeDriverController"
  config:
    suites:
      - EndToEnd
      - WebApp
    "lucatume\\WPBrowser\\Extension\\ChromeDriverController":
      port: '%CHROMEDRIVER_PORT%'
      binary: '%CHROMEDRIVER_BINARY%'
```

You can use [the `chromedriver:update` command](commands.md#chromedriverupdate) to download the latest version of
ChromeDriver compatible with your Chrome browser version and place it in the Composer `bin` directory.

### This is a service extension

This is a service extension that will be started and stopped by [the `dev:start`](commands.md#devstart)
and [`dev:stop`](commands.md#devstop) commands.
