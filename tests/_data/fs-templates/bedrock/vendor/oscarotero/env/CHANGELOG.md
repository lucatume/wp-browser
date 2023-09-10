# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.1.0] - 2020-06-11
### Added
- New constant `Env::USE_SERVER_ARRAY` to get the values using `$_SERVER` array instead `getenv()` [#8].

## [2.0.0] - 2020-06-03
### Changed
- This library is under the `Env` namespace instead the global. [#7]
- The function `env` is under `Env` namespace and loaded automatically. [#7]
- Included PHP 7 strict typing

### Removed
- Support for PHP 5.x and PHP 7.0.

## [1.2.0] - 2019-04-03
### Added
- New constant `Env::LOCAL_FIRST` to get first the values of locally-set environment variables [#6].

### Fixed
- Added requirement for ctype extension in composer.json [#4].

## [1.1.0] - 2017-07-17
### Added
- New constant `Env::USE_ENV_ARRAY` to get the values using `$_ENV` array instead `getenv()` [#3].

### Fixed
- Fixed test in php versions 5.2 and 7.x

## [1.0.2] - 2016-05-08
### Fixed
- `Env::init()` checks if the function `env()` exists, to prevent fatal errors on declare the function twice. If the funcion couldn't be included, returns false, otherwise returns true [#1].

## [1.0.1] - 2015-12-31
### Fixed
- Fixed error on strip quotes to empty strings

## 1.0.0 - 2015-12-30
First stable version

[#1]: https://github.com/oscarotero/env/issues/1
[#3]: https://github.com/oscarotero/env/issues/3
[#4]: https://github.com/oscarotero/env/issues/4
[#6]: https://github.com/oscarotero/env/issues/6
[#7]: https://github.com/oscarotero/env/issues/7
[#8]: https://github.com/oscarotero/env/issues/8

[2.1.0]: https://github.com/oscarotero/env/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/oscarotero/env/compare/v1.2.0...v2.0.0
[1.2.0]: https://github.com/oscarotero/env/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/oscarotero/env/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/oscarotero/env/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/oscarotero/env/compare/v1.0.0...v1.0.1

---

Previous releases are documented in [github releases](https://github.com/oscarotero/Gettext/releases)
