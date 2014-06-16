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

    public function loginAsAdmin();
    public function loginAs($username, $password);
    public function amOnPluginsPage();
    public function activatePlugin($pluginSlug);
    public function deactivatePlugin($pluginSlug);
    public function seePluginDeactivated($pluginSlug);
    public function seePluginActivated($pluginSlug);
    public function seePluginInstalled($pluginSlug);
    public function doNotSeePluginInstalled($pluginSlug);
    public function seeErrorMessage($classes = '');
    public function seeMessage($classes = '');
    public function seeWpDiePage();

all pretty explanatory. Methods like <code>seePlugin...</code> require the use of the <code>amOnPluginsPage</code> method before their invocation to navigate PhpBrowser to the right folder.