## WPCLI module

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
* `bin` - the path to a custom WP-CLI binary.
* `allow-root` - a boolean value to indicate if the `wp` command should be run with the `--allow-root` flag. Equivalent to the `--allow-root` option of the `wp` command. This is useful when running wp-cli commands as the root user.

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

The following configuration uses a custom WP-CLI binary:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\WPCLI:
      path: /var/wordpress
      bin: /usr/local/bin/wp
```

## Methods

The module provides the following methods:

<!-- methods -->

#### changeWpcliPath
Signature: `changeWpcliPath(string $path)` : `void`  

Changes the path to the WordPress installation that WPCLI should use.

This is the equivalent of the `--path` option.

```php
<?php
// Operate on the installation specified in the `path` config parameter.
$I->cli(['core','version']);
// Change to another installation and run a command there.
$I->changeWpcliPath('var/wordpress-installation-two');
$I->cli(['core','version']);
```

#### cli
Signature: `cli([array|string $command], [?array $env], [mixed $input])` : `int`  

Executes a wp-cli command targeting the test WordPress installation.

```php
<?php
// Activate a plugin via wp-cli in the test WordPress site.
$I->cli(['plugin', 'activate', 'my-plugin']);
// Change a user password.
$I->cli(['user', 'update', 'luca', '--user_pass=newpassword']);
```

#### cliToArray
Signature: `cliToArray(array $command, [?callable $splitCallback], [?array $env], [mixed $input])` : `array`  

Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.

```php
<?php
// Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
$inactiveThemes = $I->cliToArray(['theme', 'list', '--status=inactive', '--field=name']);
// Get the list of installed plugins and only keep the ones starting with "foo".
$fooPlugins = $I->cliToArray(['plugin', 'list', '--field=name'], function($output){
     return array_filter(explode(PHP_EOL, $output), function($name){
             return strpos(trim($name), 'foo') === 0;
     });
});
```

#### cliToString
Signature: `cliToString(array $command, [?array $env], [mixed $input])` : `string`  

Returns the output of a wp-cli command as a string.

```php
<?php
// Return the current site administrator email, using string command format.
$adminEmail = $I->cliToString('option get admin_email');
// Get the list of active plugins in JSON format, two ways.
$activePlugins = $I->cliToString(['plugin', 'list','--status=active', '--format=json']);
$activePlugins = $I->cliToString(['option', 'get', 'active_plugins' ,'--format=json']);
```

#### dontSeeInShellOutput
Signature: `dontSeeInShellOutput(string $text)` : `void`  

Checks that output from last command doesn't contain text.

```php
<?php
// Return the current site administrator email, using string command format.
$I->cli('plugin list --status=active');
$I->dontSeeInShellOutput('my-inactive/plugin.php');
```

#### dontSeeShellOutputMatches
Signature: `dontSeeShellOutputMatches(string $regex)` : `void`  

Checks that output from the last command doesn't match a given regular expression.

```php
<?php
// Return the current site administrator email, using string command format.
$I->cli('option get siteurl');
$I->dontSeeShellOutputMatches('/^http/');
```

#### grabLastCliProcess
Signature: `grabLastCliProcess()` : `lucatume\WPBrowser\WordPress\CliProcess`
#### grabLastShellErrorOutput
Signature: `grabLastShellErrorOutput()` : `string`  

Returns the shell error output of the last command.

#### grabLastShellOutput
Signature: `grabLastShellOutput()` : `string`  

Returns the shell output of the last command.

#### seeInShellOutput
Signature: `seeInShellOutput(string $text)` : `void`  

Checks that output from last command contains text.

```php
<?php
// Return the current site administrator email, using string command format.
$I->cli('option get admin_email');
$I->seeInShellOutput('admin@example.org');
```

#### seeResultCodeIs
Signature: `seeResultCodeIs(int $code)` : `void`  

Checks the result code from the last command.

```php
<?php
// Return the current site administrator email, using string command format.
$I->cli('option get admin_email');
$I->seeResultCodeIs(0);
```

#### seeResultCodeIsNot
Signature: `seeResultCodeIsNot(int $code)` : `void`  

Checks the result code from the last command.

```php
<?php
// Return the current site administrator email, using string command format.
$I->cli('invalid command');
$I->seeResultCodeIsNot(0);
```

#### seeShellOutputMatches
Signature: `seeShellOutputMatches(string $regex)` : `void`  

Checks that output from the last command matches a given regular expression.

```php
<?php
// Return the current site administrator email, using string command format.
$I->cli('option get admin_email');
$I->seeShellOutputMatches('/^\S+@\S+$/');
```
<!-- /methods -->

Explore the [WP-CLI documentation][1] for more information on the available commands.

[1]: https://wp-cli.org/

[2]: https://codeception.com/docs/AcceptanceTests

[3]: https://codeception.com/docs/AdvancedUsage#Cest-Classes 
