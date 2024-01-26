Depending on your project PHP compatibility, you have three options to choose from:

* Your project supports PHP `7.1` to `7.4`: [migrate to wp-browser version `3.5`](#migrate-from-version-3-to-35)
* Your project supports PHP `8.0` or above: [migrate to wp-browser version `4.0`](#migrate-from-version-3-to-4)
* You cannot, or do not want, to migrate from version `3` of wp-browser to a new
  version: [see how you can lock your reuirements to avoid the upgrade](#staying-on-version-lower-than-35)

## Version 3.5 and 4.0

Version `3.5` and `4.0` are the latest versions of wp-browser.  
Version `3.5` is a transpile of version `4` to be compatible with PHP `7.1` to `7.4` that contains, for all intents and
purposes, the same facilities and systems contained in version `4` of wp-browser adapter to work on lower PHP versions and version `4` of Codeception.

Future development of wp-browser will happen on version `4` and will be transpiled to version `3.5` for
back-compatibility purposes.

## Migrate from version 3 to 3.5

1. Update the required version of `wp-browser` in your `composer.json` file to `3.5`:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "^3.5"
  }
}
```

## Migrate from version 3 to 4

1. Update the required version of `wp-browser` in your `composer.json` file to `4.0`:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "^4.0"
  }
}
```

## Staying on version lower than 3.5

Update your `composer.json` file to lock the required version of `wp-browser` to a version less than `3.5`:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "<3.5"
  }
}
```
