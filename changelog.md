# Change Log
All notable changes after version 1.6.16 to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

##[unreleased] Unreleased
### Fixed
- duplicate call to globals definition in `install.php` file
- renamed file creating issues on with case sensitive systems (thanks @barryhuges)

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

[unreleased]: https://github.com/lucatume/wp-browser/compare/1.7.16a...HEAD
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
