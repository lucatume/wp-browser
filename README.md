wp-browser
==========

A WordPress specific extension of PhpBrowser for Codeception.

The package includes a class extending Codeception PhpBrowser module to adds WordPress related assertions for <code>cest</code> and <code>cept</code> tests and an extension of Codeception own Db module meant to allow for more comfortable WordPress specific database handling and testing.
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
                tablePrefix: 'wp'
                checkExistence: true
                update: true

and configure <code>Db</code> parameters and the additional ones available to the <code>WPDb</code> module:  
    
* <code>url</code> - the site home url (required)
* <code>tablePrefix</code> - allows specifying the table prefix used in the installation, defaults to "wp" (optional)
* <code>checkExistence</code> - enables some low level AI on the module side to insert needed elements in the database, e.g. will add a term and post before adding a relation between them; defaults to <code>false</code> (optional)
* <code>update</code> - will try updating the database on duplicate entries; defaults to <code>true</code> (optional)

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
                tablePrefix: 'wp'
                checkExistence: true
                update: true

and configure <code>Db</code> parameters and the additional ones available to the <code>WPDb</code> module:  

* <code>tablePrefix</code> - allows specifying the table prefix used in the installation, defaults to "wp" (optional)
* <code>checkExistence</code> - enables some low level AI on the module side to insert needed elements in the database, e.g. will add a term and post before adding a relation between them; defaults to <code>false</code> (optional)
* <code>update</code> - will try updating the database on duplicate entries; defaults to <code>true</code> (optional)

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


## Changelog
<code>1.3.0</code>

* added <code>dontHaveSomethingInDatabase</code> methods to the <code>WPDb</code> class
* fixed a bug that generated wrong SQL statements in the <code>WPDb::haveUserInDatabase</code> method

<code>1.2.0</code>

* added the <code>haveOrUpdateInDatabase</code> and <code>dontHaveInDatabase</code> methods alongside the <code>Codeception\Module\ExtendedDb</code> module.

<code>1.1.0</code>

* first public release ans semantic versioning jumpstart