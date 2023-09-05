> **This is the documentation for version 3 of the project.**
> **The current version is version 4 and the documentation can be found [here](./../README.md).**

## The wp-browser stack

The wp-browser project is built leveraging the power of a number of open-source projects.  

While I'm not listing all of them here it's worth mentioning those that will come up, again and again, in the documentation.

### WordPress

WordPress is open source software you can use to create a beautiful website, blog, or app.  

The line is taken directly from [WordPress.org](https://wordpress.org/) site. 
 
In the context of this documentation WordPress is the PHP and JavaScript framework websites and web applications can be built on, the one anyone can download [from here](https://wordpress.org/download/).

### Codeception

Codeception ([home](http://codeception.com/ "Codeception - BDD-style PHP testing.")) is a modern, powerful PHP testing framework written in PHP.  

It comes with a number of [modules](https://codeception.com/docs/06-ModulesAndHelpers) and [extensions](https://codeception.com/extensions) that are comparable to WordPress plugins and themes.  

Modules and extensions are combined in *suites* to be able to run a specific type of test. Each suite will handle a specific type of test for a specific set of code.

wp-browser is none other than a **collection of modules and extensions for Codeception made specifically to test WordPress applications**.

### PHPUnit

PHPUnit is the most widely known PHP testing framework. As the name implies it was born to make unit testing of PHP code easier but its scope and power has grown well below that.  

Codeception is based, and uses, [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework") to wrap some of its functionalities into an easy-to-use API.  
The two are so compatible one with the other that Codeception can **run** PHPUnit tests with little to no changes.

This documentation will not cover this subject and will only deal with Codeception-native test methods but you can find more information [here](https://codeception.com/docs/05-UnitTests).

