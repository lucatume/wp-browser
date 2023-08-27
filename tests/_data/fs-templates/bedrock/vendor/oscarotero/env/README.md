# env

[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]


Simple library to get environment variables converted to simple types.

## Installation

This package is installable and autoloadable via Composer as [oscarotero/env](https://packagist.org/packages/oscarotero/env).

```
$ composer require oscarotero/env
```

## Example

```php
use Env\Env;

// Using getenv function:
var_dump(getenv('FOO')); //string(5) "false"

// Using Env:
var_dump(Env::get('FOO')); //bool(false)
```

## Available conversions

* `"false"` is converted to boolean `false`
* `"true"` is converted to boolean `true`
* `"null"` is converted to `null`
* If the string contains only numbers is converted to an integer
* If the string has quotes, remove them

## Options

To configure the conversion, you can use the following constants (all enabled by default):

* `Env::CONVERT_BOOL` To convert boolean values
* `Env::CONVERT_NULL` To convert null values
* `Env::CONVERT_INT` To convert integer values
* `Env::STRIP_QUOTES` To remove the quotes of the strings

There's also additional settings that you can enable (they're disabled by default)

* `Env::USE_ENV_ARRAY` To get the values from `$_ENV`, instead `getenv()`.
* `Env::USE_SERVER_ARRAY` To get the values from `$_SERVER`, instead `getenv()`.
* `Env::LOCAL_FIRST` To get first the values of locally-set environment variables.

```php
use Env\Env;

//Convert booleans and null, but not integers or strip quotes
Env::$options = Env::CONVERT_BOOL | Env::CONVERT_NULL;

//Add one more option
Env::$options |= Env::USE_ENV_ARRAY;

//Remove one option
Env::$options ^= Env::CONVERT_NULL;
```

## Default value

By default, if the value does not exist, returns `null`, but you can change for any other value:

```php
use Env\Env;

Env::$default = false;
```

## The env() function

You can use the `env()` function, like in Laravel or other frameworks:

```php
use function Env\env;

var_dump(env('FOO'));
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/oscarotero/env/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/oscarotero/env.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/oscarotero/env.svg?style=flat-square

[link-travis]: https://travis-ci.org/oscarotero/env
[link-scrutinizer]: https://scrutinizer-ci.com/g/oscarotero/env
[link-downloads]: https://packagist.org/packages/oscarotero/env
