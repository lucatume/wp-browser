wp-browser
==========
Easy acceptance, functional, integration and unit testing for WordPress plugins, themes and sites using Codeception.

[![Build Status](https://travis-ci.org/lucatume/wp-browser.svg?branch=master)](https://travis-ci.org/lucatume/wp-browser)

wp-browser provides a [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") based solution to
 test WordPress plugins, themes and whole sites at all levels of testing.  

[Find out more here in the documentation](https://wpbrowser.wptestkit.dev).

## Installation and setup - the really fast version
Using [Composer](https://getcomposer.org/) require `wp-browser` as a development dependency:

```bash
cd my-wordrpess-project
composer require --dev lucatume/wp-browser
vendor/bin/codecept init wpbrowser
```

Answer the questions and you will be ready to test your project. Find out more about the setup in [the project 
documentation](https://wpbrowser.wptestkit.dev).

## Usage
The project provides a number of modules to ease the testing of WordPress projects; you can find out more in the 
[modules section of the documentation](https://wpbrowser.wptestkit.dev/summary/modules).  
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

This is just a bite though, find out more in [the documentation](https://wpbrowser.wptestkit.dev).
