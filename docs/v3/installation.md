## Installation

### Where should I install wp-browser?

As a rule-of-thumb wp-browser should be installed in the root folder of your 
project.  

If your project is a plugin then it should be installed in the root folder of your plugin; if your project is a theme it should be installed in the root folder of your theme.  

If your project is a site I'd, personally install it in the site root folder. 
 
The purpose of installing wp-browser in the root folder of a project is to keep the code and its tests under version control together.  

Exceptions apply but, for most projects, that's what I would do.

### Initializing the Composer project

Since [Composer](https://getcomposer.org/) is a requirement of wp-browser and the only way to install it you should, first thing, initialize the Composer project.  

**If you've already initialized the Composer project you can skip this section.**

Once you've decided where to install wp-browser navigate to that folder using the terminal and type:


```bash
composer init
```

Composer will take you through a number of questions to setup some meta information about your project.  

Do not install any dependency yet when asked (unless you know what you're doing) and, as a suggestion, set `wordpress-plugin` as "Package Type".  

Also, since WordPress is [licensed under the GPL-2.0+](https://wordpress.org/about/license/) you might want to set the "License" of your project to `GPL-2.0-or-later`.

### Installing wp-browser as a development dependency

Once you've initialized the Composer project it's time to `require` wp-browser ; you can read more about [the usage of the `require` command on the Composer documentation](https://getcomposer.org/doc/03-cli.md#require).  

wp-browser is a testing tool and, as such, should be installed as a **project development dependency**, not as a normal (production) one.  

From the terminal type:

```bash
composer require --dev lucatume/wp-browser
```

This will install the latest stable version of wp-browser and, along with it, Codeception and PHPUnit in the `vendor` folder of your project.  

Once that's done it's time to move to the [setup and configuration of wp-browser](configuration.md).
