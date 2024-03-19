## AirplaneMode module

This module allows you to easily put the website under test in "airplane mode", preventing it from making any network requests.  

The module uses <a href="https://github.com/norcross/airplane-mode" target="_blank">the `norcross/airplane-mode` plugin</a> to add or remove it from the website must-use plugins directory when activated.

This module should be used together with [the `WPWebDriver`][1] or [`WPBrowser`][2] modules.  

## Configuration

* `muPluginsDir` - **required**; the path to the WordPress must-use plugins directory.
* `symlink` - whether to symlink the plugin or copy it. By default, the plugin is **copied** in the must-use plugins directory and `symlink` is set to `false`. If you're **not** using containers, that will ignore symlinked plugins, you can set `symlink` to `true` to symlink the plugin in the must-use plugins directory. Symbolic linking is faster and uses less disk space than copying the plugin.

Example configuration to symlink the plugin in the `muPluginsDir` directory before the tests:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\AirplaneMode:
      muPluginsDir: 'var/wordpress/wp-content/mu-plugins'
      symlink: true
```

Example configuration to copy the plugin in the `muPluginsDir` directory before the tests:

```yaml
modules:
  enabled:
    lucatume\WPBrowser\Module\AirplaneMode:
      muPluginsDir: 'var/wordpress/wp-content/mu-plugins'
      symlink: false
```

The module will either symlink or copy the plugin in the `muPluginsDir` directory, depending on the `symlink` configuration parameter before the test suite runs, and will remove it after the test suite has run.

[1]: ./WPWebDriver.md
[2]: ./WPBrowser.md
