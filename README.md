wp-browser
==========

A WordPress specific set of extensions for Codeception.

The package includes a class extending Codeception PhpBrowser module that adds WordPress related assertions for <code>cest</code> and <code>cept</code> tests, an extension of Codeception own Db module meant to allow for more comfortable WordPress specific database handling and testing and a class taking care of installing and loading a WordPress installation relying on [WordPress automated testing suite](http://make.wordpress.org/core/handbook/automated-testing/).
While working on the module I've added some methods to the <code>Codeception\Module\Db</code> class to implement CRUDness into it; see below the <code>ExtendedDb</code> class.

## Installation
To install simply require the package in the <code>composer.json</code> file like

  "require-dev":
    {
      "lucatume/wp-browser": "master@dev"
    }
    
and then use <code>composer update</code> to fetch the package.  
After that  follow the configuration instructions below.

### WPBrowser configuration
WPBrowser extends <code>PHPBrowser</code> module hence any parameter required and available to that module is required and available in <code>WPBrowser</code> as well.  
In the suite <code>.yml</code> configuration file add the module among the loaded ones

    modules:
        enabled:
            - WPBrowser
        config:
            WPBrowser:
                url: 'http://example.local'
                adminUsername: 'root'
                adminPassword: 'root'
                adminUrl: '/wp-core/wp-admin'

and configure <code>PHPBrowser</code> parameters and the additional ones available to the <code>WPBrowser</code> module:  

* <code>adminUsername</code> - the site administrator username (required)
* <code>adminPassword</code> - the site administrator login name (required)
* <code>adminUrl</code> - the relative to the <code>url</code> parameter path to the administration area of the site  (required)

### WPDb configuration
The module extends the <code>Db</code> module hence any parameter required and available by the <code>Db module</code> is required and available in the <code>WPDb</code> module as well.  
In the suite <code>.yml</code> configuration file add the module among the loaded ones

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
                cleanup: false
                url: 'http://example.local'
                tablePrefix: 'wp_'
                checkExistence: true
                update: true

and configure <code>Db</code> parameters and the additional ones available to the <code>WPDb</code> module:  
    
* <code>url</code> - the site home url (required)
* <code>tablePrefix</code> - allows specifying the table prefix used in the installation, defaults to "wp_" (optional)
* <code>checkExistence</code> - enables some low level AI on the module side to insert needed elements in the database, e.g. will add a term and post before adding a relation between them; defaults to <code>false</code> (optional)
* <code>update</code> - will try updating the database on duplicate entries; defaults to <code>true</code> (optional)

### WPLoader configuration
The module wraps the configuration, installation and loading of a working headless WordPress site for testing purposes.
An adaptation of [WordPress automated testing suite](http://make.wordpress.org/core/handbook/automated-testing/) the module exposes the suite hard-coded value as configuration parameters.
In the suite <code>.yml</code> configuration file add the module among the loaded ones

    modules:
        enabled:
            - WPLoader
        config:
            WPLoader:
                wpRootFolder: "/Users/User/www/wordpress"
                dbNAme: "wpress-tests"
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
* `language` - string, def. ``, the installation language, the `WPLANG` global value.

**A word of caution**: right now the only way to write tests able to take advantage of the suite is to use the `WP_UnitTestCase` test case class; while the module will load fine and will raise no problems `WP_UnitTestCase` will take care of handling the database as intended and using another test case class will almost certainly result in an error if the test case defines more than one test method.

The package will create a link to the `bin/wpcept` script file; that's an extension of Codeception own `codecept` CLI command that adds the possibility to generate `WP_UnitTestCase` classes using the

    wpcept generate:wpunit suite File

any other `codecept` option remains intact and available. The command will generate a skeleton test case like

    <?php

    class SomeMoreTest extends \WP_UnitTestCase
    {
        protected function setUp()
        {
        }

        protected function tearDown()
        {
        }

        // tests
        public function testMe()
        {
        }

    }

### ExtendedDb configuration
The module has the same configuration as the <code>Db</code> one and hence will not require any additional parameter beside those required/available to the <code>Db</code> module.
In the suite <code>.yml</code> configuration file add the module among the loaded ones

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
                cleanup: false

and configure <code>Db</code> parameters as usual.

## Methods

### WPBrowser module
The module adds methods that can be used in <code>.cest</code> and <code>.cept</code> methods using the same <code>$I->doSomething</code> syntax used in PhpBrowser.  
Included methods are:
    
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

Methods like <code>seePlugin...</code> require the use of the <code>amOnPluginsPage</code> method before their invocation to navigate PhpBrowser to the right folder.

### WPDb module
The module extends <code>Codeception\Module\Db</code> and will hence act as a drop-in replacement for it. It adds an optional <code>tablePrefix</code> configuration parameter, defaulting to <code>wp</code>, and will require the same parameters as the original module.  
The module is meant to be a WordPress specific extension of the <code>Db</code> module and will hence decline the <code>have</code> and <code>see</code> methods for each WordPress table. As an example the methods for the <code>options</code> table are

    public function haveOptionInDatabase($option_name, $option_value);
    
    public function haveSerializedOptionInDatabase($option_name, $option_value);
    
    public function seeOptionInDatabase($option_name, $option_value);
    
    public function dontSeeOptionInDatabase($option_name, $option_value);
    
    public function seeSerializedOptionInDatabase($option_name, $option_value);
    
    public function dontSeeSerializedOptionInDatabase($option_name, $option_value);

see source for all methods.  
I've added additional methods to have full control on the database setup and be able to remove entries from each table like

    public function dontHaveOptionInDatabase(array $criteria);

    public function dontHaveUserInDatabase(array $criteria);

    public function dontHavePostInDatabase(array $criteria);

wrapping the <code>ExtendedDb::dontHaveInDatabase</code> method for quicker and clearer access; a <code>dontHaveSomethinInDatabase</code> method exists for each table, take a look at the source to see them all.

### ExtendedDb module
The module is an extension of the <code>Codeception\Module\Db</code> class implementing some methods to allow for more CRUD complete operations on the database with the methods

    public function dontHaveInDatabase($table, array $criteria);

    public function haveOrUpdateInDatabase($table, array $data)
