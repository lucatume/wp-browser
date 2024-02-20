# wp-browser

Version 3 of the project is compatible with PHP 5.6+ and Codeception 4.
This branch is maintained for back-compatibility purposes; if you're starting new project, you should use the latest version.

[Read more about how to migrate to a new version of wp-browser here][4].

wp-browser provides easy acceptance, functional, integration and unit testing for WordPress plugins, themes and
whole sites using [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.").

Find out more in [the documentation](https://wpbrowser.wptestkit.dev/v3).

## Installation and setup - the really fast version

Using [Composer](https://getcomposer.org/) require `wp-browser` as a development dependency:

```bash
cd my-wordrpess-project
composer require --dev lucatume/wp-browser
vendor/bin/codecept init wpbrowser
```

Answer the questions and you will be ready to test your project. Find out more about the setup in [the project 
documentation][1].

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

[1]: https://wpbrowser.wptestkit.dev/v3
[2]: https://wpbrowser.wptestkit.dev/v3/levels-of-testing
[3]: https://wpbrowser.wptestkit.dev/v3/modules
[4]: https://wpbrowser.wptestkit.dev/migration/
