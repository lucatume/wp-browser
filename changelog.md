# Change Log
All notable changes after version 1.6.16 to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [unreleased] Unreleased

## [2.1.6] 2018-09-25;
### Fixed
- set an upper version bound for Codeception of `2.4.5` to avoid incompatibility issues between `WPDb` and `Db` modules

## [2.1.5] 2018-08-01;
### Fixed
- add the `waitlock` parameter to the `WPDb` template configuration
- make sure the `waitlock` parameter is set in `WPDb` module configuration

## [2.1.4] 2018-05-08;
### Fixed
- the `comment_count` of posts to which comments were added using `WPDb::haveCommentInDatabase` (thanks @ptrkcsk)

### Added
- the `WPDb::countRowsInDatabase` method

## [2.1.3] 2018-05-07;
### Fixed
- check for an existing `.env` file when initializing and ask for the `.env` file name in the init command

## [2.1.2] 2018-04-26;
### Added
- the `WPDb::grabUsersTableName` method to the `WPdb` module
- the `WPDb::dontHaveUserInDatabaseWithEmail` method to the `WPDb` module

## [2.1.1] 2018-04-25;
### Changed
- make the `codecept init wpbrowser` command scaffold the `codeception.dist.yml` file

## [2.1] 2018-04-17;
### Added
- `.env` file based variable setup in the `codecept init wpbrowser` command

## [2.0.5.2] 2018-04-05;
### Fixed
- typos in the readme (thanks @mboldt)
- restore the inclusion of some `WPLoader` required files in the `src/includes/bootstrap.php` file; those were erroneously removed in `2.0` (thanks @rahularyan)

### Added
- the `wpbrowser_includes_dir` function to provide a way to get the path to the `src/includes` folder or files in it

## [2.0.5.1] 2018-03-20;
### Fixed
- missing use of the installation filters when using `WPLoader`  isolated install

## [2.0.5] 2018-03-20;
### Added
- support for installation filters, via the `$wp_tests_options['installation_filters']` global, in the `WPLoader` module

## [2.0.4] 2018-03-16;
### Fix
- restore the loading of utility files in `WPLoader` module bootstrap file

## [2.0.3] 2018-03-14;
### Fixed
- reference to the `WPHtmlOutputDriver` class from the `wp-snapshot-assertions` package

## [2.0.2] 2018-03-10;
### Changed
- moved the snapshot assertions related code from this package to the `lucatume/wp-snapshot-assertions` one
- the `tad\WPBrowser\Snapshot\WPHtmlOutputDriver` is now just an extension of the `tad\WP\Snapshots\WPHtmlOutputDriver` class from the `lucatume/wp-snapshot-assertions` package.

## [2.0.1] 2018-03-06;
### Fixed
- restore loading of the utils file in the WPLoader module, was erroneously removed in v2.0

## [2.0] 2018-03-03;
### Removed
- support for PHP 5.6 to be handled in branch `php-56-compat`

### Added
- require PHP 7.0

## [1.22.8] 2018-02-28;
### Fixed
- an issue with Windows directory separators in the `Wpbrowser` template (thanks @zdenekca)

## [1.22.7.1] 2018-02-28;
### Fixed
- PHPUnit version to use <7 for incompatibility issues

## [1.22.7] 2018-02-27;
### Added
- more PHPUnit 6+ aliases in the shims file to allow for use of older tests with newer PHPUnit versions not providing `PHPUnit_Framwork_` classes

## [1.22.6.1] 2018-02-14;
### Fixed
- remove unused `check` method from `WPHtmlOutputDriver` class

## [1.22.6] 2018-02-14;
### Added
- support for "tolerable differences" in the `WPHtmlOutputDriver` class

## [1.22.5] 2018-02-12;
### Added
- fix pre-4.7 testcase incompatibility in `WPTestCase` class (thanks @zlinke77)

## [1.22.4] 2018-02-09;
### Added
- PHP 7.2 tests (thanks @tangrufus)

### Fixed
- replaced call to deprecated `each` in file (thanks @tangrufus)

## [1.22.3] 2018-01-30;
### Fixed
- removed the faulty `output` parameter from the `WPCLI::cli` method
- fixed issues witht `WPCLI` module arguments escaping

## [1.22.2] 2018-01-27;
### Changed
- updated the `WPHtmlOutputDriver` class to support the optional `snapshotUrl` argument to focus the URL replacement

## [1.22.1] 2018-01-26;
### Fixed
- add the `electrolinux/phpquery` dependency as non dev one

## [1.22.0] 2018-01-26;
### Fixed
- added wait operation to `WPBrowserMethods` to try and tackle the missing login form issue () 
- replace `eventviva/php-image-resize` dependency with `gumlet/php-image-resize`
- added the `WPHtmlOutputDriver` class to allow comparison of WordPress specific HTML output using the `spatie/phpunit-snapshot-assertions` library

## [1.21.26] 2018-01-15;
### Fixed
- a variable reference issue in the `WPDb` module (thanks @jcabot)

## [1.21.25] 2018-01-11;
### Fixed
- an issue that was preventing `WPDb::haveAttachmentInDatabase` method from working correctly

## [1.21.24] 2018-01-11;
### Changed
- broader version constraints for `symfony/process` package
- move the `eventviva/php-image-resize` dependency to `require` in place of `require-dev`

## [1.21.23] 2017-12-13;
### Fixed
- fixed an `ExtendedDbDriver` signature issue (thanks @kouratoras)
- better handling of user input and error in `wpbrowser` template
- fixed an issue where files with `declare` type instructions would trigger fatal errors when using `WPLoader` module in `multisite` mode (thanks @jbrinley)

## [1.21.22] 2017-11-27;
### Fixed
- serialization issue in WP-Loader module (thanks @johnnyhuy)

## [1.21.21] 2017-11-24;
### Fixed
- user defined suite names are now respected during `init wpbrowser` based scaffolding process

## [1.21.20] 2017-10-10;
### Fixed
- a WordPress 4.6 related issue with hook saving (issue #108)

## [1.21.19] 2017-10-09;
### Fixed
- Travis CI tests
- restored the creation of blog tables when using `WPDb::haveBlogInDatabase` or `WPDb::haveManyBlogsInDatabase` methods

## [1.21.18] 2017-09-26
### Fixed
- `WPDb`: a more efficient regex to parse the dump (thanks @slaFFik)
- `WPDb`: avoid running the dump through URL replacement functions if `urlReplacement` has been deactivated in config (thanks @slaFFik)
- `WPDb`: avoid running the dump through white space removal, thus loading it in memory, if `populator` is being used (thanks @slaFFik)

## [1.21.17] 2017-08-21
### Fixed
- an issue where some checks could be made by the `WPTestCase` class on non-existing folders

## [1.21.16] 2017-08-12
### Fixed
- an issue in the `WPFilesystem` module that would not allow scaffolding mu-plugins

## [1.21.15] 2017-08-09
### Fixed and changed
- moved the `rrmdir` function to wp-browser `functions.php` file and removed it from the `tests/_support/functions.php` file

## [1.21.14] 2017-08-02
### Fixed
- an issue with symbolic linking of the root dir

## [1.21.13] 2017-07-28
### Fixed
- more sane support for `--quiet` and `--no-interaction` options in `WPBrowser` template (issue #95 cont.)

## [1.21.12] 2017-07-26
### Fixed
- support for `--quiet` and `--no-interaction` options in `WPBrowser` template (issue #95)

## [1.21.11] 2017-07-19
### Fixed
- an issue preventing writes in the `WPFilesystem::writeToUploadedFile` method

### Added
- `attachment` post type methods to the `WPDb` module

## [1.21.10] 2017-07-14
### Added
- support for environments in the `rootFolder` parameter of the Symlinker extension

## [1.21.9] 2017-07-14
### Fixed
- issue where users table would be set to `wp_users` in `WPDb::grabUserIdFromDatabase()` method (thanks @gundamew)

## [1.21.8] 2017-07-12
### Added
- first version of the `WPFilesystem` module

## [1.21.7] 2017-07-07
### Fixed
- removed excessive bracket in `WPBrowser` template

## [1.21.6] 2017-07-06
### Changed
- switch to `.env` based configuration for tests
- fix an issue where in some cases (e.g. CLI) `WPLoader` module set to `loadOnly: true` would generate errors

## [1.21.5] 2017-06-30
### Fixed
- issue with `WPDb::haveOrUpdateInDatabase` method

## [1.21.4] 2017-06-21
### Fixed
- `WPDb` module sql dump file handling issue (#81)
- `WordPress` module issue related to IP spoofing

## [1.21.3] 2017-06-07
### Fixed
- load file required by `attachment` factory before accessing it (`WPLoader` module in `loadOnly` configuration)
- domain replacement in SQL dump file in `WPDb` module

## [1.21.2] 2017-06-06
### Fixed
- added missing vars to bootstrap template

## [1.21.1] 2017-06-05
### Fixed
- PHP7 syntax issue

## [1.21.0] 2017-06-05
### Added
- support for Codeception `2.3`
- experimental support for PHPUnit `6.0`
- support for user-land SQL dump file import in `WPDb` module (thanks @sc0ttkclark)

### Changed
- the `wpcept` command is now deprecated in favour of a template based solution

### Removed
- the `generate:phpunitbootstrap` command

## [1.20.1] 2017-05-23
### Changed
- locked `codeception/codeception` version at `~2.2.0` while support for version `2.3` is developed
- moved the `codeception/codeception` requirement to the `require` section
- updated the code of `dontHaveInDatabase` type methods of `WPDb` to remove meta of handled objects by default
 
## [1.20.0] 2017-05-15
### Added
- added support for "just loading WordPress" to the WPLoader module using the `loadOnly` parameter

## [1.19.15] 2017-05-01
### Changed
- added Y offset to the plugin activation functions to avoid overlap with the admin bar

## [1.19.14] 2017-04-28
### Fixed
- wording and example about `window_size` parameter of `WPWebDriver` module in the README (thanks @petemolinero)
- wording of the `WordPress` module description (thanks @azavisha)
- issue where plugin would not be activated when alpha positioned at the bottom of a long list (issue #64)

### Changed
- allow the `activatePlugin` and `deactivatePlugin` of `WPBrowser` and `WPWebDriver` modules to accept an array of plugin slugs to activate

## [1.19.13] 2017-03-25
### Changed
- updated `wp-cli` version requirement to `1.1` (thanks @TangRufus)

## [1.19.12] 2017-03-10
### Fixed
- wait for login form elements in `loginAs` and `loginAsAdmin` `WpWebDriver' methods (thanks @TangRufus)

## [1.19.11] 2017-02-20
### Fixed
- missing `$_SERVER['SERVER_NAME']` var in the `WordPress` connector that would trigger notices from WordPress `general-template.php` file

### Changed
- cleaned the `WordPress` module from duplicated methods and added missind documentation blocks

## [1.19.10] 2017-02-14
### Fixed
- if the `pluginsFolder` parameter of the `WPLoader` module is defined use it to set the `WP_PLUGIN_DIR` constant

## [1.19.9] 2017-02-14
### Fixed
- missing support for custom plugin and content paths in `WPLoader` isolated install (thanks @borkweb)

## [1.19.8] 2017-01-25
### Changed
- output return in `WPCLI` module: will now return the line if the command output is just one line

## [1.19.7] 2017-01-25
### Fixed
- fixed an issue where command line options where ignored during non-interactive `bootstrap` and `bootstrap:pyramid` commands

## [1.19.6] 2017-01-25
### Added
- new REST API controller and post type controller test cases
- commands to REST API controller and post type controller test cases

### Fixed
- issue when using `isolatedInstall: false` that would generate an error on `add_filter`
- removed deprecated blog insertion instruction from same scope installation script that would cause db error output
- cookie generation issues in the `WordPress` module

### Changed
- refreshed factories, testcases and code from Core suite

## [1.19.5] 2016-12-07
### Fixed
- `WPLoader` module WordPress 4.7 compatibility issues, [#60](https://github.com/lucatume/wp-browser/issues/60)

## [1.19.4] 2016-11-30
### Fixed
- `WPCLI` module exception on non string output, [#59](https://github.com/lucatume/wp-browser/issues/59)

## [1.19.3] 2016-11-24
### Fixed
- `WordPress` module serialization issue

## [1.19.2] 2016-11-16
### Fixed
- autoload file issue

## [1.19.1] 2016-11-15
### Added
- support for `tax_input` in place of `terms` in `WPDb` module to stick with `wp_insert_post` function convention
- support for `meta_input` in place of `meta` in `WPDb` module to stick with `wp_insert_post` function convention

## [1.19.0] 2016-11-13
### Added
- network activation of plugins in multisite `WPLoader` tests

### Fixed
- more verbose output for `WPLoader` isolated installation process

## [1.18.0] 2016-11-02
### Added
- support for `--type` option in `wpcept bootstrap` interactive mode
- theme activation during `WPLoader` module activation
- the Copier extension

## [1.17.0] 2016-10-25
### Added
- first version of interactive mode to the `bootstrap` command
- first version of interactive mode to the `bootstrap:pyramid` command
- support for the `theme` configuration parameter in the `WPLoader` module configuration

### Fixed
- plugin activation/deactivation in `WPBrowser` module, thanks [Ippey](https://github.com/Ippey) 

## [1.16.0] 2016-09-05
### Added
- WPCLI module to use and access [wp-cli](http://wp-cli.org/) functionalities from within tests

### Changed
- Travis configuration file `.travis.yml` to use [external Apache setuup script](https://github.com/lucatume/travis-apache-set)

## [1.15.3] 2016-08-19
### Addded
- Travis CI integration

### Fixed
- a smaller issue with tests for the `WPBootstrapper` module and `DbSnapshot` command

## [1.15.2] 2016-08-10
### Fixed
- `WordPress` module not dumping page source on failure (thanks @kbmt)

### Changed
- better uri parsing in `WordPres` module (thanks @kbmt)

## [1.15.1] 2016-07-22
### Fixed
- missing back-compatibility configuration call in `WPBrowser` and `WPWebDriver` modules

## [1.15.0] 2016-07-19
### Added
- the `bootstrapActions` parameter of the `WPLoader` module will now accept static method signatures
- the `WordPress` module to be used for real functional tests
- support for the `rootFolder` parameter in the `Symlinker` extension

### Changed
- the parameter to specify the path to the admin area in the `WPBrowser` and `WPWebDriver` modules has been renamed to `adminPath`, was previously `adminUrl`
- default modules configurations to reflect new module usage

### Removed
- the `WPRequests` module to use the `WordPress` functional module in its place

## [1.14.3] 2016-06-10
### Changed
- the `WPLoader` module will now run the installation process in a separate process by default (thanks @jbrinley)

### Fixed
- issue with multisite database dumps and domain replacement (thanks @LeRondPoint)

## [1.14.2] 2016-06-10
### Added
- support for the `urlReplacement` configuration parameter in `WPDb` module to prevent attempts at hard-coded URL replacement in dump file

## [1.14.1] 2016-06-09
### Changed
- the `WPDb` module will try to replace the existing dump file hard-coded url with the one specified in the configuration during initialization

## [1.14.0] 2016-06-09
### Added


### Changed
- renamed the `wpunit` suite to `integration` to stick with proper TDD terms (thanks @davert)
- updated `wpcept` `bootstrap` and `bootstrap:pyramid` commands to scaffold suites closer in modules to TDD practices
- `WPBrowser` and `WPWebDriver` `loginAs` and `loginAsAdmin` methods will now return an array of access credentials and cookies to be used in requests

## [1.13.3] 2016-06-07
### Changed
- `WPTestCase` now extends `Codeception\Test\Unit` class

## [1.13.2] 2016-06-06
### Fixed
- Symlinker extension event hooking

## [1.13.1] 2016-06-06
### Fixed
- issue with Symlinker unlinking operation

## [1.13.0] 2016-06-03
### Changed
- updated code to follow `codeception/codeception` 2.2 update

## [1.12.0] 2016-06-01
### Added
- the `WPQueries` module

## [1.11.0] 2016-05-24
### Added
- `lucatume/codeception-setup-local` package requirement
- `wpcept setup` command shimming (from `lucatume/codeception-setup-local` package)
- `wpcept setup:scaffold` command shimming (from `lucatume/codeception-setup-local` package)
- `wpcept search-replace` command shimming (from `lucatume/codeception-setup-local` package)
- `wpdcept db:snapshot` command
- `lucatume/wp-browser-commons` package requirement

### Changed
- moved common code to `lucatume/wp-browser-commons` package

## [1.10.12] 2016-05-09
### Fixed
- `wpdb` reconnection procedure in WPBootstrapper module

## [1.10.11] 2016-05-05
### Added
- environments based support in `tad\WPBrowser\Extension\Symlinker` extension

## [1.10.10] 2016-05-04
### Added
- the `tad\WPBrowser\Extension\Symlinker` extension

### Changed
- update check deactivation when bootstrapping WordPress using the `WPBootstrapper` module
- updated core suite PHPUnit test files to latest version

## [1.10.9] 2016-05-03
### Fixed
- wrongly merged code from development version (thanks @crebacz for the prompt message!)
- warnings in `WPDb` module due to hasty use of array manipulation function

### Removed
- unreliable support for multisite scaffolding from WPDb module

## [1.10.8] 2016-05-02
### Fixed
- missing `blogs` table initialization on multisite installation tests with `WPLoader` module

## [1.10.7] 2016-03-30
### Fixed
- faulty active plugin option setting

## [1.10.6] 2016-03-30
### Fixed
- fixed db driver initialization in `WPDb::_cleanup` method

## [1.10.5] 2016-03-20
### Fixed
- plugin activation and deactivation related methods for WPBrowser and WPWebDriver modules (thanks @dimitrismitsis)

## [1.10.4] 2016-02-23
### Fixed
- `WPBootstrapper` module `wpdb` connection and re-connection process

## [1.10.3] 2016-02-22
### Added
- `WPBrowserMethods::amOnAdminPage` method, applies to WPWebDriver and WPBrowser modules
- `WPBootstrapper::setPermalinkStructureAndFlush` method 
- `WPBootstrapper::loadWpComponent` method 

## [1.10.0] 2016-02-18
### Modified
- the `WPBrowser` and `WpWebDriver` `activatePlugin` to use DOM in place of strings (l10n friendly)
- the `WPBrowser` and `WpWebDriver` `deactivatePlugin` to use DOM in place of strings (l10n friendly)

### Added
- the WPBootstrapper module

## [1.9.5] 2016-02-15
### Fixed
- wrong scaffolding structure when using the `wpcept bootstrap:pyramid command`

###Added
- the `wpunit` test suite to the ones scaffolded by default when using the `bootstrap:pyramid` command

## [1.9.4] 2016-01-20 
### Fixed
- proper name of `WPAjaxTestCase` class

## [1.9.3] 2016-01-20
### Added
- `wpunit` suite generation when using the `wpcept:bootstrap` command

### Changed
- provisional redirect status `301` to `302` in temporary `.htaccess` file used by `WPDb::haveMultisisiteInDatabase` method

### Removed
- `update` and `checkExistence` deprecated parameters from WPDb module

## [1.9.2] 2016-01-09
### Added
- the `$sleep` parameter to the `WPDb::haveMultisiteInDatabase` method
- missing `WPDb::$blogId` reset in cleanup method
- the `WPDb::useTheme` method
- the `WPDb::haveMenuInDatabase` method
- the `WPDb::haveMenuItemInDatabase` method
- the `WPDb::seeTermRelationshipInDat` method

## [1.9.1] 2016-01-07
### Fixed
- wrong table prefix in `WPDb::grabPrefixedTableNameFor` method for main blog when switching back to main blog.
### Removed
- the `WPDb::hitSite` method as not used anymore in code base.

## [1.9.0] 2015-12-23
### Changed
- the `WPDb::haveMultisiteInDatabase` method will now scaffold browser accessible multisite installations starting from a single site one
- WPDb module will drop tables created during multisite scaffolding

### Added
- `$autoload` parameter to `WPDb::haveOptionInDatabase` method
- `wpRootFolder` optional config parameter to the `WPDb` module

## [1.8.11] 2015-12-17
### Fixed
- added a check in embedded `bootstrap.php` file of WPLoader module for defined multisite vars

## [1.8.10] 2015-12-11
### Changed
- `WPTestCase` class now set the `$backupGlobals` to `false` by default
- removed default `$backupGlobals` value setting from test template

## [1.8.9] 2015-12-10
### Changed
- memory limit constants (`WP_MEMORY_LIMIT` and `WP_MAX_MEMORY_LIMIT`) will now check for pre-existing definitions in WPLoader module bootstrap

## [1.8.8] 2015-12-08
### Added
- blogs related methods to the WPDb module
- `haveMany` methods in WPDb module will now parse and compile [Handlebars PHP](https://github.com/XaminProject/handlebars.php "XaminProject/handlebars.php · GitHub") templates

### Changed
- renamed `haveMultisite` method to `haveMultisiteInDatabase` in WPDb module
### Removed
- `haveLinkWithTermInDatabase` method from WPDb module

## [1.8.7] 2015-12-07
### Added
- the `seeTableInDatabase` method to WPDb module
- the `haveMultisiteInDatabase` method to WPDb module
- multisite table `grabXTAbleName` methods to WPDb module

### Changed
- `havePostmetaInDatabase` method name to `havePostMetaInDatabase` in WPDb module

## [1.8.6] 2015-12-04
### Fixed
- issue with password validation in WPDb module

## [1.8.5] 2015-12-03
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

## [1.8.4] 2015-12-03
### Added
- `haveManyUsersInDatabase` method to WPDb module

### Changed
- links related methods in WPDb module

## [1.8.3] 2015-12-02
### Changed
- comments related methods in WPDb module

## [1.8.2] 2015-11-30
### Added
- terms related methods to WPDb module
- terms insertion capability to the `havePostInDatabase` and `haveManyPostsInDatabase` WPDb methods

## [1.8.1a] 2015-11-27
### Fixed
- fixed redundant logic in `WPDb::seeTermInDatabase` and `WPDb::dontSeeTermInDatabase` methods

## [1.8.1] 2015-11-27
### Changed
- reworked term related methods in WPDb module

## [1.8.0] 2015-11-26
### Added
- user and user meta related methods to the WPDb module
- options related methods to the WPDb module
- post and post meta related methods to the WPDb module

### Fixed
- duplicate call to globals definition in `install.php` file
- renamed file creating issues on with case sensitive systems (thanks @barryhuges)

### Changed
- some `seeInDatabase` method syntax

## [1.7.16a] 2015-11-18 
### Fixed
- the `_delete_all_posts` function in the automated tests bootstrap file now runs without any filters/actions hooked

## [1.7.15] 2015-11-17
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

## [1.7.14] 2015-11-10
### Fixed
- call to deprecated `delete` driver method in `ExtendedDb` module

## [1.7.13] 2015-11-10
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

## [1.7.12] 2015-11-6
### Changed
- code format

## [1.7.11] 2015-11-6
### Changed
- updated the test case class to latest from Core tests (thanks @zbtirell)

### Added
- the `waitForJQueryAjax` and `grabFullUrl` methods to the WPWebDriver module

## [1.7.10] 2015-11-5
### Changed
- modified WPLoader module compatibility check to allow for *Db modules `populate` setting

## [1.7.9] 2015-10-29
### Fixed
- config file search path in the WP Loader module

## [1.7.8] 2015-10-29
### Changed
- the `config_file` WP Loader module setting to `configFile`

## [1.7.7] 2015-10-22
### Changed
- the `WP_UnitTestCase` class bundled to extend `Codeception\Testcase\Test` class (thanks @borkweb)

## [1.7.6] 2015-10-21
### Fixed
- call to deprecated `set_current_user` function replaced with call to `wp_set_curren_user`

## [1.7.5] 2015-10-21
### Fixed
- missing `codecept_relative_path` function in `autoload.php` file (thanks @dbisso)

## [1.7.4] 2015-10-19
### Added
- plugin activation now happens with the current user set to the Administrator

### Changed
- modified the file structure
- the plugin activation hook of the WP Loader module to `wp_install` (thanks @barryhuges)

## [1.7.3] 2015-10-14
### Added
- the `pluginsFolder` setting to the WP Loader module

### Fixed
- issue with exception generation exception in WP Loader; did happen if a plugin was not found

### Changed
- some `WPLoader` methods visibility to allow for extension
- conditionally write lines to .gitignore to avoid duplicate entries(thanks @borkweb)

## [1.7.2] 2015-10-06
### Added
- an exception when a plugin file part of WPLoader `plugins` setting is not found
- the `activatePlugins` setting in WPLoader configuration

## [1.7.1] 2015-10-05
### Changed
- modifications/removals made to the `phpunit` element defined in the `phpunit.xml` file will be preserved across regenerations when using `wpcept generate:phpunitBootstrap` command.

## [1.7.0] 2015-10-05
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

## [1.6.19] - 2015-10-02
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

## [1.6.18] - 2015-10-01
### Added
- `config_file` WPLoader parameter

## [1.6.17] - 2015-09-30
### Added
- `plugins` WPLoader parameter
- `bootstrapActions` WPLoader parameter

## [1.6.16] - 2015-09-30
### Fixed
- Reference to ModuleConfigException class in WPLoader class.

[unreleased]: https://github.com/lucatume/wp-browser/compare/2.1.6...HEAD
[2.1.6]: https://github.com/lucatume/wp-browser/compare/2.1.5...2.1.6
[2.1.5]: https://github.com/lucatume/wp-browser/compare/2.1.4...2.1.5
[2.1.4]: https://github.com/lucatume/wp-browser/compare/2.1.3...2.1.4
[2.1.3]: https://github.com/lucatume/wp-browser/compare/2.1.2...2.1.3
[2.1.2]: https://github.com/lucatume/wp-browser/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/lucatume/wp-browser/compare/2.1...2.1.1
[2.1]: https://github.com/lucatume/wp-browser/compare/2.0.5.2...2.1
[2.0.5.2]: https://github.com/lucatume/wp-browser/compare/2.0.5.1...2.0.5.2
[2.0.5.1]: https://github.com/lucatume/wp-browser/compare/2.0.5...2.0.5.1
[2.0.5]: https://github.com/lucatume/wp-browser/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/lucatume/wp-browser/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/lucatume/wp-browser/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/lucatume/wp-browser/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/lucatume/wp-browser/compare/2.0...2.0.1
[2.0]: https://github.com/lucatume/wp-browser/compare/1.22.8...2.0
[1.22.8]: https://github.com/lucatume/wp-browser/compare/1.22.7.1...1.22.8
[1.22.7.1]: https://github.com/lucatume/wp-browser/compare/1.22.7...1.22.7.1
[1.22.7]: https://github.com/lucatume/wp-browser/compare/1.22.6.1...1.22.7
[1.22.6.1]: https://github.com/lucatume/wp-browser/compare/1.22.6...1.22.6.1
[1.22.6]: https://github.com/lucatume/wp-browser/compare/1.22.5...1.22.6
[1.22.5]: https://github.com/lucatume/wp-browser/compare/1.22.4...1.22.5
[1.22.4]: https://github.com/lucatume/wp-browser/compare/1.22.3...1.22.4
[1.22.3]: https://github.com/lucatume/wp-browser/compare/1.22.2...1.22.3
[1.22.2]: https://github.com/lucatume/wp-browser/compare/1.22.1...1.22.2
[1.22.1]: https://github.com/lucatume/wp-browser/compare/1.22.0...1.22.1
[1.22.0]: https://github.com/lucatume/wp-browser/compare/1.21.26...1.22.0
[1.21.26]: https://github.com/lucatume/wp-browser/compare/1.21.25...1.21.26
[1.21.25]: https://github.com/lucatume/wp-browser/compare/1.21.24...1.21.25
[1.21.24]: https://github.com/lucatume/wp-browser/compare/1.21.23...1.21.24
[1.21.23]: https://github.com/lucatume/wp-browser/compare/1.21.22...1.21.23
[1.21.22]: https://github.com/lucatume/wp-browser/compare/1.21.21...1.21.22
[1.21.21]: https://github.com/lucatume/wp-browser/compare/1.21.20...1.21.21
[1.21.20]: https://github.com/lucatume/wp-browser/compare/1.21.19...1.21.20
[1.21.19]: https://github.com/lucatume/wp-browser/compare/1.21.18...1.21.19
[1.21.18]: https://github.com/lucatume/wp-browser/compare/1.21.17...1.21.18
[1.21.17]: https://github.com/lucatume/wp-browser/compare/1.21.16...1.21.17
[1.21.16]: https://github.com/lucatume/wp-browser/compare/1.21.15...1.21.16
[1.21.15]: https://github.com/lucatume/wp-browser/compare/1.21.14...1.21.15
[1.21.14]: https://github.com/lucatume/wp-browser/compare/1.21.13...1.21.14
[1.21.13]: https://github.com/lucatume/wp-browser/compare/1.21.12...1.21.13
[1.21.12]: https://github.com/lucatume/wp-browser/compare/1.21.11...1.21.12
[1.21.11]: https://github.com/lucatume/wp-browser/compare/1.21.10...1.21.11
[1.21.10]: https://github.com/lucatume/wp-browser/compare/1.21.9...1.21.10
[1.21.9]: https://github.com/lucatume/wp-browser/compare/1.21.8...1.21.9
[1.21.8]: https://github.com/lucatume/wp-browser/compare/1.21.7...1.21.8
[1.21.7]: https://github.com/lucatume/wp-browser/compare/1.21.6...1.21.7
[1.21.6]: https://github.com/lucatume/wp-browser/compare/1.21.5...1.21.6
[1.21.5]: https://github.com/lucatume/wp-browser/compare/1.21.4...1.21.5
[1.21.4]: https://github.com/lucatume/wp-browser/compare/1.21.2...1.21.4
[1.21.3]: https://github.com/lucatume/wp-browser/compare/1.21.2...1.21.3
[1.21.2]: https://github.com/lucatume/wp-browser/compare/1.21.1...1.21.2
[1.21.1]: https://github.com/lucatume/wp-browser/compare/1.21.0...1.21.1
[1.21.0]: https://github.com/lucatume/wp-browser/compare/1.20.1...1.21.0
[1.20.1]: https://github.com/lucatume/wp-browser/compare/1.20.0...1.20.1
[1.20.0]: https://github.com/lucatume/wp-browser/compare/1.19.15...1.20.0
[1.19.15]: https://github.com/lucatume/wp-browser/compare/1.19.14...1.19.15
[1.19.14]: https://github.com/lucatume/wp-browser/compare/1.19.13...1.19.14
[1.19.13]: https://github.com/lucatume/wp-browser/compare/1.19.12...1.19.13
[1.19.12]: https://github.com/lucatume/wp-browser/compare/1.19.11...1.19.12
[1.19.11]: https://github.com/lucatume/wp-browser/compare/1.19.10...1.19.11
[1.19.10]: https://github.com/lucatume/wp-browser/compare/1.19.9...1.19.10
[1.19.9]: https://github.com/lucatume/wp-browser/compare/1.19.8...1.19.9
[1.19.8]: https://github.com/lucatume/wp-browser/compare/1.19.7...1.19.8
[1.19.7]: https://github.com/lucatume/wp-browser/compare/1.19.6...1.19.7
[1.19.6]: https://github.com/lucatume/wp-browser/compare/1.19.5...1.19.6
[1.19.5]: https://github.com/lucatume/wp-browser/compare/1.19.4...1.19.5
[1.19.4]: https://github.com/lucatume/wp-browser/compare/1.19.3...1.19.4
[1.19.3]: https://github.com/lucatume/wp-browser/compare/1.19.2...1.19.3
[1.19.3]: https://github.com/lucatume/wp-browser/compare/1.19.2...1.19.3
[1.19.2]: https://github.com/lucatume/wp-browser/compare/1.19.1...1.19.2
[1.19.1]: https://github.com/lucatume/wp-browser/compare/1.19.0...1.19.1
[1.19.0]: https://github.com/lucatume/wp-browser/compare/1.18.0...1.19.0
[1.18.0]: https://github.com/lucatume/wp-browser/compare/1.17.0...1.18.0
[1.17.0]: https://github.com/lucatume/wp-browser/compare/1.16.0...1.17.0
[1.16.0]: https://github.com/lucatume/wp-browser/compare/1.15.3...1.16.0
[1.15.3]: https://github.com/lucatume/wp-browser/compare/1.15.2...1.15.3
[1.15.2]: https://github.com/lucatume/wp-browser/compare/1.15.1...1.15.2
[1.15.1]: https://github.com/lucatume/wp-browser/compare/1.15.0...1.15.1
[1.15.0]: https://github.com/lucatume/wp-browser/compare/1.14.3...1.15.0
[1.14.3]: https://github.com/lucatume/wp-browser/compare/1.14.2...1.14.3
[1.14.2]: https://github.com/lucatume/wp-browser/compare/1.14.1...1.14.2
[1.14.1]: https://github.com/lucatume/wp-browser/compare/1.14.0...1.14.1
[1.14.0]: https://github.com/lucatume/wp-browser/compare/1.13.3...1.14.0
[1.13.3]: https://github.com/lucatume/wp-browser/compare/1.13.2...1.13.3
[1.13.2]: https://github.com/lucatume/wp-browser/compare/1.13.1...1.13.2
[1.13.1]: https://github.com/lucatume/wp-browser/compare/1.13.0...1.13.1
[1.13.0]: https://github.com/lucatume/wp-browser/compare/1.12.0...1.13.0
[1.12.0]: https://github.com/lucatume/wp-browser/compare/1.11.0...1.12.0
[1.11.0]: https://github.com/lucatume/wp-browser/compare/1.10.12...1.11.0
[1.10.12]: https://github.com/lucatume/wp-browser/compare/1.10.11...1.10.12
[1.10.11]: https://github.com/lucatume/wp-browser/compare/1.10.10...1.10.11
[1.10.10]: https://github.com/lucatume/wp-browser/compare/1.10.9...1.10.10
[1.10.9]: https://github.com/lucatume/wp-browser/compare/1.10.8...1.10.9
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
