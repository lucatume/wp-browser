# WPCLI module

Use [WP-CLI][1] to interact with the WordPress installation under test and issue commands.

This module is used in the context of end-to-end testing, together with, or as a replacement for
the [WPDb module](WPDb.md) to manipulate the database and the [WPFilesystem module](WPFilesystem.md) to manipulate the
site file structure.

This module should be with [Cest][2] and [Cept][3] test cases.

## Configuration

* `path` - **required**; the path to the WordPress installation under test. This can be a relative path to the
  codeception root directory, or an absolute path to the WordPress installation directory. The WordPress installation
  directory is the directory that contains the `wp-load.php` file.
* `url` - the URL of the WordPress installation under test. Equivalent to the `--url` option of the `wp` command.
* `user` - the user to use to run the `wp` command. Equivalent to the `--user` option of the `wp` command.
* `skip-plugins` - a boolean value to indicate if the `wp` command should skip loading plugins. Equivalent to the
  `--skip-plugins` option of the `wp` command.
* `skip-themes` - a boolean value to indicate if the `wp` command should skip loading themes. Equivalent to the
  `--skip-themes` option of the `wp` command.
* `skip-packages` - a boolean value to indicate if the `wp` command should skip loading packages. Equivalent to the
  `--skip-packages` option of the `wp` command.
* `require` - a list of PHP files to require before running the `wp` command. Equivalent to the `--require` option of
  the `wp` command.
* `exec` - PHP code to execute before running the `wp` command. Equivalent to the `--exec` option of the `wp` command.
* `context` - the context to use when running the `wp` command. Equivalent to the `--context` option of the `wp`
  command.
* `color` - a boolean value to indicate if the `wp` command should output in color. Equivalent to the `--color` option
  of the `wp` command.
* `no-color` - a boolean value to indicate if the `wp` command should not output in color. Equivalent to the
  `--no-color` option of the `wp` command.
* `debug` - a boolean value to indicate if the `wp` command should output debug information. Equivalent to the
  `--debug` option of the `wp` command.
* `quiet` - a boolean value to indicate if the `wp` command should suppress informational messages. Equivalent to the
  `--quiet` option of the `wp` command.
* `throw` - a boolean value to indicate if the `wp` command should throw an exception if the command fails.
* `timeout` - the timeout to use when running the `wp` command. When the timeout is reached the command will be
  terminated as a failure.
* `cache-dir` - the directory to use to cache the files WPCLI might downloads. Equivalent to setting
  the `WP_CLI_CACHE_DIR`
  environment variable.
* `config-path` - the path to the `wp-cli.yml` file to use. Equivalent to setting the `WP_CLI_CONFIG_PATH`
  environment variable.
* `custom-shell` - the shell to use to run the `wp` command. Equivalent to setting the `WP_CLI_SHELL` environment
  variable.
* `packages-dir` - the directory to use to store the packages downloaded by the `wp package` command. Equivalent to
  setting the `WP_CLI_PACKAGES_DIR` environment variable.

The following is an example of the module configuration to run WPCLI commands on the `/var/wordpress` directory:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPCLI:
      path: /var/wordpress
      throw: true
```

The following configuration uses [dynamic configuration parameters][3] to set the module configuration:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPCLI:
      path: '%WP_ROOT_DIR%'
      throw: true
```

## Methods

The module provides the following methods:

* `cli(array|string [$command], ?array [$env], mixed [$input])` : `int`
* `cliToArray(array $command, ?callable [$splitCallback], ?array [$env], mixed [$input])` : `array`
* `cliToString(array $command, ?array [$env], mixed [$input])` : `string`
* `dontSeeInShellOutput(string $text)` : `void`
* `dontSeeShellOutputMatches(string $regex)` : `void`
* `grabLastCliProcess()` : `lucatume\WPBrowser\WordPress\CliProcess`
* `grabLastShellErrorOutput()` : `string`
* `grabLastShellOutput()` : `string`
* `seeInShellOutput(string $text)` : `void`
* `seeResultCodeIs(int $code)` : `void`
* `seeResultCodeIsNot(int $code)` : `void`
* `seeShellOutputMatches(string $regex)` : `void`

Explore the [WP-CLI documentation][1] for more information on the available commands.

[1]: https://wp-cli.org/

[2]: https://codeception.com/docs/AcceptanceTests

[3]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 
