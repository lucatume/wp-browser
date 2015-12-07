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
      "lucatume/wp-browser": "master@dev"
    }
```
    
and then use `composer update` to fetch the package.  
After that  follow the configuration instructions below.

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
              url: 'http://example.local'
              tablePrefix: 'wp_'
              checkExistence: true
              update: true
```

and configure `Db` parameters and the additional ones available to the `WPDb` module:  
    
* `url` - the site home url (required)
* `tablePrefix` - allows specifying the table prefix used in the installation, defaults to "wp_" (optional)
* `checkExistence` - enables some low level AI on the module side to insert needed elements in the database, e.g. will add a term and post before adding a relation between them; defaults to `false` (optional)
* `update` - will try updating the database on duplicate entries; defaults to `true` (optional)

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

### wpcept command
The package will create a link to the `bin/wpcept` script file; that's an extension of Codeception own `codecept` CLI application to allow for a WordPress specific setup.

#### bootstrap
The CLI application adds the `bootstrap` command argument to allow for a quick WordPress testing environment setup replacing the default bootstrap configuration created by Codeception.

```sh
  wpcept bootstrap
```

The command will generate the "Unit", "Functional" and "Acceptance" suites following the same pattern used by Codeception but with WordPress specific modules:

* Unit with `Asserts` and `UnitHelper` modules
* Functional with `Filesystem`, `WPDb`, `WPLoader` and `FunctionalHelper` modules
* Acceptance with `WPBrowser`, `WPDb` and `AcceptanceHelper` modules

Please note that defautl Codeception suite bootstrapping is available using the `codecept bootstrap` command.

#### bootstrap:pyramid
The `bootstrap:pyramid` command argument allows for a quick WordPress testing environment setup following the [test pyramid](http://martinfowler.com/bliki/TestPyramid.html) suite organization.  
The command

```sh
  wpcept bootstrap:pyramid
```

will generate the "UI", "Service" and "Unit" suites and will take care of setting up default modules and their settings for each like:

* Unit with `Asserts` and `UnitHelper` modules
* Functional with `Filesystem`, `WPDb`, `WPLoader` and `FunctionalHelper` modules
* Acceptance with `WPBrowser`, `WPDb` and `AcceptanceHelper` modules

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
* haveCommentInDatabase
* haveCommentMetaInDatabase
* haveLinkInDatabase
* haveLinkWithTermInDatabase
* haveManyCommentsInDatabase
* haveManyLinksInDatabase
* haveManyPostsInDatabase
* haveManyTermsInDatabase
* haveManyUsersInDatabase
* haveMultisite
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

See source for more method details.

### ExtendedDb module
The module is an extension of the `Codeception\Module\Db` class implementing some methods to allow for more CRUD complete operations on the database with the methods

```php
  public function dontHaveInDatabase($table, array $criteria);

  public function haveOrUpdateInDatabase($table, array $data)
```
