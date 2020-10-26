# wp-browser

[![CI](https://github.com/lucatume/wp-browser/workflows/CI/badge.svg)](https://github.com/lucatume/wp-browser/actions?query=branch%3Amaster)

wp-browser provides easy acceptance, functional, integration and unit testing for WordPress plugins, themes and
whole sites using [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.").

Find out more in [the documentation](https://wpbrowser.wptestkit.dev).

## Installation and setup - the really fast version

Using [Composer](https://getcomposer.org/) require `wp-browser` as a development dependency:

```bash
cd my-wordrpess-project
composer require --dev lucatume/wp-browser
vendor/bin/codecept init wpbrowser
```

Answer the questions and you will be ready to test your project. Find out more about the setup in [the project 
documentation][1].

## Using wp-browser with Codeception 4.0

Codeception version `4.0`, while still being compatible with PHP `5.6` and wp-browser, did break its structure into discrete modules.  

If you want to use wp-browser with Codeception version `4.0+` you will need to make sure you've got all the required packages.  
Add the following requirements in your `composer.json` file, in the `require-dev` section:

```json
{
  "require-dev": {
    "lucatume/wp-browser": "^2.4",
    "codeception/module-asserts": "^1.0",
    "codeception/module-phpbrowser": "^1.0",
    "codeception/module-webdriver": "^1.0",
    "codeception/module-db": "^1.0",
    "codeception/module-filesystem": "^1.0",
    "codeception/module-cli": "^1.0",
    "codeception/util-universalframework": "^1.0"
  }
}
```

You might not need all of them depending on the modules you use in your suites, but this will cover all the modules for this project. 

[Read more here.][2]

## Usage
The project provides a number of modules to ease the testing of WordPress projects; you can find out more in the 
[modules section of the documentation][3].  
Here's a quick example acceptance test you can write:

```php
// tests/acceptance/PrivatePostsCept.php
$I->haveManyPostsInDatabase(3, ['post_title' => 'Test post {{n}}', 'post_status' => 'private']);

$I->loginAs('subscriber', 'secret');
$I->amOnPage('/');
$I->see('Nothing found');

$I->loginAs('editor', 'secret');
$I->amOnPage('/');
$I->see('Test post 0');
$I->see('Test post 1');
$I->see('Test post 2');
``` 

This is just a bite though, find out more in [the documentation][1].

## Current Sponsors

My sincere thanks to my sponsors: you make maintaining this project easier.

* [@TimothyBJacobs](https://github.com/TimothyBJacobs)

[1]: https://wpbrowser.wptestkit.dev/
[2]: https://wpbrowser.wptestkit.dev/levels-of-testing
[3]: https://wpbrowser.wptestkit.dev/modules
