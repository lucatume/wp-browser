# WPLoader module
This module should be used in integration tests, see [levels of testing for more information](./../levels-of-testing.md), to bootstrap WordPress code in the context of the tests.  
Setting the `loadOnly` parameter to `true` the module can be additionally used in acceptance and functional tests to acccess WordPress code in the tests context.  
This module is a wrapper around the functionalities provided by [the WordPress PHPUnit Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), as such it provides the same method and facilities.  
The parameters provided to the module duplicate the ones used in the WordPress configuration file: the `WPLoader` module will **not** bootstrap WordPress using the `wp-config.php` file, it will define and use its own WordPress configuration built from the module parameters.

## Everything happens in a transaction
When used to bootstrap and install WordPress (`loadOnly: false`) exactly as the [the WordPress PHPUnit Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/) it is based on, this module will operate any change to the database in a transaction.  
This means that, in the context of integration tests, the result of any write or delete operation done during the tests will be rolled back at the end of each test method; this is done for a number of reasons like performance and tests independence.  
Inspection of the database during tests, e.g. stopping execution using XDebug, **will not show any change in the database**.
Keep this in mind while trying to debug integration tests using the `WPLoader` module.  
When configured to only load WordPress (`loadOnly: true`) then any database operation will be committed and written to the database.

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
* `dbCollate` - defaults to an empty string, the value the `DB_COLLATE` constant will be set to.
* `tablePrefix` - defaults to `wptests_`, the value the `$table_prefix` variable will be set to.
* `domain` - defaults to `example.org`, the domain of the WordPress site to scaffold for the tests.
* `adminEmail` - defaults to `admin@example.org`, the email of the WordPress site to scaffold for the tests.
* `title` - defaults to `Test Blog`, the title of the WordPress site to scaffolded for the tests.
* `phpBinary` - defaults to `php`, the PHP binary the host machine will have to use to bootstrap and load the test WordPress installation.
* `language` - defaults to an empty string, the language of the WordPress installation to scaffold.
* `configFile` - defaults to an empty string, an additional configuration file to include **before** loading WordPress. Any instruction in this fill will run **before** any WordPress file is included.
* `pluginsFolder` - defaults to an empty string; the relative path to the plugins folder from the `wpRootFolder` if different from the default one or the one defined by the `WP_PLUGIN_DIR` constant; if the `WP_PLUGIN_DIR` constant is defined in a config file (see the `configFile` parameter) this will be ignored.
* `plugins` - defaults to an empty string; a list of plugins that should be loaded before any test case runs and after mu-plugins have been loaded; these should be defined in the `folder/plugin-file.php` format.
* `activatePlugins` - defaults to an empty string, a list of plugins that will be activated before any test case runs and after WordPress is fully loaded and set up; these should be defined in the `folder/plugin-file.php` format; when the `multisite` option is set to `true` the plugins will be **network activated** during the installation.
* `bootstrapActions` - defaults to an empty string, a list of actions or **static functions** that should be called after before any test case runs, after plugins have been loaded and activated; static functions should be defined in the YAML array format:
    ```yaml
    bootstrapActions:
        - action_one
        - action_two
        - [MyClass, myStaticMethod]
    ```
* `theme` - defaults to an empty string, the theme that should be activated for the tests; if a string is passed then both `template` and `stylesheet` options will be set to the passed value; if an array is passed then the `template` and `stylesheet` will be set in that order:

    ```yaml
    theme: my-theme
    ```

    The theme will be set to `my-theme`.

    ```yaml
    theme: [ parent, child ]
    ```

    The `template` will be set to `parent`, the `stylesheet` will be set to `child`.

**A word of caution**: right now the only way to write tests able to take advantage of the suite is to use the `WP_UnitTestCase` test case class; while the module will load fine and will raise no problems `WP_UnitTestCase` will take care of handling the database as intended and using another test case class will almost certainly result in an error if the test case defines more than one test method.

### Example configuration
```yml
  modules:
      enabled:
          - WPLoader
      config:
          WPLoader:
              multisite: false
              wpRootFolder: "/Users/luca/www/wordpress"
              dbName: "wordpresss_tests"
              dbHost: "localhost"
              dbUser: "root"
              dbPassword: "password"
              isolatedInstall: true
              tablePrefix: "wptests_"
              domain: "wordrpess.localhost"
              adminEmail: "admin@wordpress.localhost"
              title: "Test Blog"
              theme: my-theme
              plugins: ['hello.php', 'my-plugin/my-plugin.php']
              activatePlugins: ['hello.php', 'my-plugin/my-plugin.php']
```

## Usage in integration or "WordPress unit" tests

The most common use of this module is to run integration, or "WordPress unit" tests (see [levels of testing for more information](./../levels-of-testing.md)).  

As a first step generate a `WPTestCase` using Codeception command-line utility (see [the commands provided by wp-browser](./../commands.md)):

```bash
codecept generate:wpunit my_suite "Acme\User"
```

Codeception will generate the `tests/my_suite/Acme/UserTest.php` class.
The class extends the `Codeception\TestCase\WPTestCase` class provided by wp-browser; this looks like a normal PHPUnit test case but has some perks due to it's _mixed breed_ nature.  
Understanding them might help you work with it:

* WordPress is installed and configured for the tests **before** the test case is loaded; WordPress defined functions and classes (and those of the plugins and themes loaded with it) will be available in the `setUpBeforeClass` method. 
* WordPress is **not** loaded when PHPUnit will call the [data provider methods](https://phpunit.readthedocs.io/en/8.2/writing-tests-for-phpunit.html#writing-tests-for-phpunit-data-providers); this means the `post_provider` method wil generate a function not found exception when the test case runs as the WordPress defined methods are not loaded yet:
	```php
	public function post_provider(){
			// `wp_insert_post` is loaded with WordPress and WordPress has not been loaded yet!
			return [
					[wp_insert_post(['post_title' => 'Test', 'post_status' => 'publish'])]
			];
	}

	public funcion test_posts($post_id){
			$this->assertInstanceOf(WP_Post::class, get_post($post_id));
	}
	```
* WordPress is reset to an initial known state before each test runs; the database transaction is rolled back to wipe any data and tables you might have manipulated in the tests, the global space is cleaned. See [Everything happens in a transaction](#everything-happens-in-a-transaction).  
* This is a [Codeception Unit test](https://codeception.com/docs/05-UnitTests), as such it does provide access to the `$this->tester` property to access the methods defined in other modules loaded in the suite and to [Codeception test doubles](https://codeception.com/docs/05-UnitTests#Test-Doubles)
* This is a [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework") test case too; there are way too many testing functions to cover to report them here but, to highlight a few: [mocking with Prophecy](https://phpunit.readthedocs.io/en/8.2/test-doubles.html) and the wealth of [PHPUnit assertion methods](https://phpunit.readthedocs.io/en/8.2/writing-tests-for-phpunit.html).
* This is kind of a WordPress Core suite test case; as such it provides access to its functions and to the often-overlooked `static::factory()` method; in this instance too there are too many methods to list them all but it's worth noting how easy it is to set up test fixtures with the factory:
	```php
	public function test_post_creation(){
			$random_post_id = static::factory()->post->create();

			$this->assertInstanceOf(WP_Post::class, get_post($random_post_id));
	}
	```
* The `factory` property can be accessed on the `tester` property too and will work the same way as if called using `static::factory()`:
    ```php
	public function test_post_creation(){
			$random_post_id = $this->tester->factory()->post->create();

			$this->assertInstanceOf(WP_Post::class, get_post($random_post_id));
	}
    ```

## WPLoader to only bootstrap WordPress
If the need is to just bootstrap the WordPress installation in the context of the tests variable scope then the `WPLoader` module `loadOnly` parameter should be set to `true`; this could be the case for functional tests in need to access WordPress provided methods, functions and values.  
An example configuration for the module in this mode is this one:

```yaml
  modules:
      enabled:
          - WPDb # BEFORE the WPLoader one!
          - WPLoader # AFTER the WPDb one!
      config:
          WPDb:
              dsn: 'mysql:host=localhost;dbname=wordpress'
              user: 'root'
              password: 'password'
              dump: 'tests/_data/dump.sql'
              populate: true
              cleanup: true
              waitlock: 10
              url: 'http://wordpress.localhost'
              urlReplacement: true
              tablePrefix: 'wp_'
          WPLoader:
              loadOnly: true 
              wpRootFolder: "/Users/User/www/wordpress"
              dbName: "wpress-tests"
              dbHost: "localhost"
              dbUser: "root"
              dbPassword: "root"
```

With reference to the table above the module will not take care of the test WordPress installation state before and after the tests, the installed and activated plugins, and theme.  
The module can be used in conjuction with a `WPDb` module to provide the tests with a WordPress installation suiting the tests at hand; when doing so please take care to list, in the suite configuration file `modules` section (see example above) the `WPDb` module **before** the `WPLoader` one.  
Codeception will initialize the modules **in the same order they are listed in the modules section of the suite configuration file** and the WPLoader module **needs** the database to be populated by the `WPDb` module before it runs!
As an example this is a correct suite configuration:
```yaml
modules:
  enabled:
      - WPDb # this before...
      - WPLoader # ...this one.
  config:
      WPDb:
        # ...
      WPLoader:
        loadOnly: true
        # ... 
```
<!--doc-->


## Public API
<nav>
	<ul>
		<li>
			<a href="#factory">factory</a>
		</li>
	</ul>
</nav>

<h3>factory</h3>

<hr>

<p>Accessor method to get the object storing the factories for things. This methods gives access to the same factories provided by the <a href="https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/">Core test suite</a>.</p>
<pre><code class="language-php">    $postId = $I-&gt;factory()-&gt;post-&gt;create();
    $userId = $I-&gt;factory()-&gt;user-&gt;create(['role' =&gt; 'administrator']);</code></pre>


*This class extends \Codeception\Module*

<!--/doc-->
