<p align="center">
  <img alt="wp-password-bcrypt" src="https://cdn.roots.io/app/uploads/logo-roots.svg" width="100">
</p>

<p align="center">
  <img alt="MIT License" src="https://img.shields.io/github/license/roots/wp-password-bcrypt?color=%23525ddc&style=flat-square" />

  <a href="https://packagist.org/packages/roots/wp-password-bcrypt">
    <img alt="Packagist" src="https://img.shields.io/packagist/v/roots/wp-password-bcrypt.svg?style=flat-square" />
  </a>

  <a href="https://packagist.org/packages/roots/wp-password-bcrypt">
    <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/roots/wp-password-bcrypt.svg?style=flat-square)" />
  </a>

  <img alt="Build Status" src="https://github.com/roots/wp-password-bcrypt/workflows/Main/badge.svg" />

  <a href="https://twitter.com/rootswp">
    <img alt="Follow Roots" src="https://img.shields.io/twitter/follow/rootswp.svg?style=flat-square&color=1da1f2" />
  </a>
</p>

<p align="center">
  <strong>Drop-in bcrypt password hashing for WordPress</strong>
  <br />
  Built with ❤️
</p>

## Supporting

**wp-password-bcrypt** is an open source project and completely free to use.

However, the amount of effort needed to maintain and develop new features and products within the Roots ecosystem is not sustainable without proper financial backing. If you have the capability, please consider donating using the links below:

<div align="center">

[![Sponsor on GitHub](https://img.shields.io/static/v1?label=sponsor&message=%E2%9D%A4&logo=GitHub)](https://github.com/sponsors/roots)
[![Donate via Patreon](https://img.shields.io/badge/donate-patreon-orange.svg?style=flat-square&logo=patreon")](https://www.patreon.com/rootsdev)
[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-blue.svg?style=flat-square&logo=paypal)](https://www.paypal.me/rootsdev)

</div>

## Overview

wp-password-bcrypt is a WordPress plugin to replace WP's outdated and insecure MD5-based password hashing with the modern and secure [bcrypt](https://en.wikipedia.org/wiki/Bcrypt).

This plugin requires PHP >= 5.5.0 which introduced the built-in [`password_hash`](http://php.net/manual/en/function.password-hash.php) and [`password_verify`](http://php.net/manual/en/function.password-verify.php) functions.

See [Improving WordPress Password Security](https://roots.io/improving-wordpress-password-security/) for more background on this plugin and the password hashing issue.

## Requirements

- PHP >= 5.5.0
- WordPress >= 4.4 (see https://core.trac.wordpress.org/ticket/33904)

## Installation

This plugin is a Composer library so it can be installed in a few ways:

#### Composer Autoloaded

`composer require roots/wp-password-bcrypt`

`wp-password-bcrypt.php` file will be automatically autoloaded by Composer and it _won't_ appear in your plugins.

#### Manually as a must-use plugin

If you don't use Composer, you can manually copy `wp-password-bcrypt.php` into your `mu-plugins` folder.

We **do not** recommend using this as a normal (non-mu) plugin. It makes it too easy to disable or remove the plugin.

## The Problem

WordPress still uses an MD5 based password hashing scheme. They are effectively making 25% of websites more insecure because they refuse to bump their minimum PHP requirements. By continuing to allow EOL PHP versions back to 5.2, they can't use newer functions like `password_hash`.

This is a [known](https://core.trac.wordpress.org/ticket/21022) problem which WordPress has ignored for over 4 years now. Not only does WordPress set the insecure default of MD5, they don't do any of the following:

- document this issue
- provide instructions on how to fix it and make it more secure
- notify users on newer PHP versions that they _could_ be more secure

What's wrong with MD5? Really simply: it's too cheap and fast to generate cryptographically secure hashes.

## The Solution

WordPress did at least one good thing: they made `wp_check_password` and `wp_hash_password` [pluggable](https://codex.wordpress.org/Pluggable_Functions) functions. This means we can define these functions in a plugin and "override" the default ones.

This plugin plugs in 3 functions:

- `wp_check_password`
- `wp_hash_password`
- `wp_set_password`

#### `wp_hash_password`

This function is the simplest. This plugin simply calls `password_hash` instead of WP's default password hasher.
The `wp_hash_password_options` filter is available to set the [options](http://php.net/manual/en/function.password-hash.php) that `password_hash` can accept.

#### `wp_check_password`

At its core, this function just calls `password_verify` instead of the default.
However, it also checks if a user's password was _previously_ hashed with the old MD5-based hasher and re-hashes it with bcrypt. This means you can still install this plugin on an existing site and everything will work seamlessly.

The `check_password` filter is available just like the default WP function.

#### `wp_set_password`

This function is included here verbatim but with the addition of returning the hash. The default WP function does not return anything which means you end up hashing it twice for no reason.

## FAQ

**What happens to existing passwords when I install the plugin?**

Nothing at first. An existing password is only re-hashed with bcrypt _when they log in_. If a user never logs in, their password will remain hashed with MD5 in your database forever.

**Why doesn't this plugin re-hash all existing passwords in the database?**

Right now it's beyond the scope of the plugin. We want to keep it simple and straightforward. This is probably best left up to the individual developer or maybe a separate plugin in the future. See https://github.com/roots/wp-password-bcrypt/issues/6 for more details.

**What happens if I remove/deactivate the plugin?**

Magically, everything still works. See this [comment](https://github.com/roots/wp-password-bcrypt/issues/7#issuecomment-190919884) for more details.

Any existing bcrypt hashed passwords will remain that way. Any new users or users resetting a password will get a new MD5 hashed password.

**Why aren't you using the password_compat library so this works back to PHP 5.3.7?**

The [password_compact](https://github.com/ircmaxell/password_compat) library is great if you really need it. But the Roots team advocates using supported versions of PHP which of now (March 2016) is 5.5 and above. Part of security is using a version of PHP that still gets security patches so we won't actively do something to support old unsupported versions of PHP.

**Why doesn't this plugin show up in the admin?**

If you're using Composer, then the `wp-password-bcrypt.php` file is automatically autoloaded. It's not treated as a true WordPress plugin since the package type is not set to `wordpress-muplugin` so it won't show up in the plugin list.

**What's wrong with using this as a plugin instead of a must-use plugin?**

As explained above, you don't want to disable this plugin once you've enabled it. Installing this in `plugins` (as a "normal" plugin) instead of in `mu-plugins` (as a must-use plugin) makes it possible for an admin user to accidentally disable it.

**How is this different than other plugins which already exist?**

There are a [few plugins](https://en-gb.wordpress.org/plugins/search.php?q=bcrypt) that exist which enable bcrypt. This plugin is different because we bypass the `PasswordHash` class and the `phpass` library that WordPress core uses. This plugin uses PHP's built-in `password_hash` and `password_verify` functions directly to only support PHP 5.5+.

**I already use Two-factor authentication and/or prevent brute-force login attempts. Does this plugin still help?**

Better hashing functions like bcrypt serve a different purpose than Two-factor authentication, brute-force attempt protection, or anything which acts at the log in stage. Strong hashing functions are important if an attacker is able to get access to your database. They will make it much harder/practically impossible to determine the plain-text passwords from the hashes. Whereas with MD5, this is trivial. Tools/plugins to protect logging in are still important and should be used together with this plugin.

## Further Reading

- `password_hash` [RFC](https://wiki.php.net/rfc/password_hash)
- [Secure Password Storage in PHP](https://paragonie.com/blog/2016/02/how-safely-store-password-in-2016#php)
- [How To Safely Store A Password](https://codahale.com/how-to-safely-store-a-password/)

## Contributors

This plugin is based on a [Gist](https://gist.github.com/Einkoro/11078301) by [@Einkoro](https://github.com/Einkoro).

It has been modified and packaged by the Roots team. Jan Pingel (@Einkoro) has granted his permission for us to redistribute his original BSD-licensed code to an MIT license.

## Contributing

Contributions are welcome from everyone. We have [contributing guidelines](https://github.com/roots/guidelines/blob/master/CONTRIBUTING.md) to help you get started.

## Community

Keep track of development and community news.

- Participate on the [Roots Discourse](https://discourse.roots.io/)
- Follow [@rootswp on Twitter](https://twitter.com/rootswp)
- Read and subscribe to the [Roots Blog](https://roots.io/blog/)
- Subscribe to the [Roots Newsletter](https://roots.io/subscribe/)
- Listen to the [Roots Radio podcast](https://roots.io/podcast/)
