# Change Log
All notable changes after version 1.6.16 to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [unreleased] Unreleased

## [2.6.17] 2020-11-17;

### Fixed

- URL replacement issue in `DbDump` class

## [2.6.16] 2020-10-26;

### Fixed

- handle more Cookie types in `WPBrowser::grabCookiesWithPattern` method to avoid errors.

### Changed

- change DotEnv suggestion to  `vlucas/phpdotenv:^4.0` to avoid env vars loading issues.

## [2.6.15] 2020-10-21;

### Fixed

- avoid deleting the whole plugins folder when `havePlugin` is used to create single file plugins (thanks @Luc45)

## [2.6.14] 2020-10-20;

### Added

- the `logOut(string|bool $redirectTo)` method to the `WPBrowser` and `WPWebDriver` modules (thanks @gabe-connolly)

## [2.6.13] 2020-09-22;

### Fixed

- restore full phpstan lvl 8 coverage

## [2.6.12] 2020-09-17;

### Changed

- rename the `rmkdir` function to `mkdirp` for clarity and resemblance with the WordPress one.

## [2.6.11] 2020-09-15;

### Fixed

- an issue where the `recurseRemoveDir` function would leave empty directories behind, fixes #447

### Added

- the `mkdirp` function to scaffold nested directory structures and files

## [2.6.10] 2020-08-25;

### Fixed

- remove version block on `symfony/filesystem` dependency, fixes #440

## [2.6.9] 2020-08-19;

### Added

- the `WPDb::importSql` method to allow importing custom SQL strings in the database during tests

## [2.6.8] 2020-08-19;

### Fixed

- avoid deprecation notices when loading `MockPHPMailer`, fixes #436

## [2.6.7] 2020-08-14;

### Added

- the `WPDb::havePostThumbnailInDatabase` and `WPDb::dontHavePostThumbnailInDatabase` methods, fixes #434

## [2.6.6] 2020-08-04;

### Fixed

- URL replacement function in `WPDb` module that would incorrectly handling the replacement of `locahost:port` URLs, fixes #430

## [2.6.5] 2020-07-16;

### Fixed

- return type of `WPLoader::factory` method to ensure IDE type-hinting will work correctly (thanks @Luc45)

## [2.6.4] 2020-07-07;

### Fixed

- typos and spacing in documentation (thanks @cliffordp)
- environment file parsing for empty values, fixes #427

## [2.6.3] 2020-06-30;

### Fixed

- An issue where additional required plugins would not be correctly parsed during the `init wpbrowser` command, fixes #424

## [2.6.2] 2020-06-19;

### Fixed

- An issue where users created during tests would not be have the correct editing and layout meta set, fixes #422, thanks @ryanshoover  

## [2.6.1] 2020-06-11;

### Fixed

- ensure `$_SERVER['REQUEST_TIME']` and `$_SERVER['REQUEST_TIME_FLOAT']` are correctly set when running tests based on the `Codeception\Test\WPTestCase` class, fixes #417

## [2.6.0] 2020-06-08;

### Added

- support, in `WPTestCase`, for the `@runInSeparateProcess` annotation to run test methods in separate PHP processes; fixes #410

## [2.5.7] 2020-06-02;

### Fixed

- Codeception required version erroneously set in prev version of `composer.json` file

## [2.5.6] 2020-06-02;

### Added

- check in the `codecept init wpbrowser` command to check and report missing Codeception 4.0 to the user during initialization, fixes #412

## [2.5.5] 2020-05-25;

### Changed

- refactoring to pass `phpstan` level `1` and `2` checks.

## [2.5.4] 2020-05-22;

### Fixed

- an issue with .env files handling that, when the `vlucas/phpdotenv` package is not required, would incorrectly set up the test environment.

### Changed

- some refactoring to pass `phpstan` level `0` checks.

## [2.5.3] 2020-05-15;

### Added

- the `WPDb.letCron` configuration parameter to control whether `wp-cron` processes should be allowe to spawn during tests or not (new default).

### Fixed

- the `WPDb` module will set up the database to prevent `wp-cron` requests from being spawned during tests, fixes #363.
- env file parsing issues reported, fixes #398.

### Changed

- following changes to how the `WPDb` module sets up the database at the start of tests (and between tests), `wp-cron` process will not be spawned during tests unless the `WPDb.letCron` configuration parameter is set to `true`.

## [2.5.2] 2020-05-13;

### Fixed

- added `Dotenv\Dotenv` polyfill class to avoid back-compatibility issues w/ projects not requiring `vlucas/phpdotenv` explicitly and using env files for tests configuration.

## [2.5.1] 2020-05-13;

### Fixed

- add `function_exists` check to avoid redefinition issues when wp-browser is used in two related packages (thanks @cliffordp)

## [2.5.0] 2020-05-11;

### Fixed

* `README.md` file updates (thanks @szepeviktor)
* `src/tad/scripts` fixes and refactorings (thanks @szepeviktor)
* cron and admin AJAX query vars handling (thanks @Luc45)

### Removed

* `gumlet/php-image-resize` package requirement; runtime image modification in the `WPDb::haveAttachmentInDatabase` method will require it at runtime.
* `vlucas/phpdotenv` package requirement; `wp-browser` will not use it internally, but Codeception will keep requiring it for dynamic parameter configuration.

## [2.4.8] 2020-05-01;

### Fixed

- initialization environment vars in the `Wpbrowser` template providing functions for the `codecept init wpbrowser` command

## [2.4.7] 2020-04-23;

### Fixed

- support for Unix sockets in `WPDB` and `WPLoader` modules

### Changed

- the `codecept init wpbrowser` will now scaffold the suites to support both classic MySQL hosts like `1.2.3.4:3306`, container-type hosts like `db` and Unix socket hosts like `localhost:/var/mysql.sock`

## [2.4.6] 2020-04-20;

### Fixed

- PHP 5.6 incompatibility issues introduced in version `2.4.0`, fixes #372

## [2.4.5] 2020-04-15;

### Added

- set the `admin_email_lifespan` option value to prevent showing the administration email verification in the `WPDb` module, after the database is imported; fixes #358
- `WPDb::EVENT_AFTER_DB_PREPARE` action after an imported datababse is prepared by applying quality-of-testing-life "patches" to the database

## [2.4.4] 2020-04-14;

### Added

- clearer messaging for missing Codeception 4.0 modules in wp-browser modules requiring it, fixes #365 and #360

## [2.4.3] 2020-04-13;

### Fixed

- suites configuration parameter handling in the `tad\WPBrowser\Extension\Events` extension.

## [2.4.2] 2020-04-11;

### Added

- support for the `contentFolder` parameter in the `WPLoader` configuration. This is the equivalent of setting the `WP_CONTENT_DIR` constant in a custom configuration file, fixed #342

### Removed

- internal `tad\WPBrowser\Filesystem\Utils` class in favour of `filesystem` functions

## [2.4.1] 2020-04-10;

### Changed

- an issue where tables created by plugins during the WordPress installation managed by the `WPLoader` module would be dropped; default behaviour changed to emptying the tables, fixes #356

## [2.4.0] 2020-04-10;

### Added

- compatibility with Codeception 4.0
- the `tad\WPBrowser\Extension\Events` extension to enable subscribing to Codeception 4.0 events

### Fixed

- the event listener and dispatcher system to work consistently across Codeception versions
- issue where `WPDb::haveUserInDatabase` method would not create all the user meta #359

## [2.3.4] 2020-04-03;

### Fixed

- add deprecated functions handling for functions moved in version `2.3`
- mark `rrmdir` function as deprecated

## [2.3.3] 2020-04-01;

### Fixed

- `tad\WPBrowser\vendorDir` issue that would cause the function to return wrong value

## [2.3.2] 2020-03-29;

### Fixed

- absolute paths handling in the `configFile` parameter of `WPLoader` configuration

## [2.3.1] 2020-03-29;

### Fixed

- absolute paths handling in the `pluginsFolder` parameter of `WPLoader` configuration

## [2.3.0] 2020-03-29;

### Added

- the `originalUrl` to the `WPDb` module configuration; this can help in some instances where `urlReplacement` is active but is not working correctly.
- the `tad\WPBrowser\Traits\WithWordPressFilters` trait to provide methods, for test cases, to debug WordPress actions and filter initial and final values.
- use the `tad\WPBrowser\Traits\WithWordPressFilters` trait in the `WPLoader` module to debug WordPress actions and filter initial and final values.

### Fixed

- an issue that would prevent the site URL from being correctly replaced during `WPDb` module dump imports
- sanity checks on the `Copier` extension


### Changed

- refactoring of utility functions
- the build system from Travis CI to GitHub Actions, based on Docker

## [2.2.37] 2020-02-21;

### Fixed

- issue in the `WithEvents` trait that would cause issues in the `console` command

## [2.2.36] 2020-02-19;

### Fixed

- issue with `WPDb` and `WPLoader` module in `loadOnly` mode that would cause WPLoader to load WordPress before the correct database setup

## [2.2.35] 2020-02-13;

### Fixed

- issue with setup default values where the default environment file name would be empty or the file would be missing

## [2.2.34] 2020-01-29;

### Fixed

- cache flushing issue in `WPTestCase` (thanks @mitogh)

## [2.2.33] 2019-12-18;

### Fixed
- `WPDb::haveUserCapabilitiesInDatabase` to make sure entries are not duplicated when called on same user [#335]

### Updated
- `WPDb::haveUserInDatabase` and `WPDb::haveUserCapabilitiesInDatabase` methods to support more complex user role assignment [#336]

## [2.2.32] 2019-11-26;
### Fixed
- `WPRestControllerTestCase` issue (thanks @TimothyBJacobs)
- wrong theme detection in healthcheck [#328]
- `WPDb::haveUserInDatabase` issue where the sanitization would be stricter than the WordPress one on user login [#332]

## [2.2.31] 2019-10-22;
### Fixed
- documentation generation issue [#323]
- missing `WPWebDriver` configuration and example configuration sections
- `setUp`, `tearDown` and `setUpBeforeClass` issues with test cases [#325]

## [2.2.30] 2019-10-14;
### Added
- `dontSeeInShellOutput`, `seeInShellOutput`, `seeResultCodeIs`, `seeResultCodeIsNot` and `seeShellOutputMatches` methods to `WPCLI` module (thanks @TimothyBJacobs)b

## [2.2.29] 2019-09-24;
### Fixed
- `wpbrowser` template class to make sure the environment file name is respected when set to different values
- some `wpbrowser` template text
- output of `WPCLI` module when exit code is `0` and there are both `stdout` and `stderr` outputs, fixes #316

## [2.2.28] 2019-09-19;
### Fixed
- `WPCLI` module: do not mark command as failed, thus do not throw, if exit code is `0`, fixes #312

## [2.2.27] 2019-09-17;
### Fixed
- command line parsing issue in the `WPCLI` module, fixes #310

## [2.2.26] 2019-09-17;
### Fixed
- double class definition issues dealing with Codeception and PHPUnit versions

## [2.2.25] 2019-09-13;
### Changed
- smaller refactoring to avoid introducing `STATIC_ANALYSIS` environment var in code (thanks @szepeviktor)

### Fixed
- restore Codeception `2.5` and `3.0` support in `composer.json` file erroneously removed in `2.2.24`

## [2.2.24] 2019-09-06;
### Changed
- a number of refactorings and fixings following addition of `phpstan`, fixes #291 (thanks @szepeviktor)

## [2.2.23] 2019-09-06;
### Fixed
- smaller adjustment to `slug` function and number handling

## [2.2.22] 2019-09-05;
### Added
- support for environment variables to the `WPCLI` module, fixes #299 (thanks @TimothyBJacobs)

## [2.2.21] 2019-09-04;
### Added
- the `tad\WPBrowser\slug` function to create the slug version of a string
- the `tad\WPBrowser\buildCommandLine` function to generate a Symfony Process compatible command line (array format) from a string one
- the `tad\WPBrowser\renderString` function to render a string in the Handlebars format from data
- the `WPCLI::cliToString` method to get a wp-cli command output as string, fixes #297 (thanks @TimothyBJacobs)

### Changed
- removed the `bacon/bacon-string-utils` dependency
- added support for custom extra arguments to the `WPCLI` module, see documentation, fixes #295 (thanks @TimothyBJacobs)
- replaced the `xamin/handlebars.php` dependency with the `zordius/lightncandy` one

### Fixed
- lazy evaluation of the WordPress path in `WPCLI` module, fixes #294 (thanks @TimothyBJacobs)
- correct evaluation of command exit status in `WPCLI` module, fixes #296 (thanks @TimothyBJacobs)

## [2.2.20] 2019-08-26;
### Fixed
- ignore foreign key checks when dropping tables in isolated install (thanks @TimothyBJacobs)

## [2.2.19] 2019-08-16;
### Fixed
- enhancements to the build scripts (thanks @karser)
- avoid redefining constants during `WPLoader` module bootstrap

## [2.2.18] 2019-08-02;
### Fixed
- `WPLoader` module: load WordPress on `SUITE_INIT` when only loading (#283)

## [2.2.17] 2019-07-31;
### Fixed
- eager instantiation of WordPress factories causing warnings (#281)

## [2.2.16] 2019-07-23;
### Added
- expose a `tad\WPBrowser\Module\WPLoader\FactoryStore` instance on the `$tester` property when loading the `WPLoader` module with `loadOnly: false`.

### Fixed
- add explicit override of the `$tester` property when scaffolding the `WPTestCase` class (thanks @Luc45)

## [2.2.15] 2019-06-28;
### Fixed
- call `Codeception\Test\Unit` setup methods in `WPTestCase` to provide Codeception Unit testing facilities in "WordPress unit" test cases

## [2.2.14] 2019-06-14;
### Fixed
- remove left-over `%WP_URL%` from generated configuration files when runnin `codecept init wpbrowser` and replace it with `%TEST_SITE_WP_URL%` (thanks @HendrikRoehm)

## [2.2.13] 2019-06-13;
### Fixed
- when the `WPLoader` module is set to `loadOnly` mode and used in conjunction with a `*Db` module delay its load after all other modules ran their `_beforeSuite` action; this tackles an issue only partially resolved in `2.2.8` (thanks @Luc45)

## [2.2.12] 2019-06-10;
### Fixed
- make sure Cron is disabled while `WPLoader` module is installing WordPress in isolation (default mode)

## [2.2.11] 2019-06-06;
## Added
- support for `timeout` parameter in WPCLI module configuration

## [2.2.10] 2019-06-03;
### Added
- when in debug mode the `WPLoader` module will now display a report about the bootstrapped WordPress installation

### Fixed
- restored the `Codeception\Test\Unit` class as parent of the `\Codeception\TestCase\WPTestCase`; it was erroneously removed from the inheritance tree in 2.2.7

## [2.2.9] 2019-05-24;
### Fixed
- catch unlink errors in the `Symlinker` extension (thanks @halmos)
- fix `WPTestCase` template generation to scaffold PHPUnit `8.0+` compatible code (thanks @halmos)

### Changed
- updated the documentation to cover some more frequent questions and issues about WPDb

## [2.2.8] 2019-05-20;
### Changed
- updated requirement of `codeception/codeception` to include version `3.0`
- updated `WPTestCase` to handle PHPUnit version `8.0+` compatibility
- internalized the classes and functions provided by the `lucatume/wp-browser-commons` package

### Fixed
- initialize the WPLoader module after all other modules initialized when `loadOnly` is `true` to avoid WordPress exiting due to a non-initialized database fixture

## [2.2.7] 2019-05-08;
### Changed
- replaced `wp-cli/wp-cli:1.1.*` dependecy with the `wp-cli/wp-cli-bundle:^2.0` one

## [2.2.6] 2019-05-07;
### Added
- informative debug to try and provide guidance and information when the `WPLoader` and `WPDb` modules are used together and WordPress dies suddenly.
- the `WPDb::dontSeePostWithTermInDatabase` method (#230, thanks @jcabot)

### Fixed
- the `WPDb::seePostWithTermInDatabase` method logic (#230, thanks @jcabot)


## [2.2.5] 2019-04-22;
### Added
- allow opening PHP tags in the code arguments of the `WPFilesystem::havePlugin`, `WPFilesystem::haveMuPlugin` and `WPFilesystem::haveTheme` methods.

## [2.2.4] 2019-04-19;
### Fixed
- a mu-plugins path issue in the `WPFilesystem` module.

## [2.2.3] 2019-04-19;
### Added
- first version of the documentaion
- fixed an issue where the initialization template would not correctly set the placeholder names (thanks @Luc45)
- methods `getQueries` and `countQueries` to the `WPQueries` module

## [2.2.2] 2019-04-14;
### Fixed
- an issue in `src/includes/utils.php` (thanks @lots0logs)
- an issue with `WPDb` URL replacement functionality that would prevent it from working with the dump format of some applications

## [2.2.1] 2019-02-11;
### Changed
- updated `vlucas/phpdotenv` library dependency to `^3.0` (thanks @Naktibalda)

## [2.2.0] 2018-11-29;
### Removed
- the `haveOrUpdateInDatabase` method from `WPDb` module
- the `ExtendedDb` class
- the requirement of the `lucatume/wp-snaphot-assertions` package
- the `\tad\WPBrowser\Snapshot\WPHtmlOutputDriver` class
- the `wpcept` binaries
- `lucatume/wp-snapshot-assertions` dependency
- `tad\WPBrowser\Snapshot\WPHtmlOutputDriver` proxy class and  the `lucatume/wp-snapshot-assertions` dependency
- `wpcept` and `wpcept.bat` deprecated binaries
- `Codeception\Command\DbSnapshot` command
- `tad\Codeception\Command\SearchReplace` command from the template
- `lucatume/codeception-setup-local` dependency
- the `WPBootstrapper` module
- the `WPSugarMethods` trait

### Fixed
- compatibility with Codeception `2.5.0` updating the `WPDb` class
- added a clear disclaimer about db wiping in the `init wpbrowser` command
- an issue where the WpWebDriver module would not login correctly [#121](https://github.com/lucatume/wp-browser/pull/121)
- code style compatibilty with PSR-2 standard
- an issue in the `tad\WPBrowser\Tests\Support\importDump` function that would prevent the function from working if the database host specified a port
- an issue with end-of-line chars in tests on Windows [#191](https://github.com/lucatume/wp-browser/pull/191) - thanks @Luc45

### Changed
- lowered the PHP required version from 7.0 to 5.6
- PHP requirement lowered to PHP 5.6
- removed the limit to Codeception version
- Travis tests run now on a Docker stack

### Added
- add a `.gitattributes` file to stop littering people's vendor library (sorry, my bad)
- support for relative paths in the `wpRootFolder` parameter
- the `WPCLI` and `WordPress` modules will now set the `WPBROWSER_HOST_REQUEST=1` environment variable; this can be used to discern requests coming not only from wp-cli, using the `WP_CLI` constant, but from a wp-cli instance used and managed by the `WPCLI` module.

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

[1.6.16]: https://github.com/lucatume/wp-browser/compare/1.6.15...1.6.16
[1.6.17]: https://github.com/lucatume/wp-browser/compare/1.6.16...1.6.17
[1.6.18]: https://github.com/lucatume/wp-browser/compare/1.6.17...1.6.18
[1.6.19]: https://github.com/lucatume/wp-browser/compare/1.6.18...1.6.19
[1.7.0]: https://github.com/lucatume/wp-browser/compare/1.6.19...1.7.0
[1.7.1]: https://github.com/lucatume/wp-browser/compare/1.7.0...1.7.1
[1.7.2]: https://github.com/lucatume/wp-browser/compare/1.7.1...1.7.2
[1.7.3]: https://github.com/lucatume/wp-browser/compare/1.7.2...1.7.3
[1.7.4]: https://github.com/lucatume/wp-browser/compare/1.7.3...1.7.4
[1.7.5]: https://github.com/lucatume/wp-browser/compare/1.7.4...1.7.5
[1.7.6]: https://github.com/lucatume/wp-browser/compare/1.7.5...1.7.6
[1.7.7]: https://github.com/lucatume/wp-browser/compare/1.7.6...1.7.7
[1.7.8]: https://github.com/lucatume/wp-browser/compare/1.7.8...1.7.8
[1.7.9]: https://github.com/lucatume/wp-browser/compare/1.7.8...1.7.9
[1.7.10]: https://github.com/lucatume/wp-browser/compare/1.7.9...1.7.10
[1.7.11]: https://github.com/lucatume/wp-browser/compare/1.7.10...1.7.11
[1.7.12]: https://github.com/lucatume/wp-browser/compare/1.7.11...1.7.12
[1.7.13c]: https://github.com/lucatume/wp-browser/compare/1.7.12...1.7.13c
[1.7.14]: https://github.com/lucatume/wp-browser/compare/1.7.13c...1.7.14
[1.7.15]: https://github.com/lucatume/wp-browser/compare/1.7.14...1.7.15
[1.7.16a]: https://github.com/lucatume/wp-browser/compare/1.7.15...1.7.16a
[1.8.0]: https://github.com/lucatume/wp-browser/compare/1.7.16a...1.8.0
[1.8.1]: https://github.com/lucatume/wp-browser/compare/1.8.0...1.8.1
[1.8.1a]: https://github.com/lucatume/wp-browser/compare/1.8.1...1.8.1a
[1.8.2]: https://github.com/lucatume/wp-browser/compare/1.8.1a...1.8.2
[1.8.3]: https://github.com/lucatume/wp-browser/compare/1.8.2...1.8.3
[1.8.4]: https://github.com/lucatume/wp-browser/compare/1.8.3...1.8.4
[1.8.5]: https://github.com/lucatume/wp-browser/compare/1.8.4...1.8.5
[1.8.6]: https://github.com/lucatume/wp-browser/compare/1.8.5...1.8.6
[1.8.7]: https://github.com/lucatume/wp-browser/compare/1.8.6...1.8.7
[1.8.8]: https://github.com/lucatume/wp-browser/compare/1.8.7...1.8.8
[1.8.9]: https://github.com/lucatume/wp-browser/compare/1.8.8...1.8.9
[1.8.9]: https://github.com/lucatume/wp-browser/compare/1.8.8...1.8.9
[1.8.10]: https://github.com/lucatume/wp-browser/compare/1.8.9...1.8.10
[1.8.11]: https://github.com/lucatume/wp-browser/compare/1.8.10...1.8.11
[1.9.0]: https://github.com/lucatume/wp-browser/compare/1.8.11...1.9.0
[1.9.1]: https://github.com/lucatume/wp-browser/compare/1.9.0...1.9.1
[1.9.2]: https://github.com/lucatume/wp-browser/compare/1.9.1...1.9.2
[1.9.3]: https://github.com/lucatume/wp-browser/compare/1.9.2...1.9.3
[1.9.4]: https://github.com/lucatume/wp-browser/compare/1.9.3...1.9.4
[1.9.5]: https://github.com/lucatume/wp-browser/compare/1.9.4...1.9.5
[1.10.0]: https://github.com/lucatume/wp-browser/compare/1.9.5...1.10.0
[1.10.3]: https://github.com/lucatume/wp-browser/compare/1.10.0...1.10.3
[1.10.4]: https://github.com/lucatume/wp-browser/compare/1.10.3...1.10.4
[1.10.5]: https://github.com/lucatume/wp-browser/compare/1.10.4...1.10.5
[1.10.6]: https://github.com/lucatume/wp-browser/compare/1.10.5...1.10.6
[1.10.7]: https://github.com/lucatume/wp-browser/compare/1.10.6...1.10.7
[1.10.8]: https://github.com/lucatume/wp-browser/compare/1.10.7...1.10.8
[1.10.9]: https://github.com/lucatume/wp-browser/compare/1.10.8...1.10.9
[1.10.10]: https://github.com/lucatume/wp-browser/compare/1.10.9...1.10.10
[1.10.11]: https://github.com/lucatume/wp-browser/compare/1.10.10...1.10.11
[1.10.12]: https://github.com/lucatume/wp-browser/compare/1.10.11...1.10.12
[1.11.0]: https://github.com/lucatume/wp-browser/compare/1.10.12...1.11.0
[1.12.0]: https://github.com/lucatume/wp-browser/compare/1.11.0...1.12.0
[1.13.0]: https://github.com/lucatume/wp-browser/compare/1.12.0...1.13.0
[1.13.1]: https://github.com/lucatume/wp-browser/compare/1.13.0...1.13.1
[1.13.2]: https://github.com/lucatume/wp-browser/compare/1.13.1...1.13.2
[1.13.3]: https://github.com/lucatume/wp-browser/compare/1.13.2...1.13.3
[1.14.0]: https://github.com/lucatume/wp-browser/compare/1.13.3...1.14.0
[1.14.1]: https://github.com/lucatume/wp-browser/compare/1.14.0...1.14.1
[1.14.2]: https://github.com/lucatume/wp-browser/compare/1.14.1...1.14.2
[1.14.3]: https://github.com/lucatume/wp-browser/compare/1.14.2...1.14.3
[1.15.0]: https://github.com/lucatume/wp-browser/compare/1.14.3...1.15.0
[1.15.1]: https://github.com/lucatume/wp-browser/compare/1.15.0...1.15.1
[1.15.2]: https://github.com/lucatume/wp-browser/compare/1.15.1...1.15.2
[1.15.3]: https://github.com/lucatume/wp-browser/compare/1.15.2...1.15.3
[1.16.0]: https://github.com/lucatume/wp-browser/compare/1.15.3...1.16.0
[1.17.0]: https://github.com/lucatume/wp-browser/compare/1.16.0...1.17.0
[1.18.0]: https://github.com/lucatume/wp-browser/compare/1.17.0...1.18.0
[1.19.0]: https://github.com/lucatume/wp-browser/compare/1.18.0...1.19.0
[1.19.1]: https://github.com/lucatume/wp-browser/compare/1.19.0...1.19.1
[1.19.2]: https://github.com/lucatume/wp-browser/compare/1.19.1...1.19.2
[1.19.3]: https://github.com/lucatume/wp-browser/compare/1.19.2...1.19.3
[1.19.3]: https://github.com/lucatume/wp-browser/compare/1.19.2...1.19.3
[1.19.4]: https://github.com/lucatume/wp-browser/compare/1.19.3...1.19.4
[1.19.5]: https://github.com/lucatume/wp-browser/compare/1.19.4...1.19.5
[1.19.6]: https://github.com/lucatume/wp-browser/compare/1.19.5...1.19.6
[1.19.7]: https://github.com/lucatume/wp-browser/compare/1.19.6...1.19.7
[1.19.8]: https://github.com/lucatume/wp-browser/compare/1.19.7...1.19.8
[1.19.9]: https://github.com/lucatume/wp-browser/compare/1.19.8...1.19.9
[1.19.10]: https://github.com/lucatume/wp-browser/compare/1.19.9...1.19.10
[1.19.11]: https://github.com/lucatume/wp-browser/compare/1.19.10...1.19.11
[1.19.12]: https://github.com/lucatume/wp-browser/compare/1.19.11...1.19.12
[1.19.13]: https://github.com/lucatume/wp-browser/compare/1.19.12...1.19.13
[1.19.14]: https://github.com/lucatume/wp-browser/compare/1.19.13...1.19.14
[1.19.15]: https://github.com/lucatume/wp-browser/compare/1.19.14...1.19.15
[1.20.0]: https://github.com/lucatume/wp-browser/compare/1.19.15...1.20.0
[1.20.1]: https://github.com/lucatume/wp-browser/compare/1.20.0...1.20.1
[1.21.0]: https://github.com/lucatume/wp-browser/compare/1.20.1...1.21.0
[1.21.1]: https://github.com/lucatume/wp-browser/compare/1.21.0...1.21.1
[1.21.2]: https://github.com/lucatume/wp-browser/compare/1.21.1...1.21.2
[1.21.3]: https://github.com/lucatume/wp-browser/compare/1.21.2...1.21.3
[1.21.4]: https://github.com/lucatume/wp-browser/compare/1.21.2...1.21.4
[1.21.5]: https://github.com/lucatume/wp-browser/compare/1.21.4...1.21.5
[1.21.6]: https://github.com/lucatume/wp-browser/compare/1.21.5...1.21.6
[1.21.7]: https://github.com/lucatume/wp-browser/compare/1.21.6...1.21.7
[1.21.8]: https://github.com/lucatume/wp-browser/compare/1.21.7...1.21.8
[1.21.9]: https://github.com/lucatume/wp-browser/compare/1.21.8...1.21.9
[1.21.10]: https://github.com/lucatume/wp-browser/compare/1.21.9...1.21.10
[1.21.11]: https://github.com/lucatume/wp-browser/compare/1.21.10...1.21.11
[1.21.12]: https://github.com/lucatume/wp-browser/compare/1.21.11...1.21.12
[1.21.13]: https://github.com/lucatume/wp-browser/compare/1.21.12...1.21.13
[1.21.14]: https://github.com/lucatume/wp-browser/compare/1.21.13...1.21.14
[1.21.15]: https://github.com/lucatume/wp-browser/compare/1.21.14...1.21.15
[1.21.16]: https://github.com/lucatume/wp-browser/compare/1.21.15...1.21.16
[1.21.17]: https://github.com/lucatume/wp-browser/compare/1.21.16...1.21.17
[1.21.18]: https://github.com/lucatume/wp-browser/compare/1.21.17...1.21.18
[1.21.19]: https://github.com/lucatume/wp-browser/compare/1.21.18...1.21.19
[1.21.20]: https://github.com/lucatume/wp-browser/compare/1.21.19...1.21.20
[1.21.21]: https://github.com/lucatume/wp-browser/compare/1.21.20...1.21.21
[1.21.22]: https://github.com/lucatume/wp-browser/compare/1.21.21...1.21.22
[1.21.23]: https://github.com/lucatume/wp-browser/compare/1.21.22...1.21.23
[1.21.24]: https://github.com/lucatume/wp-browser/compare/1.21.23...1.21.24
[1.21.25]: https://github.com/lucatume/wp-browser/compare/1.21.24...1.21.25
[1.21.26]: https://github.com/lucatume/wp-browser/compare/1.21.25...1.21.26
[1.22.0]: https://github.com/lucatume/wp-browser/compare/1.21.26...1.22.0
[1.22.1]: https://github.com/lucatume/wp-browser/compare/1.22.0...1.22.1
[1.22.2]: https://github.com/lucatume/wp-browser/compare/1.22.1...1.22.2
[1.22.3]: https://github.com/lucatume/wp-browser/compare/1.22.2...1.22.3
[1.22.4]: https://github.com/lucatume/wp-browser/compare/1.22.3...1.22.4
[1.22.5]: https://github.com/lucatume/wp-browser/compare/1.22.4...1.22.5
[1.22.6]: https://github.com/lucatume/wp-browser/compare/1.22.5...1.22.6
[1.22.6.1]: https://github.com/lucatume/wp-browser/compare/1.22.6...1.22.6.1
[1.22.7]: https://github.com/lucatume/wp-browser/compare/1.22.6.1...1.22.7
[1.22.7.1]: https://github.com/lucatume/wp-browser/compare/1.22.7...1.22.7.1
[1.22.8]: https://github.com/lucatume/wp-browser/compare/1.22.7.1...1.22.8
[2.0]: https://github.com/lucatume/wp-browser/compare/1.22.8...2.0
[2.0.1]: https://github.com/lucatume/wp-browser/compare/2.0...2.0.1
[2.0.2]: https://github.com/lucatume/wp-browser/compare/2.0.1...2.0.2
[2.0.3]: https://github.com/lucatume/wp-browser/compare/2.0.2...2.0.3
[2.0.4]: https://github.com/lucatume/wp-browser/compare/2.0.3...2.0.4
[2.0.5]: https://github.com/lucatume/wp-browser/compare/2.0.4...2.0.5
[2.0.5.1]: https://github.com/lucatume/wp-browser/compare/2.0.5...2.0.5.1
[2.0.5.2]: https://github.com/lucatume/wp-browser/compare/2.0.5.1...2.0.5.2
[2.1]: https://github.com/lucatume/wp-browser/compare/2.0.5.2...2.1
[2.1.1]: https://github.com/lucatume/wp-browser/compare/2.1...2.1.1
[2.1.2]: https://github.com/lucatume/wp-browser/compare/2.1.1...2.1.2
[2.1.3]: https://github.com/lucatume/wp-browser/compare/2.1.2...2.1.3
[2.1.4]: https://github.com/lucatume/wp-browser/compare/2.1.3...2.1.4
[2.1.5]: https://github.com/lucatume/wp-browser/compare/2.1.4...2.1.5
[2.1.6]: https://github.com/lucatume/wp-browser/compare/2.1.5...2.1.6
[2.2.0]: https://github.com/lucatume/wp-browser/compare/2.1.6...2.2.0
[2.2.1]: https://github.com/lucatume/wp-browser/compare/2.2.0...2.2.1
[2.2.2]: https://github.com/lucatume/wp-browser/compare/2.2.1...2.2.2
[2.2.3]: https://github.com/lucatume/wp-browser/compare/2.2.2...2.2.3
[2.2.4]: https://github.com/lucatume/wp-browser/compare/2.2.3...2.2.4
[2.2.5]: https://github.com/lucatume/wp-browser/compare/2.2.4...2.2.5
[2.2.6]: https://github.com/lucatume/wp-browser/compare/2.2.5...2.2.6
[2.2.7]: https://github.com/lucatume/wp-browser/compare/2.2.6...2.2.7
[2.2.8]: https://github.com/lucatume/wp-browser/compare/2.2.7...2.2.8
[2.2.9]: https://github.com/lucatume/wp-browser/compare/2.2.8...2.2.9
[2.2.10]: https://github.com/lucatume/wp-browser/compare/2.2.9...2.2.10
[2.2.11]: https://github.com/lucatume/wp-browser/compare/2.2.10...2.2.11
[2.2.12]: https://github.com/lucatume/wp-browser/compare/2.2.11...2.2.12
[2.2.13]: https://github.com/lucatume/wp-browser/compare/2.2.12...2.2.13
[2.2.14]: https://github.com/lucatume/wp-browser/compare/2.2.13...2.2.14
[2.2.15]: https://github.com/lucatume/wp-browser/compare/2.2.14...2.2.15
[2.2.16]: https://github.com/lucatume/wp-browser/compare/2.2.15...2.2.16
[2.2.17]: https://github.com/lucatume/wp-browser/compare/2.2.16...2.2.17
[2.2.18]: https://github.com/lucatume/wp-browser/compare/2.2.17...2.2.18
[2.2.19]: https://github.com/lucatume/wp-browser/compare/2.2.18...2.2.19
[2.2.20]: https://github.com/lucatume/wp-browser/compare/2.2.19...2.2.20
[2.2.21]: https://github.com/lucatume/wp-browser/compare/2.2.20...2.2.21
[2.2.22]: https://github.com/lucatume/wp-browser/compare/2.2.21...2.2.22
[2.2.23]: https://github.com/lucatume/wp-browser/compare/2.2.22...2.2.23
[2.2.24]: https://github.com/lucatume/wp-browser/compare/2.2.23...2.2.24
[2.2.25]: https://github.com/lucatume/wp-browser/compare/2.2.24...2.2.25
[2.2.26]: https://github.com/lucatume/wp-browser/compare/2.2.25...2.2.26
[2.2.27]: https://github.com/lucatume/wp-browser/compare/2.2.26...2.2.27
[2.2.28]: https://github.com/lucatume/wp-browser/compare/2.2.27...2.2.28
[2.2.29]: https://github.com/lucatume/wp-browser/compare/2.2.28...2.2.29
[2.2.30]: https://github.com/lucatume/wp-browser/compare/2.2.29...2.2.30
[2.2.31]: https://github.com/lucatume/wp-browser/compare/2.2.30...2.2.31
[2.2.32]: https://github.com/lucatume/wp-browser/compare/2.2.31...2.2.32
[2.2.33]: https://github.com/lucatume/wp-browser/compare/2.2.32...2.2.33
[2.2.34]: https://github.com/lucatume/wp-browser/compare/2.2.33...2.2.34
[2.2.35]: https://github.com/lucatume/wp-browser/compare/2.2.34...2.2.35
[2.2.36]: https://github.com/lucatume/wp-browser/compare/2.2.35...2.2.36
[2.2.37]: https://github.com/lucatume/wp-browser/compare/2.2.36...2.2.37
[2.3.0]: https://github.com/lucatume/wp-browser/compare/2.2.37...2.3.0
[2.3.1]: https://github.com/lucatume/wp-browser/compare/2.3.0...2.3.1
[2.3.2]: https://github.com/lucatume/wp-browser/compare/2.3.1...2.3.2
[2.3.3]: https://github.com/lucatume/wp-browser/compare/2.3.2...2.3.3
[2.3.4]: https://github.com/lucatume/wp-browser/compare/2.3.3...2.3.4
[2.4.0]: https://github.com/lucatume/wp-browser/compare/2.3.4...2.4.0
[2.4.1]: https://github.com/lucatume/wp-browser/compare/2.4.0...2.4.1
[2.4.2]: https://github.com/lucatume/wp-browser/compare/2.4.1...2.4.2
[2.4.3]: https://github.com/lucatume/wp-browser/compare/2.4.2...2.4.3
[2.4.4]: https://github.com/lucatume/wp-browser/compare/2.4.3...2.4.4
[2.4.5]: https://github.com/lucatume/wp-browser/compare/2.4.4...2.4.5
[2.4.6]: https://github.com/lucatume/wp-browser/compare/2.4.5...2.4.6
[2.4.7]: https://github.com/lucatume/wp-browser/compare/2.4.6...2.4.7
[2.4.8]: https://github.com/lucatume/wp-browser/compare/2.4.7...2.4.8
[2.5.0]: https://github.com/lucatume/wp-browser/compare/2.4.8...2.5.0
[2.5.1]: https://github.com/lucatume/wp-browser/compare/2.5.0...2.5.1
[2.5.2]: https://github.com/lucatume/wp-browser/compare/2.5.1...2.5.2
[2.5.3]: https://github.com/lucatume/wp-browser/compare/2.5.2...2.5.3
[2.5.4]: https://github.com/lucatume/wp-browser/compare/2.5.3...2.5.4
[2.5.5]: https://github.com/lucatume/wp-browser/compare/2.5.4...2.5.5
[2.5.6]: https://github.com/lucatume/wp-browser/compare/2.5.5...2.5.6
[2.5.7]: https://github.com/lucatume/wp-browser/compare/2.5.6...2.5.7
[2.6.0]: https://github.com/lucatume/wp-browser/compare/2.5.7...2.6.0
[2.6.1]: https://github.com/lucatume/wp-browser/compare/2.6.0...2.6.1
[2.6.2]: https://github.com/lucatume/wp-browser/compare/2.6.1...2.6.2
[2.6.3]: https://github.com/lucatume/wp-browser/compare/2.6.2...2.6.3
[2.6.4]: https://github.com/lucatume/wp-browser/compare/2.6.3...2.6.4
[2.6.5]: https://github.com/lucatume/wp-browser/compare/2.6.4...2.6.5
[2.6.6]: https://github.com/lucatume/wp-browser/compare/2.6.5...2.6.6
[2.6.7]: https://github.com/lucatume/wp-browser/compare/2.6.6...2.6.7
[2.6.8]: https://github.com/lucatume/wp-browser/compare/2.6.7...2.6.8
[2.6.9]: https://github.com/lucatume/wp-browser/compare/2.6.8...2.6.9
[2.6.10]: https://github.com/lucatume/wp-browser/compare/2.6.9...2.6.10
[2.6.11]: https://github.com/lucatume/wp-browser/compare/2.6.10...2.6.11
[2.6.12]: https://github.com/lucatume/wp-browser/compare/2.6.11...2.6.12
[2.6.13]: https://github.com/lucatume/wp-browser/compare/2.6.12...2.6.13
[2.6.14]: https://github.com/lucatume/wp-browser/compare/2.6.13...2.6.14
[2.6.15]: https://github.com/lucatume/wp-browser/compare/2.6.14...2.6.15
[2.6.16]: https://github.com/lucatume/wp-browser/compare/2.6.15...2.6.16
[2.6.17]: https://github.com/lucatume/wp-browser/compare/2.6.16...2.6.17
[unreleased]: https://github.com/lucatume/wp-browser/compare/2.6.17...HEAD
