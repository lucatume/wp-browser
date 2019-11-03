## Requirements

wp-browser has some requirements your development environment will need to fulfill for it to work correctly.  

### PHP

The minimum supported version of PHP supported by wp-browser is 5.6.  

This requirement does not reflect on the minimum PHP version your plugin might require; see [the FAQs](faq.md#is-codeception-wp-browser-php-5-2-compatible) for more information.

### Composer

There is no `phar` version of wp-browser and it can only be installed using [Composer](https://getcomposer.org/).  

See [Composer installation guide](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) for more information.

### WordPress, MySQL, Apache/Nginx

wp-browser will **not** download, install and configure WordPress for you.  

It will also **not** download, install and setup MySQL, Apache, Nginx or any other technology required by a fully functional WordPress installation for you.  

You need to set up a local WordPress installation on your own; you can use [your preferred solution to do it](faq.md#do-i-need-to-use-a-specific-local-development-environment-to-use-wp-browser).

In the documentation I will show automated ways to do this but, for most projects, that's not the best solution.
