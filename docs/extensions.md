# Extensions

The [Codeception testing framework](http://codeception.com/ "Codeception - BDD-style PHP testing.") can be extended in a number of ways.  

The one this project leverages the most are modules but [extensions are another way].  

Modules extend the functionality of Codeception in the context of the tests, while extensions extend its interaction capacities; this is by no means a strict rule but that's usually the case.  

The package contains two additional extensions to facilitate testers' life.

### Symlinker

The `tad\WPBrowser\Extension\Symlinker` extension provides an automation to have the Codeception root directory symbolically linked in a WordPress local installation.  

Since version `3.9` WordPress supports this feature (with some [precautions](https://make.wordpress.org/core/2014/04/14/symlinked-plugins-in-wordpress-3-9/https://make.wordpress.org/core/2014/04/14/symlinked-plugins-in-wordpress-3-9/)) and the extension takes charge of:

* symbolically linking a plugin or theme folder in the specified destination before any suite boots up
* unlinking that symbolic link after all of the suites did run

It's the equivalent of doing something like this from the command line (on a Mac):

```bash
ln -s /my/central/plugin/folder/my-plugin /my/local/wordpress/installation/wp-content/plugins/my-plugin
/my/central/plugin/folder/my-plugin/vendor/bin/codecept run
rm -rf /my/local/wordpress/installation/wp-content/plugins/my-plugin

```

The extension needs small configuration in the `codeception.yml` file:

```yaml
extensions:
    enabled:
        - tad\WPBrowser\Extension\Symlinker
    config:
        tad\WPBrowser\Extension\Symlinker:
            mode: plugin
            destination: /my/local/wordpress/installation/wp-content/plugins
            rootFolder: /some/plugin/folder
```

The arguments are:

* `mode` - can be `plugin` or `theme` and indicates whether the current Codeception root folder being symlinked is a plugin or a theme one
* `destination` - the absolute path to the WordPress local installation plugins or themes folder; to take the never ending variety of possible setups into account the extension will make no checks on the nature of the destination: could be any folder.
* `rootFolder` - optional absolute path to the WordPress plugin or theme to be symlinked root folder; will default to the Codeception root folder

### Copier

The `tad\WPBrowser\Extension\Copier` extension provides an automation to have specific files and folders copied to specified destination files and folders before the suites run.

While WordPress handles symbolic linking pretty well there are some cases, like themes and drop-ins, where there is a need for "real" files to be put in place.

One of such cases is, currently, one where [Docker](https://www.docker.com/get-started) is used to to host and serve the code under test: symbolically linked files cannot be bound inside a container and Docker containers will fail to start in this case.

The extension follows the standard Codeception extension activation and has one configuration parameter only:


```yaml
extensions:
    enabled:
        - tad\WPBrowser\Extension\Copier
    config:
        tad\WPBrowser\Extension\Copier:
            files:
                tests/_data/required-drop-in.php: /var/www/wordpress/wp-content/drop-in.php
                tests/_data/themes/dummy: /var/www/wordpress/wp-content/themes/dummy
                /Users/Me/Repos/required-plugin: /var/www/wordpress/wp-content/plugins/required-plugin.php
                /Users/Me/Repos/mu-plugin.php: ../../../../wp-content/mu-plugins/mu-plugin.php
```

The extension will handle absolute and relative paths for sources and destinations and will resolve relative paths from the project root folder.

When copying directories the extension will only create the destination folder and not the folder tree required; in the example configuration above the last entry specifies that a `mu-plugin.php` file should be copied to the `mu-plugins` folder: that `mu-plugins` folder must be there already.

#### Environments support

Being able to symlink a plugin or theme folder into a WordPress installation for testing purposes could make sense when trying to test, as an example, a plugin in a single site and in multi site environment.  

Codeception [supports environments](http://codeception.com/docs/07-AdvancedUsage#Environmentshttp://codeception.com/docs/07-AdvancedUsage#Environments) and the extension does as well specifying a destination for each.

As an example the `acceptance.suite.yml` file might be configured to support `single` and `multisite` environments:

```yaml
env:
    single:
        modules:
            config:
                WPBrowser:
                    url: 'http://wp.dev'
                WPDb:
                    dsn: 'mysql:host=127.0.0.1;dbname=wp'
    multisite:
        modules:
            config:
                WPBrowser:
                    url: 'http://mu.dev'
                WPDb:
                    dsn: 'mysql:host=127.0.0.1;dbname=mu'
```

In the `codeception.yml` file specifying a `destination` for each supported environment will tell the extension to symbolically link the plugin or theme file to different locations according to the current environment:

```yaml
extensions:
    enabled:
        - tad\WPBrowser\Extension\Symlinker
    config:
        tad\WPBrowser\Extension\Symlinker:
            mode: plugin
            destination:
                single: /var/www/wp/wp-content/plugins
                multisite: /var/www/mu/wp-content/plugins
```

If no destination is specified for the current environment the extension will fallback to the first specified one.  

A `default` destination can be specified to override this behaviour.

```yaml
extensions:
    enabled:
        - tad\WPBrowser\Extension\Symlinker
    config:
        tad\WPBrowser\Extension\Symlinker:
            mode: plugin
            destination:
                default: /var/www/default/wp-content/plugins
                single: /var/www/wp/wp-content/plugins
                multisite: /var/www/mu/wp-content/plugins
```

When running a suite specifying more than one environment like


```bash
codecept run acceptance --env foo,baz,multisite
```

Then the extension will use the first matched one, in the case above the `multisite` destination will be used.  

The `rootFolder` parameter too can be set to be environment-aware and it will follow the same logic as the destination:


```yaml
extensions:
    enabled:
        - tad\WPBrowser\Extension\Symlinker
    config:
        tad\WPBrowser\Extension\Symlinker:
            mode: plugin
            rootFolder:
                dev: /
                dist: /dist
                default: /
            destination:
                default: /var/www/dev/wp-content/plugins
                dev: /var/www/dev/wp-content/plugins
                dist: /var/www/dist/wp-content/plugins
```

When running a suite specifying more than one environment like

```bash
codecept run acceptance --env dist
```

Then the extension will symlink the files from `/dist` into the `/var/www/dist/wp-content/plugins` folder.

### Events

Due to some internal changes in Codeception `4.0`, the internal API (really a collection of low-level hacks on my part) that allowed `wp-browser` to dispatch, and listen for, events in the modules has been removed.

If you want to leverage [the event system wp-browser provides] with Codeception default events (e.g. `suite.init` or `test.before`), then you will need to use this extension.

You will **not** need this extension if you're not using Codeception version `4.0`.

The extension has no configuration and you will need to enable it in your Codeception **main** configuration file (e.g. `codeception.dist.yml`):  

```yaml
extensions:
    enabled:
        - tad\WPBrowser\Extension\Events
```

[4]: events-api.md
