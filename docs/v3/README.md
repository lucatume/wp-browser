> **This is the documentation for version 3 of the project.**
> **The current version is version 4 and the documentation can be found [here](./../README.md).**

The [wp-browser project](https://github.com/lucatume/wp-browser "lucatume/wp-browser · GitHub") provides a [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") based solution to test WordPress plugins, themes and whole sites at all levels of testing.  

The purpose of this documentation is to help you set up, run and iterate over your project and test code using the powerful APIs provided by Codeception while trying to alleviate the pains of setting it up for WordPress projects.  

Throughout the documentation you will find references to test terminology: I've tried to condense those into small, digestable chunks to provide a rough idea without and a limited context; where required I tried to provide links to dive deeper into the subjects.  

Happy testing!

## Table of contents

* [Welcome](welcome.md)
    * [Frequently asked questions](faq.md)
    * [Codeception, PHPUnit and wp-browser](codeception-phpunit-and-wpbrowser.md)
* [Using wp-browser with Codeception 4.0](codeception-4-suport.md)
* Migration guides
    * [Version 2 to version 3](migration/from-version-2-to-version-3.md)
* [Levels of testing](levels-of-testing.md)
* Getting started
    * [Requirements](requirements.md)
    * [Installation](installation.md)
    * [Setting up a minimum WordPress installation](setting-up-minimum-wordpress-installation.md)
    * [Configuration](configuration.md)
* Tutorials
    * [Automatically change database during acceptance and functional tests](tutorials/automatically-change-db-in-tests.md)
    * [Setting up wp-browser on VVV to test a plugin](tutorials/vvv-setup.md)
    * [Setting up wp-browser on MAMP for Mac to test a plugin](tutorials/mamp-mac-setup.md)
    * [Setting up wp-browser on WAMP for Windows to test a plugin](tutorials/wamp-setup.md)
    * [Setting up wp-browser on Local by Flywheel to test a plugin](tutorials/local-flywheel-setup.md)
* Modules
    * [WPBrowser](modules/WPBrowser.md)
    * [WPCLI](modules/WPCLI.md)
    * [WPDb](modules/WPDb.md)
    * [WPFilesystem](modules/WPFilesystem.md)
    * [WPLoader](modules/WPLoader.md)
    * [WPQueries](modules/WPQueries.md)
    * [WPWebDriver](modules/WPWebDriver.md)
* Advanced Usage
    * [Running tests in separate processes](advanced/run-in-separate-process.md)
* [Events API](events-api.md)
* [Extensions](extensions.md)
* [Commands](commands.md)
* [Contributing](contributing.md)
* [Sponsors](sponsors.md)
* [Changelog](https://github.com/lucatume/wp-browser/blob/master/CHANGELOG.md)
