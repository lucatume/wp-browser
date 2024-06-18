This extension will symlink the plugins and themes specified in the `plugins` and `themes` configuration parameters to
the WordPress installation plugins and themes directories, respectively.

The plugins and themes will be symlinked before each suite, and removed after each suite.

### Configuration

The extension can be configured with the following parameters:

* required
    * `wpRootFolder` - the relative (to the current working directory) or absolute path to the WordPress installation
      root folder, the directory that contains the `wp-load.php` file.
* optional
    * `cleanupAfterSuite` - default `false`, a boolean value to indicate if the symlinks created by the extension
      sshould be removed after the suite ran.
    * `plugins`- a list of plugin **directories** to symlink to the WordPress installation plugins directory, if not set
      the plugin symlinking will be skipped.
    * `themes`- a list of theme **directories** to symlink to the WordPress installation themes directory, if not set
      the theme symlinking will be skipped.

### Configuration Examples

Example configuration symbolically linking the plugins and themes to the WordPress installation plugins and themes
directories:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\Symlinker"
  config:
    "lucatume\\WPBrowser\\Extension\\Symlinker":
      wpRootFolder: /var/www/html
      plugins:
        - '.' # Relative path, the current working directory.
        - /home/plugins/plugin-1 # Absolute path to a plugin directory.
        - vendor/acme/plugin-2 # Relative path to a plugin directory.
      themes:
        - /home/theme-1 # Absolute path to a theme directory.
        - vendor/acme/theme-2 # Relative path to a theme directory.
```

The extension can access environment variables defined in the tests configuration file:

```yaml
extensions:
  enabled:
    - "lucatume\\WPBrowser\\Extension\\Symlinker"
  config:
    "lucatume\\WPBrowser\\Extension\\Symlinker":
      wpRootFolder: '%WP_ROOT_FOLDER%'
      plugins:
        - '%PLUGIN_STORAGE%/plugin-1'
        - '%PLUGIN_STORAGE%/plugin-2'
      themes:
        - '%THEME_STORAGE%/theme-1'
        - '%THEME_STORAGE%/theme-2'
```
