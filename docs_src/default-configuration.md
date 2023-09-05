## Default testing configuration

The recommended configuration for most projects.
It allows you to get into WordPress integration and end-to-end testing quickly and easily.

## Requirements

The default configuration will set up [Codeception][1] and wp-browser to use SQLite as the database engine, PHP built-in
server to serve the test site on localhost and your local version of Chrome, driven by Chromedriver, to run end-to-end
tests.

As such, the default configuration has the following requirements:

* the `sqlite3` PHP extension; you can check if if's installed by running `php -m | grep sqlite3` in your terminal
* the `pdo_sqlite` PHP extension; you can check if if's installed by running `php -m | grep pdo_sqlite` in your terminal
* PHP built-in server can work with only one thread, but it will be faster using multiple threads; multiple threads are
  not supported on Windows, but they are supported on WSL.
* the Chrome browser installed on your machine

## Overview - plugin and theme project

If you're configuring wp-browser for a plugin or theme project, the default configuration will install WordPress in
the `tests/_wordpress` directory and configure the installation to run using SQLite as a database engine.
The [SQLite Database Integration plugin][2]) will be placed in the installation must-use plugins directory.

If your plugin or theme project requires additional plugins or theme to work, you can place them in
the `tests/_worpdress/wp-content/plugins` and `tests/_wordpress/wp-content/themes` directories respectively.

When adding, or removing, plugin and themes, remember to
update [the WPLoader module configuration](modules/WPLoader.md#configuration) to load the correct plugins and themes in
your integration tests.

On the same note, update the database dump used by [the WPDb module](modules/WPDb.md#configuration) to reflect the
changes in the dump loaded in the end-to-end tests.
The easiest way to update the database fixture is to load the current database dump
using [the `wp:db:import` command](commands.md#wpdbimport), manually setting up the site interacting with it and then
exporting the database dump using [the `wp:db:export` command](commands.md#wpdbexport).

You can find out about the URL of the site served by the PHP built-in web server by
running [the `dev:info` command](commands.md#devinfo).

## Overview - site project

If you're configuring wp-browser for a site project, the default configuration will use a combination of PHP built-in
web server and the [SQLite Database Integration plugin][2] to run the tests and serve your site.

The router file used by the PHP built-in web server will force the site, when served on localhost, to use SQLite as
database engine leaving your existing local MySQL database untouched.

Your existing WordPress installation will be picked up as it is, with all the plugins and themes found in the contents
directory.

Existing plugins and themes are not added to [WPLoader module configuration](modules/WPLoader.md#configuration) by
wp-browser, you have to do that manually.

Similarly, the database dump used by [the WPDb module](modules/WPDb.md#configuration) is, by default, an empty WordPress
installation where no plugins and themes are active.
You have to update the database dump used by the module to reflect the state of your site.
You can do that by loading the current database dump using [the `wp:db:import` command](commands.md#wpdbimport),
manually setting up the site interacting with it and then
exporting the database dump using [the `wp:db:export` command](commands.md#wpdbexport).

You can find out about the URL of the site served by the PHP built-in web server by
running [the `dev:info` command](commands.md#devinfo).

## When not to use the default configuration

The default configuration is the recommended one for most projects, but some projects might require you to use a custom
configuration to make the most out of wp-browser.

### Database drop-in

The default configuration will use the [SQLite Database Integration plugin][2] to use SQLite as the database engine.
This requires placing a `db.php` drop-in file in the WordPress content directory.

If your project already requires a `db.php` drop-in file, you will have to use a custom configuration.

### Multisite with sub-domains

While Chrome will handle sub-domains correctly, even on `localhost`, WordPress will not.
If you're testing a multisite installation with sub-domains, you will have to use a custom configuration.

### Custom site structure

If your site uses a customized file structure to manage WordPress, you will need to configure wp-browser using a custom
configuration.
This is usually true for some site projects, and will most likely not be an issue for plugin and theme projects.

Using a custom configuration is not that difficult
though: [read more about using a custom configuration here.](custom-configuration.md)

[1]: https://codeception.com/

[2]: https://wordpress.org/plugins/sqlite-database-integration/
