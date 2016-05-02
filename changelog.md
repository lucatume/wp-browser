# Change Log
All notable changes after version 1.6.16 to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

##[unreleased] Unreleased

##[1.10.8] 2016-05-02
### Fixed
- missing parameters errors in `wpcept` bin
- missing `blogs` table initialization on multisite installation tests with `WPLoader` module

### Changed
- `wpcept` bin command to include and use latest Codeception code

##[1.10.7] 2016-03-30
### Fixed
- faulty active plugin option setting

##[1.10.6] 2016-03-30
### Fixed
- fixed db driver initialization in `WPDb::_cleanup` method

##[1.10.5] 2016-03-20
### Fixed
- plugin activation and deactivation related methods for WPBrowser and WPWebDriver modules (thanks @dimitrismitsis)

##[1.10.4] 2016-02-23
### Fixed
- `WPBootstrapper` module `wpdb` connection and re-connection process

##[1.10.3] 2016-02-22
### Added
- `WPBrowserMethods::amOnAdminPage` method, applies to WPWebDriver and WPBrowser modules
- `WPBootstrapper::setPermalinkStructureAndFlush` method 
- `WPBootstrapper::loadWpComponent` method 

##[1.10.0] 2016-02-18
### Modified
- the `WPBrowser` and `WpWebDriver` `activatePlugin` to use DOM in place of strings (l10n friendly)
- the `WPBrowser` and `WpWebDriver` `deactivatePlugin` to use DOM in place of strings (l10n friendly)

### Added
- the WPBootstrapper module

##[1.9.5] 2016-02-15
### Fixed
- wrong scaffolding structure when using the `wpcept bootstrap:pyramid command`

### Added
- the `wpunit` test suite to the ones scaffolded by default when using the `bootstrap:pyramid` command

##[1.9.4] 2016-01-20 
### Fixed
- proper name of `WPAjaxTestCase` class

##[1.9.3] 2016-01-20
### Added
- `wpunit` suite generation when using the `wpcept:bootstrap` command

### Changed
- provisional redirect status `301` to `302` in temporary `.htaccess` file used by `WPDb::haveMultisisiteInDatabase` method

### Removed
- `update` and `checkExistence` deprecated parameters from WPDb module

##[1.9.2] 2016-01-09
### Added
- the `$sleep` parameter to the `WPDb::haveMultisiteInDatabase` method
- missing `WPDb::$blogId` reset in cleanup method
- the `WPDb::useTheme` method
- the `WPDb::haveMenuInDatabase` method
- the `WPDb::haveMenuItemInDatabase` method
- the `WPDb::seeTermRelationshipInDat` method

##[1.9.1] 2016-01-07
### Fixed
- wrong table prefix in `WPDb::grabPrefixedTableNameFor` method for main blog when switching back to main blog.
### Removed
- the `WPDb::hitSite` method as not used anymore in code base.

##[1.9.0] 2015-12-23
### Changed
- the `WPDb::haveMultisiteInDatabase` method will now scaffold browser accessible multisite installations starting from a single site one
- WPDb module will drop tables created during multisite scaffolding

### Added
- `$autoload` parameter to `WPDb::haveOptionInDatabase` method
- `wpRootFolder` optional config parameter to the `WPDb` module

##[1.8.11] 2015-12-17
### Fixed
- added a check in embedded `bootstrap.php` file of WPLoader module for defined multisite vars

##[1.8.10] 2015-12-11
### Changed
- `WPTestCase` class now set the `$backupGlobals` to `false` by default
- removed default `$backupGlobals` value setting from test template

##[1.8.9] 2015-12-10
### Changed
- memory limit constants (`WP_MEMORY_LIMIT` and `WP_MAX_MEMORY_LIMIT`) will now check for pre-existing definitions in WPLoader module bootstrap

##[1.8.8] 2015-12-08
### Added
- blogs related methods to the WPDb module
- `haveMany` methods in WPDb module will now parse and compile [Handlebars PHP](https://github.com/XaminProject/handlebars.php "XaminProject/handlebars.php · GitHub") templates

### Changed
- renamed `haveMultisite` method to `haveMultisiteInDatabase` in WPDb module
### Removed
- `haveLinkWithTermInDatabase` method from WPDb module

##[1.8.7] 2015-12-07
### Added
- the `seeTableInDatabase` method to WPDb module
- the `haveMultisiteInDatabase` method to WPDb module
- multisite table `grabXTAbleName` methods to WPDb module

### Changed
- `havePostmetaInDatabase` method name to `havePostMetaInDatabase` in WPDb module

##[1.8.6] 2015-12-04
### Fixed
- issue with password validation in WPDb module

##[1.8.5] 2015-12-03
### Added
- `haveManyTermsInDatabase` method to WPDb module
- `seeTermTaxonomyInDatabase` method to WPDb module
- `dontSeeTermTaxonomyInDatabase` method to WPDb module
- `haveTermMetaInDatabase` method to WPDb module
- `grabTermMetaTableName` method to WPDb module
- `seeTermMetaInDatabase` method to WPDb module
- `dontHaveTermMetaInDatabase` method to WPDb module
- `dontSeeTermMetaInDatabase` method to WPDb module
- the possibility to have user meta in the database while inserting the user using `haveUserInDatabase` WPDb module method

### Changed
- WPDb `havePostMetaInDatabase` will not add a row for each element in an array meta value but serialize it

##[1.8.4] 2015-12-03
### Added
- `haveManyUsersInDatabase` method to WPDb module

### Changed
- links related methods in WPDb module

##[1.8.3] 2015-12-02
### Changed
- comments related methods in WPDb module

##[1.8.2] 2015-11-30
### Added
- terms related methods to WPDb module
- terms insertion capability to the `havePostInDatabase` and `haveManyPostsInDatabase` WPDb methods

##[1.8.1a] 2015-11-27
### Fixed
- fixed redundant logic in `WPDb::seeTermInDatabase` and `WPDb::dontSeeTermInDatabase` methods

##[1.8.1] 2015-11-27
### Changed
- reworked term related methods in WPDb module

##[1.8.0] 2015-11-26
### Added
- user and user meta related methods to the WPDb module
- options related methods to the WPDb module
- post and post meta related methods to the WPDb module

### Fixed
- duplicate call to globals definition in `install.php` file
- renamed file creating issues on with case sensitive systems (thanks @barryhuges)

### Changed
- some `seeInDatabase` method syntax

##[1.7.16a] 2015-11-18 
### Fixed
- the `_delete_all_posts` function in the automated tests bootstrap file now runs without any filters/actions hooked

##[1.7.15] 2015-11-17
### Fixed
- namespace of the `WPRestApiTestCase` class
- multiple loading of factory and Trac ticket classes in `WPTestCase` and `WP_UnitTestCase` classes
- windows and PHP 5.4 compatibility problems (thanks @zdenekca)

### Changed
- tested and modified WPDb user related methods
- `dontHaveOptionInDatabase` method from the `WPDb` module class

### Added
- user and user meta related methods to the `WPDb` module
- options and transients related methods to the `WPDb` module

##[1.7.14] 2015-11-10
### Fixed
- call to deprecated `delete` driver method in `ExtendedDb` module

##[1.7.13] 2015-11-10
### Added
- the `\Codeception\TestCase\WPTestCase`, an extension of the base Codeception test case and a copy of the core `WP_UnitTestCase` class
- the `\Codeception\TestCase\WPCanonicalTestCase`, an extension of the base Codeception test case and a copy of the core `WP_Canonical_UnitTestCase` class
- the `\Codeception\TestCase\WPAjaxTestCase`, an extension of the base Codeception test case and a copy of the core `WP_Ajax_UnitTestCase` class
- the `\Codeception\TestCase\WPRestApiTestCase`, an extension of the base Codeception test case and a copy of the core `WP_Test_REST_TestCase` class
- the `\Codeception\TestCase\WPXMLRPCTestCase`, an extension of the base Codeception test case and a copy of the core `WP_XMLRPC_UnitTestCase` class
- the `wpcept generate:wpcanonical` command to generate test cases extending the `\Codeception\TestCase\WPCanonicalTestCase` class
- the `wpcept generate:wpajax` command to generate test cases extending the `\Codeception\TestCase\WPAjaxTestCase` class
- the `wpcept generate:wprest` command to generate test cases extending the `\Codeception\TestCase\WPRestApiTestCase` class
- the `wpcept generate:wpxmlrpc` command to generate test cases extending the `\Codeception\TestCase\WPXMLRPCTestCase` class

### Changed
- updated core unit tests suite code latest version
- bundled test case classes names will now point to the vanilla WP test cases
- the `wpcept generate:wpunit` command will now generate test cases extending the `\Codeception\TestCase\WPTestCase` class

### Fixed
- namespaced test class generation for `generate:wp*` commands will now properly generate the namespace string

##[1.7.12] 2015-11-6
### Changed
- code format

##[1.7.11] 2015-11-6
### Changed
- updated the test case class to latest from Core tests (thanks @zbtirell)

### Added
- the `waitForJQueryAjax` and `grabFullUrl` methods to the WPWebDriver module

##[1.7.10] 2015-11-5
### Changed
- modified WPLoader module compatibility check to allow for *Db modules `populate` setting

##[1.7.9] 2015-10-29
### Fixed
- config file search path in the WP Loader module

##[1.7.8] 2015-10-29
### Changed
- the `config_file` WP Loader module setting to `configFile`

##[1.7.7] 2015-10-22
### Changed
- the `WP_UnitTestCase` class bundled to extend `Codeception\Testcase\Test` class (thanks @borkweb)

##[1.7.6] 2015-10-21
### Fixed
- call to deprecated `set_current_user` function replaced with call to `wp_set_curren_user`

##[1.7.5] 2015-10-21
### Fixed
- missing `codecept_relative_path` function in `autoload.php` file (thanks @dbisso)

##[1.7.4] 2015-10-19
### Added
- plugin activation now happens with the current user set to the Administrator

### Changed
- modified the file structure
- the plugin activation hook of the WP Loader module to `wp_install` (thanks @barryhuges)

##[1.7.3] 2015-10-14
### Added
- the `pluginsFolder` setting to the WP Loader module

### Fixed
- issue with exception generation exception in WP Loader; did happen if a plugin was not found

### Changed
- some `WPLoader` methods visibility to allow for extension
- conditionally write lines to .gitignore to avoid duplicate entries(thanks @borkweb)

##[1.7.2] 2015-10-06
### Added
- an exception when a plugin file part of WPLoader `plugins` setting is not found
- the `activatePlugins` setting in WPLoader configuration

##[1.7.1] 2015-10-05
### Changed
- modifications/removals made to the `phpunit` element defined in the `phpunit.xml` file will be preserved across regenerations when using `wpcept generate:phpunitBootstrap` command.

##[1.7.0] 2015-10-05
### Added
- the possibility to use the `~` symbol in WP Loader configuration
- the possibility to specify config file names and have WP Loader search in any parent folder in place of just WP root and above
- the `wpcept generate:phpunitBootstrap` command to allow for the generation of a PHPUnit configuration and bootstrap file to run functional tests

### Changed
- Codeception dependency to "~2.0"
- administrator username and password default values for easier search and replacing operation
- files and classes organization to reflect namespacing

### Removed
- `badcow\lorem-ipsum` dependency

##[1.6.19] - 2015-10-02
### Added
- added the `changelog.txt` file, thanks @olivierlacan for the http://keepachangelog.com/ site and the information.
- check and exception for WPLoader `wpRootFolder` parameter
- check and exception for conflicting WPDb, Db and WP Loader settings to avoid database handling issues
- it's now possible to pass an array of paths to external config files as `config_file` WP Loader parameter

### Changed
- WPLoader will look for the config file defined in the `config_file` parameter in WP root folder and the one above before throwing an module configuration exception.
- Markdown formatting issues in the README file
- WPDb module has been removed from default modules in the `functional` and `acceptance` suites bootstrapped using the `wpcept bootstrap` command
- WPDb module has been removed from default modules in the `service` and `ui` suites bootstrapped using the `wpcept bootstrap:pyramid` command

##[1.6.18] - 2015-10-01
### Added
- `config_file` WPLoader parameter

##[1.6.17] - 2015-09-30
### Added
- `plugins` WPLoader parameter
- `bootstrapActions` WPLoader parameter

##[1.6.16] - 2015-09-30
### Fixed
- Reference to ModuleConfigException class in WPLoader class.

[unreleased]: https://github.com/lucatume/wp-browser/compare/1.10.8...HEAD
[1.10.8]: https://github.com/lucatume/wp-browser/compare/1.10.7...1.10.8
[1.10.7]: https://github.com/lucatume/wp-browser/compare/1.10.6...1.10.7
[1.10.6]: https://github.com/lucatume/wp-browser/compare/1.10.5...1.10.6
[1.10.5]: https://github.com/lucatume/wp-browser/compare/1.10.4...1.10.5
[1.10.4]: https://github.com/lucatume/wp-browser/compare/1.10.3...1.10.4
[1.10.3]: https://github.com/lucatume/wp-browser/compare/1.10.0...1.10.3
[1.10.0]: https://github.com/lucatume/wp-browser/compare/1.9.5...1.10.0
[1.9.5]: https://github.com/lucatume/wp-browser/compare/1.9.4...1.9.5
[1.9.4]: https://github.com/lucatume/wp-browser/compare/1.9.3...1.9.4
[1.9.3]: https://github.com/lucatume/wp-browser/compare/1.9.2...1.9.3
[1.9.2]: https://github.com/lucatume/wp-browser/compare/1.9.1...1.9.2
[1.9.1]: https://github.com/lucatume/wp-browser/compare/1.9.0...1.9.1
[1.9.0]: https://github.com/lucatume/wp-browser/compare/1.8.11...1.9.0
[1.8.11]: https://github.com/lucatume/wp-browser/compare/1.8.10...1.8.11
[1.8.10]: https://github.com/lucatume/wp-browser/compare/1.8.9...1.8.10
[1.8.9]: https://github.com/lucatume/wp-browser/compare/1.8.8...1.8.9
[1.8.9]: https://github.com/lucatume/wp-browser/compare/1.8.8...1.8.9
[1.8.8]: https://github.com/lucatume/wp-browser/compare/1.8.7...1.8.8
[1.8.7]: https://github.com/lucatume/wp-browser/compare/1.8.6...1.8.7
[1.8.6]: https://github.com/lucatume/wp-browser/compare/1.8.5...1.8.6
[1.8.5]: https://github.com/lucatume/wp-browser/compare/1.8.4...1.8.5
[1.8.4]: https://github.com/lucatume/wp-browser/compare/1.8.3...1.8.4
[1.8.3]: https://github.com/lucatume/wp-browser/compare/1.8.2...1.8.3
[1.8.2]: https://github.com/lucatume/wp-browser/compare/1.8.1a...1.8.2
[1.8.1a]: https://github.com/lucatume/wp-browser/compare/1.8.1...1.8.1a
[1.8.1]: https://github.com/lucatume/wp-browser/compare/1.8.0...1.8.1
[1.8.0]: https://github.com/lucatume/wp-browser/compare/1.7.16a...1.8.0
[1.7.16a]: https://github.com/lucatume/wp-browser/compare/1.7.15...1.7.16a
[1.7.15]: https://github.com/lucatume/wp-browser/compare/1.7.14...1.7.15
[1.7.14]: https://github.com/lucatume/wp-browser/compare/1.7.13c...1.7.14
[1.7.13c]: https://github.com/lucatume/wp-browser/compare/1.7.12...1.7.13c
[1.7.12]: https://github.com/lucatume/wp-browser/compare/1.7.11...1.7.12
[1.7.11]: https://github.com/lucatume/wp-browser/compare/1.7.10...1.7.11
[1.7.10]: https://github.com/lucatume/wp-browser/compare/1.7.9...1.7.10
[1.7.9]: https://github.com/lucatume/wp-browser/compare/1.7.8...1.7.9
[1.7.8]: https://github.com/lucatume/wp-browser/compare/1.7.8...1.7.8
[1.7.7]: https://github.com/lucatume/wp-browser/compare/1.7.6...1.7.7
[1.7.6]: https://github.com/lucatume/wp-browser/compare/1.7.5...1.7.6
[1.7.5]: https://github.com/lucatume/wp-browser/compare/1.7.4...1.7.5
[1.7.4]: https://github.com/lucatume/wp-browser/compare/1.7.3...1.7.4
[1.7.3]: https://github.com/lucatume/wp-browser/compare/1.7.2...1.7.3
[1.7.2]: https://github.com/lucatume/wp-browser/compare/1.7.1...1.7.2
[1.7.1]: https://github.com/lucatume/wp-browser/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/lucatume/wp-browser/compare/1.6.19...1.7.0
[1.6.19]: https://github.com/lucatume/wp-browser/compare/1.6.18...1.6.19
[1.6.18]: https://github.com/lucatume/wp-browser/compare/1.6.17...1.6.18
[1.6.17]: https://github.com/lucatume/wp-browser/compare/1.6.16...1.6.17
[1.6.16]: https://github.com/lucatume/wp-browser/compare/1.6.15...1.6.16
