# wp-browser

[![CI](https://github.com/lucatume/wp-browser/workflows/CI/badge.svg)](https://github.com/lucatume/wp-browser/actions?query=branch%3Amaster)

You can use wp-browser to test WordPress sites, plugins and themes.

## Installation

Add wp-browser to your project as a development dependency using [Composer](https://getcomposer.org/):

```bash
cd my-wordrpess-project
composer require --dev lucatume/wp-browser
```

Initialize wp-browser to quickly configured to suite your project and setup:

```bash
vendor/bin/codecept init wpbrowser
```

The command walks you through a series of questions to configure wp-browser for your project by either following a
default configuration or by asking you to provide the information needed to configure it.

### Configuration

The default configuration will get you started in little time using:

* MySQL as the database engine, using [Docker][1] to run the database server
* PHP built-in web server to serve the WordPress site
* Chromedriver installed using [the `webdriver-binary/binary-chromedriver`][2] Composer package

If you're working on a plugin or theme project, he default configuration will add some extra steps:

* install the latest version of WordPress in the `tests/_wordpress` directory
* create a `tests/_plugins` directory: any file or directory in this directory will be symlinked into the WordPress
  installation in `tests/_wordpress/wp-content/plugins`
* create a `tests/_themes` directory: any file or directory in this directory will be symlinked into the WordPress
  installation in `tests/_wordpress/wp-content/themes`

For most projects this configuration will be enough to get started with testing.
The default configuration will start all the required services before running the tests; you can start and stop the
running services with the following commands:

```bash
vendor/bin/codecept wp:dev-start
vendor/bin/codecept wp:dev-stop
````

If you decide to skip the default configuration, you will be able to set up `wp-browser` to suit your needs and local
setup by editing the `tests/.env` file.
The inline documentation in the file will guide you through the configuration process.

### Running tests

The configuration will set up two test suites:

* `integration` to run your project code in the context of WordPress. This suite works like the one used
  by [WordPress Core][6] to run [PHPUnit][3] tests. Integration tests are often referred to as "unit tests" in the
  WordPress ecosystem. These are usually low-level and fast tests.
* `end2end` to run tests that interact with the WordPress site using a browser. The default configuration will "drive"
  Chrome using ChromeDriver. These tests are high-level and slower than integration tests.

You can run all the tests using this command:

```bash
vendor/bin/codecept run
``` 

> Note: the project replaces Codeception `run` command with one that will run each suite in a separate process. You can
> invoke the original Codeception command using the `codeception:run` command.

You can run a single test suite using this command:

```bash
vendor/bin/codecept run integration
vendor/bin/codecept run end2end
```

There are more commands available to run tests, you can find them in the [Codeception documentation][4].

## Getting support for wp-browser configuration and usage

The best place to get support for wp-browser is [the project documentation][7].
Since this project builds on top of [PHPUnit][3] and [Codeception][4], you can also refer to their documentation.

If you can't find the answer to your question here you can ask on
the ["Issues" section of the wp-browser repository][5].

Finally, you can [contact me directly][7] to set up a call to discuss your project needs and how wp-browser can help
you.

## Sponsors

A thanks to my sponsors: you make maintaining this project easier.

[1]: https://www.docker.com/

[2]: https://packagist.org/packages/webdriver-binary/binary-chromedriver

[3]: https://phpunit.de/

[4]: https://codeception.com/

[5]: https://github.com/lucatume/wp-browser/issues

[6]: https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/

[7]: /docs/README.md
