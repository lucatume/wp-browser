wp-browser
==========

A WordPress specific set of extensions for Codeception.

The package includes a class extending Codeception PhpBrowser module that adds WordPress related assertions for `cest` and `cept` tests, an extension of Codeception own Db module meant to allow for more comfortable WordPress specific database handling and testing and a class taking care of installing and loading a WordPress installation relying on [WordPress automated testing suite](http://make.wordpress.org/core/handbook/automated-testing/).
While working on the module I've added some methods to the `Codeception\Module\Db` class to implement CRUDness into it; see below the `ExtendedDb` class.

## Installation
To install simply require the package in the `composer.json` file like

```json
  "require-dev":
    {
      "lucatume/wp-browser": "~1.11"
    }
```
    
and then use `composer update` to fetch the package.  
After that  follow the configuration instructions below.

## Modules
While the package name is the same as the first module added to it ("WPBrowser") the package will add more than one module to [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") to ease WordPress testing.  
Not every module will make sense or work in any suite or type of test case but here's an high level view:

* WPBrowser - a PHP based, JavaScript-less and headless browser for functional testing
* WPWebDriver - a Guzzle based, JavaScript capable web driver; to be used in conjunction with [a Selenium server](http://www.seleniumhq.org/download/), [PhantomJS](http://phantomjs.org/) or any real web browser for acceptance testing
* WPDb - an extension of the default codeception [Db module](http://codeception.com/docs/modules/Db) that will interact with a WordPress database to be used in functional testing
* WPLoader - will load and configure a **blank** WordPress installation to use as a base to set up fixtures and access WordPress defined functions and classes in integration tests; a wrapping of the WordPress [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework") based [test suite provided in the WordPress repository](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/).
* WPBootstrapper - will bootstrap an existing WordPress installation in the same variable scope of the calling function to have access to its methods.
* WPQueries - allows for assertments to be made on WordPress database access in integration tests.
* WPRequests - makes request to an existing WordPress installation **in a separate process** and offers an API to access WordPress services in functional tests.

### WPBrowser configuration
WPBrowser extends `PHPBrowser` module hence any parameter required and available to that module is required and available in `WPBrowser` as well.  
In the suite `.yml` configuration file add the module among the loaded ones

```yml
  modules:
      enabled:
          - WPBrowser
      config:
          WPBrowser:
              url: 'http://example.local'
              adminUsername: 'root'
              adminPassword: 'root'
              adminUrl: '/wp-core/wp-admin'
```

and configure `PHPBrowser` parameters and the additional ones available to the `WPBrowser` module:  

* `adminUsername` - the site administrator username (required)
* `adminPassword` - the site administrator login name (required)
* `adminUrl` - the relative to the `url` parameter path to the administration area of the site  (required)

### WPWebDriver configuration
WPWebDriver extends `WebDriver` module hence any parameter required and available to that module is required and available in `WPWebDriver` as well.
In the suite `.yml` configuration file add the module among the loaded ones

```yml
  modules:
      enabled:
          - WPWebDriver
      config:
          WPWebDriver:
              url: 'http://example.local'
              browser: phantomjs
              port: 4444
              window_size: '1024x768'
              adminUsername: 'root'
              adminPassword: 'root'
              adminUrl: '/wp-core/wp-admin'
```

and configure `WPWebDriver` parameters and the additional ones available to the `WPWebDriver` module:

* `adminUsername` - the site administrator username (required)
* `adminPassword` - the site administrator login name (required)
* `adminUrl` - the relative to the `url` parameter path to the administration area of the site  (required)

### WPDb configuration
The module extends the `Db` module hence any parameter required and available by the `Db module` is required and available in the `WPDb` module as well.  
In the suite `.yml` configuration file add the module among the loaded ones

```yml
  modules:
      enabled:
          - WPDb
      config:
          WPDb:
              dsn: 'mysql:host=localhost;dbname=testdb'
              user: 'root'
              password: ''
              dump: 'tests/_data/dump.sql'
              populate: true
              cleanup: true
              reconnect: true
              url: 'http://example.local'
              tablePrefix: 'wp_'
```

and configure `Db` parameters and the additional ones available to the `WPDb` module:  
    
* `url` - the site home url (required)
* `tablePrefix` - allows specifying the table prefix used in the installation, defaults to `wp_` (optional)

#### Dump file domain replacement
The SQL dump file will be loaded by the module during initialization **before** each test following the same limitations about size imposed by [Codeception Db module](http://codeception.com/docs/09-Data#db).  
The problem with WordPress database dumps is that the website URL address is harcoded in the database itself making dump sharing a serch and replace pain.  
The module will try to replace the domain written in the loaded SQL dump file on the fly to match the one specified in the `url` config parameter to allow dumps to work locally with no issues.

### WPLoader configuration
The module wraps the configuration, installation and loading of a working headless WordPress site for testing purposes.
An adaptation of [WordPress automated testing suite](http://make.wordpress.org/core/handbook/automated-testing/) the module exposes the suite hard-coded value as configuration parameters.  
Since this module takes charge of setting up and cleaning the database used for the tests point it to a database that does not contain sensible data!  
Also note that this module cannot be used together with WPDb or DB modules with the `cleanup` settings set to `true`.  
In the suite `.yml` configuration file add the module among the loaded ones

```yml
  modules:
      enabled:
          - WPLoader
      config:
          WPLoader:
              wpRootFolder: "/Users/User/www/wordpress"
              dbName: "wpress-tests"
              dbHost: "localhost"
              dbUser: "root"
              dbPassword: "root"
              wpDebug: true
              dbCharset: "utf8"
              dbCollate: ""
              tablePrefix: "wptests_"
              domain: "example.org"
              adminEmail: "admin@example.com"
              title: "Test Blog"
              phpBinary: "php"
              language: ""
              configFile: ""
			  plugins: ['hello.php', 'my-plugin/my-plugin.php']
			  activatePlugins: ['hello.php', 'my-plugin/my-plugin.php']
			  booststrapActions: ['my-first-action', 'my-second-action']
```

and configure it using the required parameters:

 * `wpRootFolder` - the absolute path to the root folder of the WordPress installation to use for testing, the `ABSPATH` global value.
 * `dbNAme` - the name of the database to use for the tests, will be trashed during tests so take care, will be the `DB_NAME` global.
 * `dbHost` - the host the database can be found at, will be the `DB_HOST` global.
 * `dbUser` - the database privileged user, should `GRANT ALL` on the database, will be the `DB_USER` global.
 * `dbPassword` - the password for the user, will be the `DB_PASSWORD` global.

 Optional parameters are available to the module to reproduce the original testing suite possibilities as closely as possible:

* `wpDebug` - bool, def. `true`, the `WP_DEBUG` global value.
* `multisite` - bool, def. `false`, if set to `true` will create a multisite instllation, the `WP_TESTS_MULTISITE` global value.
* `dbCharset` - string, def. `utf8`, the DB_CHARSET global value.
* `dbCollate` - string, def. ``, the DB_COLLATE global value.
* `tablePrefix` - string, def. `wptests_`, the `WP_TESTS_TABLE_PREFIX` value.
* `domain` - string, def. `example.org`, the root URL of the site, the `WP_TESTS_DOMAIN` global value.
* `adminEmail` - string, def. `admin@example.org`, the admin email, the `WP_TEST_EMAIL` global value.
* `title` - string, def. `Test Blog`, the blog title, the `WP_TESTS_TITLE` global value.
* `phpBinary` - string, def. `php`, the php bin command, the `WP_PHP_BINARY` global value.
* `language` - string, def. ` `, the installation language, the `WPLANG` global value.
* `configFile` - string or array, def. ` `, the path, or an array of paths, to custom config file(s) relative to the `wpRootFolder` folder, no leading slash needed; this is the place where custom `wp_tests_options` could be set.
* `pluginsFolder` - string, def. ` `, the relative path to the plugins folder from the `wpRootFolder` if different from the `wp-content/plugins` default one
* `plugins` - array, def. `['hello.php', 'my-plugin/my-plugin.php']`, a list of plugins that should be loaded before any test case runs and after mu-plugins have been loaded; these should be defined in the `folder/plugin-file.php` format.
* `activatePlugins` - array, def. `['hello.php', 'my-plugin/my-plugin.php']`, a list of plugins that will be activated before any test case runs and after WordPress is fully loaded and set up; these should be defined in the `folder/plugin-file.php` format.
* `bootstrapActions` - array, def. `['my-first-action', 'my-second-action']`, a list of actions that should be called after before any test case runs, after plugins have been loaded and activated.

**A word of caution**: right now the only way to write tests able to take advantage of the suite is to use the `WP_UnitTestCase` test case class; while the module will load fine and will raise no problems `WP_UnitTestCase` will take care of handling the database as intended and using another test case class will almost certainly result in an error if the test case defines more than one test method.

### WPBootstrapper configuration
The module will bootstrap a WordPress installation loading its `wp-load.php` file.   
The configuration will require one parameter only :

 * `wpRootFolder` - the absolute path to the root folder of the WordPress installation to use for testing, the `ABSPATH` global value.

### WPQueries configuration
This module requires no configuration.

### WPRequests configuration
The module will require the absolute path to an existing WordPress installation; the path should point to WordPress root folder, the one containing the `wp-load.php` file.

```yaml
modules:
    enabled:
        WPRequests:
            wpRootFolder: /var/www/wp
```

### wpcept command
The package will create a link to the `bin/wpcept` script file; that's an extension of Codeception own `codecept` CLI application to allow for a WordPress specific setup.

#### bootstrap
The CLI application adds the `bootstrap` command argument to allow for a quick WordPress testing environment setup replacing the default bootstrap configuration created by Codeception.

```sh
  wpcept bootstrap
```

The command will generate the "Unit", "Wpunit", "Functional" and "Acceptance" suites following the same pattern used by Codeception but with WordPress specific modules:

* Unit with `Asserts` and helper modules
* Wpunit with `WPLoader` and helper modules
* Functional with `Filesystem`, `WPDb`, `WPLoader` and helper modules
* Acceptance with `WPBrowser`, `WPDb` and helper modules

Please note that default Codeception suite bootstrapping is available using the `codecept bootstrap` command.
The "Wpunit" suite is meant to be a middle ground between the simple unit tests of classes that are able to mock any dependency and do not rely on any WordPress defined class, method or function and those that do.

#### bootstrap:pyramid
The `bootstrap:pyramid` command argument allows for a quick WordPress testing environment setup following the [test pyramid](http://martinfowler.com/bliki/TestPyramid.html) suite organization.  
The command

```sh
  wpcept bootstrap:pyramid
```

will generate the "UI", "Service", "Wpunit" and "Unit" suites and will take care of setting up default modules and their settings for each like:

* Unit with `Asserts` and `UnitHelper` modules
* Wpunit with `WPLoader` and helper modules
* Functional with `Filesystem`, `WPDb`, `WPLoader` and `FunctionalHelper` modules
* Acceptance with `WPBrowser`, `WPDb` and `AcceptanceHelper` modules

Please note that default Codeception suite bootstrapping is available using the `codecept bootstrap` command.
The "Wpunit" suite is meant to be a middle ground between the simple unit tests of classes that are able to mock any dependency and do not rely on any WordPress defined class, method or function and those that do.

#### generate:wpunit
Generates a test case extending the `\Codeception\TestCase\WPTestCase` class using the

```sh
  wpcept generate:wpunit suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPTestCase
{
    protected function setUp()
    {
      parent::setUp();
    }

    protected function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

#### generate:wprest
Generates a test case extending the `\Codeception\TestCase\WPRestApiTestCase` class using the

```sh
  wpcept generate:wprest suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPRestApiTestCase
{
    protected function setUp()
    {
      parent::setUp();
    }

    protected function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

#### generate:wpajax
Generates a test case extending the `\Codeception\TestCase\WPAjaxTestCase` class using the

```sh
  wpcept generate:wpajax suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPAjaxTestCase
{
    protected function setUp()
    {
      parent::setUp();
    }

    protected function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

#### generate:wpxmlrpc
Generates a test case extending the `\Codeception\TestCase\WPXMLRPCTestCase` class using the

```sh
  wpcept generate:wpxmlrpc suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPXMLRPCTestCase
{
    protected function setUp()
    {
      parent::setUp();
    }

    protected function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

#### generate:wpcanonical
Generates a test case extending the `\Codeception\TestCase\WPCanonicalTestCase` class using the

```sh
  wpcept generate:wpcanonical suite SomeClass
```

The command will generate a skeleton test case like


```php
<?php

class SomeClassTest extends \Codeception\TestCase\WPCanonicalTestCase
{
    protected function setUp()
    {
      parent::setUp();
    }

    protected function tearDown()
    {
      parent::tearDown();
    }

    // tests
    public function testMe()
    {
    }

}
```

Any other `codecept` option remains intact and available. 

#### generate:phpunitBootstrap
This command will generate the files required to run functional tests defined in test case classes extending the `WP_UnitTestCase` class.  
The method will read the `codeception.yml` file to point PHPUnit `phpunit.xml` file to the tests folder and set up a `phpunit-bootstrap.php` file in the tests folder.  
The command has the following arguments

`suites` - a comma separated list of suites the tests should run, def. `functional`
`suffix` - the suffix of test classes PHPUnit should run, def. `Test`
`vendor` - the path, relative to the project root folder, to the vendor folder, def. `vendor`

Each call to the command will re-generate the `phpunit.xml` and `tests/phpunit-bootstrap.php` files, changes made to the `phpunit` element attributes in the `phpunit.xml` file will be preserved across regenerations.

### Management commands
The package comes with some commands meant to make the management and sharing of a shared repository easier.
Some are wrappers around external commands (like `search-replace` and `setup`) or native to the WPBrowser package.  
All the commands share the `--save-config` option: when used in flag mode any **option** value specified in the command (so **no arguments**) will be saved in a `commands-config.yml` file in the root folder.  
As an example running:

```bash
wpcept db:snapshot issue3344 wp-tests --local-url=http://wp-tests.dev --dist-url=http://acme.tests.dev --host=192.54.0.1 --user=db --pass=db --save-config
```

will generate a  `command-config.yml` file like this:

```yaml
# tad\Codeception\Command configuration file.
# Each section should be the name of a supported command
# This file was auto-generated by the use of the `--save-config` option on one or more commands.
# But you can modify it by hand with some care.
db:snapshot:
    local-url: http://wp-tests.dev
    dist-url: http://acme.tests.dev
    host: 192.54.0.1
    user: db
    pass: db
```

that will allow to shorten the next invocation of the command considerably on the next run:

```bash
wpcept db:snapshot issue44566 wp-tests 
```

Multiple commands can and will write their own configuration in the `command-config.yml` file.  
It is possible to override saved configuration values specifying the option in the command:

```bash
wpcept db:snapshot issue22444 wp-tests --user=root --host=localhost
```

#### search-replace
This is merely a shimming of the `search-replace` command defined in [the `lucatume/codeception-setup-local` package](https://github.com/lucatume/codeception-setup-local "lucatume/codeception-setup-local · GitHub"); see package documentation for more information.

#### setup
This is merely a shimming of the `setup` command defined in [the `lucatume/codeception-setup-local` package](https://github.com/lucatume/codeception-setup-local "lucatume/codeception-setup-local · GitHub"); see package documentation for more information.

#### db:snapshot
The command allows developers to take a snapshot of a database state to be used to share database-based fixtures in a team.  
The command takes the following arguments and options:

* `snapshot` - the first argument is the name of the snapshot to be taken; e.g. `issue4455` or `ticket-ab-f00-34`; required
* `name` - the second argument is the name of the database that should be exported; e.g. `wp` or `test-db`; required
* `--host` - this options allows defining the database host; defaults to `localhost`; optional
* `--user` - this options allows defining the database user; defaults to `root`; optional
* `--pass` - this options allows defining the database password; defaults to `root`; optional
* `--dump-file` - this options allows defining the destination file for the database dump (an absolute path); defaults to `<snapshot>.sql` in Codeception data folder; optional
* `--dist-dump-file` - this options allows defining the destination file for the distribution database dump (an absolute path); defaults to `<snapshot>.dist.sql` in Codeception data folder; optional
* `--skip-tables` - this options allows defining any table that shuould not be dumped (a comma separated list); e.g. `wp_posts,wp_users`; defaults to none; optional
* `--local-url` - this options allows defining the local setup url that is hardcoded in the local version of the database by WordPress; e.g. `http://wp.dev`; defaults to `http://local.dev`; optional but probably needed
* `--dist-url` - this options allows defining the distribution setup url that will be hardcoded in the distribution version of the database dump; e.g. `http://wptest.dev`; defaults to `http://dist.dev`; optional but probably needed

A typical flow using the command would be:

* a developer sets up a local version of a starting database state for a test or a series of tests
* the developer creates a local (to be used in local tests) and distribution (to be shared with other team members) dump of his/her local database using:

  ```bash
  wpcept db:snapshot issue3344 wp-tests --local-url=http://wp-tests.dev --dist-url=http://acme.tests.dev
  ```
* any other developer on the team can use the `search-replace` command to localize the distribution version of the database dump to suite his/her setup:
  
  ```bash
  wpcept search-replace http://acme.tests.dev http://local.dev ./tests/_data/issue3344.dist.sql ./tests/_data/issue3344.sql
  ```

### ExtendedDb configuration
The module has the same configuration as the `Db` one and hence will not require any additional parameter beside those required/available to the `Db` module.
In the suite `.yml` configuration file add the module among the loaded ones

```yml
  modules:
      enabled:
          - ExtendedDb
      config:
          ExtendedDb:
              dsn: 'mysql:host=localhost;dbname=testdb'
              user: 'root'
              password: ''
              dump: 'tests/_data/dump.sql'
              populate: true
              cleanup: true
```

and configure `Db` parameters as usual.

## Methods

### WPBrowser module
The module adds methods that can be used in `.cest` and `.cept` methods using the same `$I->doSomething` syntax used in PhpBrowser.  
Included methods are:

```php
  // login as administrator using username and password
  public function loginAsAdmin();

  // login as user
  public function loginAs($username, $password);

  // go the plugins page
  public function amOnPluginsPage();

  // activate a plugin from the plugin page
  public function activatePlugin($pluginSlug);

  // deactivate a plugin from the plugin page
  public function deactivatePlugin($pluginSlug);

  // check that a plugin is installed and deactivated from the plugin page
  public function seePluginDeactivated($pluginSlug);

  // check that a plugin is installed and activated from the plugin page
  public function seePluginActivated($pluginSlug);

  // check that a plugin is installed from the plugin page
  public function seePluginInstalled($pluginSlug);

  // check that a plugin is not installed from the plugin page
  public function doNotSeePluginInstalled($pluginSlug);

  // check for an error admin notice
  public function seeErrorMessage($classes = '');

  // check for an updated admin notice
  public function seeMessage($classes = '');

  // check that the current page is a wp_die generated one
  public function seeWpDiePage();

  // grab all cookies whose name matches a pattern
  public function grabCookiesWithPattern($pattern);

  // grab WordPress test cookie
  public function grabWordPressTestCookie($pattern = null);

  // grab WordPress login cookie
  public function grabWordPressLoginCookie($pattern = null);

  // grab WordPrss auth cookie
  public function grabWordPressAuthCookie($pattern = null);
```

Methods like `seePlugin...` require the use of the `amOnPluginsPage` method before their invocation to navigate PhpBrowser to the right folder.

### WPDb module
The module extends `Codeception\Module\Db` and will hence act as a drop-in replacement for it. It adds an optional `tablePrefix` configuration parameter, defaulting to `wp`, and will require the same parameters as the original module.  
The verbs used by the `Db` module are honored so `dontHave` removes an entry, `have` adds an entry, `dontSee` checks an entry is not in the database, `see` checks an entry is in the database, `grab` gets a value from the database or the module.  
When dealing with multisite installations then the `useBlog` and `useMainBlog` methods can be used to perform any following database operation on the specified site tables if applicable; some tables are unique in a WordPress installation (e.g. `users`) and the command will not mess with it.  
The module is meant to be a WordPress specific extension of the `Db` module and will hence decline the `have` and `see` methods for each WordPress table; here's a current list of all the defined methods:

* dontHaveBlogInDatabase
* dontHaveCommentInDatabase
* dontHaveCommentMetaInDatabase
* dontHaveLinkInDatabase
* dontHaveOptionInDatabase
* dontHavePostInDatabase
* dontHavePostMetaInDatabase
* dontHaveSiteOptionInDatabase
* dontHaveSiteTransientInDatabase
* dontHaveTermInDatabase
* dontHaveTermMetaInDatabase
* dontHaveTermRelationshipInDatabase
* dontHaveTermTaxonomyInDatabase
* dontHaveTransientInDatabase
* dontHaveUserInDatabase
* dontHaveUserMetaInDatabase
* dontSeeBlogInDatabase
* dontSeeCommentInDatabase
* dontSeeCommentMetaInDatabase
* dontSeeLinkInDatabase
* dontSeeOptionInDatabase
* dontSeePageInDatabase
* dontSeePostInDatabase
* dontSeePostMetaInDatabase
* dontSeeTermInDatabase
* dontSeeTermMetaInDatabase
* dontSeeTermTaxonomyInDatabase
* dontSeeUserInDatabase
* dontSeeUserMetaInDatabase
* getSiteDomain
* grabAllFromDatabase
* grabBlogsTableName
* grabBlogVersionsTableName
* grabCommentmetaTableName
* grabCommentsTableName
* grabLatestEntryByFromDatabase
* grabLinksTableName
* grabOptionFromDatabase
* grabPostMetaTableName
* grabPostsTableName
* grabPrefixedTableNameFor
* grabRegistrationLogTableName
* grabSignupsTableName
* grabSiteMetaTableName
* grabSiteOptionFromDatabase
* grabSiteTableName
* grabSiteTransientFromDatabase
* grabSiteUrl
* grabTermIdFromDatabase
* grabTermMetaTableName
* grabTermRelationshipsTableName
* grabTermsTableName
* grabTermTaxonomyIdFromDatabase
* grabTermTaxonomyTableName
* grabUserIdFromDatabase
* grabUserMetaFromDatabase
* grabUsermetaTableName
* haveBlogInDatabase
* haveCommentInDatabase
* haveCommentMetaInDatabase
* haveLinkInDatabase
* haveManyBlogsInDatabase
* haveManyCommentsInDatabase
* haveManyLinksInDatabase
* haveManyPostsInDatabase
* haveManyTermsInDatabase
* haveManyUsersInDatabase
* haveMultisiteInDatabase
* haveOptionInDatabase
* havePageInDatabase
* havePostInDatabase
* havePostmetaInDatabase
* haveSiteOptionInDatabase
* haveSiteTransientInDatabase
* haveTermInDatabase
* haveTermMetaInDatabase
* haveTermRelationshipInDatabase
* haveTransientInDatabase
* haveUserCapabilitiesInDatabase
* haveUserInDatabase
* haveUserLevelsInDatabase
* haveUserMetaInDatabase
* seeBlogInDatabase
* seeCommentInDatabase
* seeCommentMetaInDatabase
* seeLinkInDatabase
* seeOptionInDatabase
* seePageInDatabase
* seePostInDatabase
* seePostMetaInDatabase
* seePostWithTermInDatabase
* seeSiteOptionInDatabase
* seeSiteSiteTransientInDatabase
* seeTableInDatabase
* seeTermInDatabase
* seeTermMetaInDatabase
* seeTermTaxonomyInDatabase
* seeUserInDatabase
* seeUserMetaInDatabase
* useBlog
* useMainBlog
* useTheme
* haveMenuInDatabase
* haveMenuItemInDatabase
* seeTermRelationshipInDatabase

See source code for more detail.

#### Handlebar templates while having many
When using one of the `haveMany` methods (`haveManyBlogsInDatabase`, `haveManyCommentsInDatabase`, `haveManyLinksInDatabase`, `haveManyPostsInDatabase`, `haveManyTermsInDatabase`, `haveManyUsersInDatabase`) it's possible to tap into [Handlebars PHP](https://github.com/XaminProject/handlebars.php "XaminProject/handlebars.php · GitHub") templating capabilities to set up complex testing data.  
When specifying a string value overriding the default ones the simplest replacement is the one where the `{{n}}` placeholder is replaced with the index of the object instance in the series:
```php
$I->haveManyPostsInDatabase(3, ['post_title' => 'Post {{n}} title']);
```

will insert 3 posts in the database titled "Post 0 title", "Post 1 title" and "Post 2 title".  
The string value will be used as a template and the `n` parameter will always be passed to the template; should additional template data be needed then each `haveMany` method allows for an additional `template_data` entry in the `overrides` array.

```php
$overrides = [
	'post_title' => 'Post {{n}} title {{some_string}}', 
	'template_data' => ['some_string' => 'foo']
	];
$I->haveManyPostsInDatabase(3, $overrides);
```

will insert 3 posts in the database titled "Post 0 title foo", "Post 1 title foo" and "Post 2 title foo".
To extend the flexibility template data allows for functions and closures to be specified: each will be called passing the index as an argument.

```php
$numeral = function($n){
	$numerals = ['First', 'Second', 'Third'];
	return $numerals[$n];
	};
$overrides = [
	'post_title' => '{{numeral}} post title',
	'template_data' => ['numeral' => $numeral]
	];
$I->haveManyPostsInDatabase(3, $overrides);
```

will insert 3 posts in the database titled "First post title", "Second post title" and "Third post title".
All of default [Handlebars PHP](https://github.com/XaminProject/handlebars.php "XaminProject/handlebars.php · GitHub") helpers are available to use in templates; the code below is an example:

```php
$numeral = function($n){
	$numerals = ['First', 'Second', 'Third'];
	return $numerals[$n];
	};
$overrides = [
	'post_title' => '{{#if n}}{{numeral}} post title{{/if}}{{#unless n}}I have index 0{{/unless}}',
	'template_data' => ['numeral' => $numeral]
	];
$I->haveManyPostsInDatabase(3, $overrides);
```
will insert 3 posts in the database titled "I have index 0", "Second post title" and "Third post title".

### ExtendedDb module
The module is an extension of the `Codeception\Module\Db` class implementing some methods to allow for more CRUD complete operations on the database with the methods

```php
  public function dontHaveInDatabase($table, array $criteria);

  public function haveOrUpdateInDatabase($table, array $data)
```

### WPBootstrapper
The module adds some *sugar* methods, beside allowing for the call of any WordPress defined function or class method, to speed up teh writing of test methods:

* `setPermalinkStructureAndFlush($permalinkStructure = '/%postname%/', $hardFlush = true)` - sets the permalink structure to the specified value and flushes the rewrite rules.
* `loadWpComponent($component)` - includes the file(s) required to access some functions and classes WordPress would not load by default in a bootstrap; currently supported
  * `plugins` - includes the `wp-admin/includes/plugin.php` file to access functions like `activate_plugin` and `deactivate_plugins`.

### WPQueries
The module assertion methods can be accessed including it in the suite configuration file.  
When writing tests the module can be accessed using the `getModule` method.  
In any test case class extending the base `Codeception\TestCase\Test` class the module can be accessed like this:

```php
class QueriesTest extends Codeception\TestCase\Test{

  public function test_insertion_queries(){
    wp_insert_post(['post_type' => 'page', 'post_title' => 'Some title']);

    $queries = $this->getModule('WPQueries');
    $queries->assertQueries();
  }

}
```

In `cept` or `cest` format tests the module can be accessed in a similar way:

```php
$I = new FunctionalTester($scenario);
$I->amOnPage('/');
$I->click('Create random post');

$queries = $I->getModule('WPQueries');

$queries->assertQueries();
```

The module defines the following assertion methods, see code doc blocks documentation for the details:

* assertQueries
* assertNotQueries
* assertCountQueries 
* assertQueriesByStatement
* assertQueriesByMethod
* assertNotQueriesByStatement
* assertQueriesCountByStatement
* assertNotQueriesByMethod
* assertQueriesCountByMethod
* assertQueriesByFunction
* assertNotQueriesByFunction
* assertQueriesCountByFunction
* assertQueriesByStatementAndMethod
* assertNotQueriesByStatementAndMethod
* assertQueriesCountByStatementAndMethod
* assertQueriesByStatementAndFunction
* assertNotQueriesByStatementAndFunction
* assertQueriesCountByStatementAndFunction
* assertQueriesByAction
* assertNotQueriesByAction
* assertQueriesCountByAction
* assertQueriesByStatementAndAction
* assertNotQueriesByStatementAndAction
* assertQueriesCountByStatementAndAction
* assertQueriesByFilter
* assertNotQueriesByFilter
* assertQueriesCountByFilter
* assertQueriesByStatementAndFilter
* assertNotQueriesByStatementAndFilter
* assertQueriesCountByStatementAndFilter

**Note**: when used in a `WPTestCase` exending class the assertion methods will exclude queries made during `WPTestCase::setUp`, `WPTestCase::tearDown` and factory methods!  
This means that the `test_queries` test method below will fail as no queries have been made by methods or that are not part of `setUp`, `tearDown` or factories:

```php
class QueriesTest extends Codeception\TestCase\WPTestCase {
  public fuction test_queries(){
    
    $this->factory()->posts->create();

    // this will fail!
    $this->assertQueries();
  }
}
```

### WPRequests module
This module is meant to be used in functional tests to access those services WordPress is not exposing in independent modules.  
The current implementation of the module offers just one methods:

* `public string createNonce(string $action, array $credentials )` to generate a nonce for the specified action and user; the `$credentials` array is usually the output of the `loginAs` or `loginAsAdmin` methods defined by the `WPBrowser` or `WPWebDriver` modules.

```php
class RestPostInsertionCest
{
    public function test_post_insertion(FunctionalTester $I)
    {
        $I->sendPOST('/create-post',[
            'nonce' => $I->createNonce('wp_rest', $I->loginAsAdmin()),
            'title' => 'Some title',
            'content'  => 'Some content'
        ]);
        
        $I->seePostInDatabase(['post_title' => 'Some title', 'post_content' => 'Some content']);
    }
}
```

**Note**: the module will bootstrap the WordPress installation for each request! Calling the `createNonce` method 5 times will bootstrap WordPress 5 times with a huge impact on test timings; taking this into account try to cache re-usable results when possible.  

## Extensions
The package contains an additional extension to facilitate testers' life.

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
```

The arguments are:

* `mode` - can be `plugin` or `theme` and indicates whether the current Codeception root folder being symlinked is a plugin or a theme one
* `destination` - the absolute path to the WordPress local installation plugins or themes folder; to take the neverending variety of possible setups into account the extension will make no checks on the nature of the destination: could be any folder.

#### Environments support
Being able to symlink a plugin or theme folder into a WordPress installation for testing purposes could make sense when trying to test, as an example, a plugin in a single site and in multi site environment.  
Codeception [supports environments](http://codeception.com/docs/07-AdvancedUsage#Environmentshttp://codeception.com/docs/07-AdvancedUsage#Environments) and the extension does as well specifyin a destination for each.  
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
