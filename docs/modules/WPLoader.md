# WPLoader module
This module should be used in integration tests, see [levels of testing for more information](./../levels-of-testing.md), to bootstrap WordPress code in the context of the tests.  
Setting the `loadOnly` parameter to `true` the module can be additionally used in acceptance and functional tests to acccess WordPress code in the tests context.  
This module is a wrapper around the functionalities provided by [the WordPress PHPUnit Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), as such it provides the same method and facilities.  
The parameters provided to the module duplicate the ones used in the WordPress configuration file: the `WPLoader` module will **not** bootstrap WordPress using the `wp-config.php` file, it will define and use its own WordPress configuration built from the module parameters.

## Configuration
* `wpRootFolder` *required* The absolute, or relative to the project root folder, path to the root WordPress installation folder. The WordPress installation root folder is the one that contains the `wp-load.php` file.
* `dbName` *required* - The name of the database used by the WordPress installation, same as the `DB_NAME` constant.
* `dbHost` *required* - The host of the database used by the WordPress installation, same as the `DB_HOST` constant.
* `dbUser` *required* - The user of the database used by the WordPress installation, same as the `DB_USER` constant.
* `dbPassword` *required* - The password of the database used by the WordPress installation, same as `DB_PASSWORD` constant.
* `loadOnly` - defaults to `false`; whether to only load WordPress, without bootstrapping a fresh installation for tests or not. Read more in the ["Using WPLoader in acceptance and functional tests"](#using-wploader-in-acceptance-and-functional-tests) section. If this parameter is set to `true` the following parameters will not apply.
* `isolatedInstall` - defaults to `true`, whether to install and bootstrap the WordPress installation in a secondary PHP thread for thread safety or not. Maintained for back-compatibility purposes with wp-browser first versions: to get a replica of the bootstrap process used by [WordPress Core PHPUnit tests]() leave this to `true`.
* `wpDebug` - defaults to `true`, the value the `WP_DEBUG` constant will be set to.
* `multisite` - defaults to `false`, the value the `MULTISITE` constant will be set to.
* `dbCharset` - defaults to `utf8`, the value the `DB_CHARSET` constant will be set to.
* `dbCollate` - defaults to ` `, the value the `DB_COLLATE` constant will be set to.
* `tablePrefix` - defaults to `wptests_`, the value the `$table_prefix` variable will be set to.
* `domain` - defaults to `example.org`, the domain of the WordPress site to scaffold for the tests.
* `adminEmail` - defaults to `admin@example.org`, the email of the WordPress site to scaffold for the tests.
* `title` - defaults to `Test Blog`, the title of the WordPress site to scaffolded for the tests.
* `phpBinary` - defaults to `php`, the PHP binary the host machine will have to use to bootstrap and load the test WordPress installation.
* `language` - defaults to ` `, the language of the WordPress installation to scaffold.
* `configFile` - defaults to ` `, an additional configuration file to include **before** loading WordPress. Any instruction in this fill will run **before** any WordPress file is included.
* `pluginsFolder` - defaults to ` `, the relative path to the plugins folder from the `wpRootFolder` if different from the default one or the one defined by the `WP_PLUGIN_DIR` constant; if the `WP_PLUGIN_DIR` constant is defined in a config file (see the `configFile` parameter) this will be ignored.
* `plugins` - defaults to ` `; a list of plugins that should be loaded before any test case runs and after mu-plugins have been loaded; these should be defined in the `folder/plugin-file.php` format.
* `activatePlugins` - defaults to ` `, a list of plugins that will be activated before any test case runs and after WordPress is fully loaded and set up; these should be defined in the `folder/plugin-file.php` format; when the `multisite` option is set to `true` the plugins will be **network activated** during the installation.
* `bootstrapActions` - defaults to ` `, a list of actions or **static functions** that should be called after before any test case runs, after plugins have been loaded and activated; static functions should be defined in the YAML array format:
    ```yaml
    bootstrapActions:
        - action_one
        - action_two
        - [MyClass, myStaticMethod]
    ```
* `theme` - defaults to ` `, the theme that should be activated for the tests; if a string is passed then both `template` and `stylesheet` options will be set to the passed value; if an array is passed then the `template` and `stylesheet` will be set in that order:

    ```yaml
    theme: my-theme
    ```

    The theme will be set to `my-theme`.

    ```yaml
    theme: [ parent, child ]
    ```

    The `template` will be set to `parent`, the `stylesheet` will be set to `child`.

**A word of caution**: right now the only way to write tests able to take advantage of the suite is to use the `WP_UnitTestCase` test case class; while the module will load fine and will raise no problems `WP_UnitTestCase` will take care of handling the database as intended and using another test case class will almost certainly result in an error if the test case defines more than one test method.

## WPLoader to only bootstrap WordPress
If the need is to just bootstrap the WordPress installation in the context of the tests variable scope then the `WPLoader` module `loadOnly` parameter should be set to `true`; this could be the case for functional tests in need to access WordPress provided methods, functions and values.  
An example configuration for the module in this mode is this one:

```yaml
  modules:
      enabled:
          - WPLoader
      config:
          WPLoader:
              loadOnly: true 
              wpRootFolder: "/Users/User/www/wordpress"
              dbName: "wpress-tests"
              dbHost: "localhost"
              dbUser: "root"
              dbPassword: "root"
```

With reference to the table above the module will not take care of the test WordPress installation state before and after the tests, the installed and activated plugins, and theme.  
The module can be used in conjuction with a `WPDb` module to provide the tests with a WordPress installation suiting the tests at hand.
<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#activateplugins">activatePlugins</a>
		</li>
		<li>
			<a href="#bootstrapactions">bootstrapActions</a>
		</li>
		<li>
			<a href="#factory">factory</a>
		</li>
		<li>
			<a href="#loadplugins">loadPlugins</a>
		</li>
		<li>
			<a href="#switchtheme">switchTheme</a>
		</li>
	</ul>
</nav>

<h3>activatePlugins</h3>

<hr>


  

<h3>bootstrapActions</h3>

<hr>

<p>Calls a list of user-defined actions needed in tests.</p>
  

<h3>factory</h3>

<hr>

<p>Accessor method to get the object storing the factories for things. Example usage: $postId = $I-&gt;factory()-&gt;post-&gt;create();</p>
  

<h3>loadPlugins</h3>

<hr>

<p>Loads the plugins required by the test.</p>
  

<h3>switchTheme</h3>

<hr>


*This class extends \Codeception\Module*

<!--/doc-->
