wp-browser
==========

WordPress extension of PhpBrowser for Codeception.

The package includes a class extending Codeception PhpBrowser module that adds WordPress related assertions for <code>cest</code> and <code>cept</code> tests.

## Installing
To install simply require the package in the <code>composer.json</code> file like

  "require-dev":
    {
      "lucatume/wp-browser": "master@dev"
    }
    
and then use <code>composer update</code>

## Configuration
In the <code>acceptance.suite.yml</code> add the module among the loaded ones

    # Codeception Test Suite Configuration

    # suite for acceptance tests.
    # perform tests in browser using the WebDriver or PhpBrowser.
    # If you need both WebDriver and PHPBrowser tests - create a separate suite.

    class_name: AcceptanceTester
    modules:
        enabled:
            - AcceptanceHelper
            - PhpBrowser
            - WPBrowser
        config:
            PhpBrowser:
                url: 'http://wp-routing.local'
            WPBrowser:
                adminUsername: 'theAdmin'
                adminPassword: 'iguana'
                adminUrl: '/wp-core/wp-admin'

and configure the required <code>adminUsername</code>, <code>adminPassword</code> and <code>adminUrl</code> fields. The <code>adminUrl</code> is relative to the url specified in PhpBrowser configuration.

## Methods
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

all pretty explanatory. Methods like <code>seePlugin...</code> require the use of the <code>amOnPluginsPage</code> method before their invocation to navigate PhpBrowser to the right folder.