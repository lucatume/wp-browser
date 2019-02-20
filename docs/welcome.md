The [wp-browser](https://github.com/lucatume/wp-browser "lucatume/wp-browser · GitHub") project aims at providing a [Codeception](http://codeception.com/ "Codeception - BDD-style PHP testing.") based solution to test WordPress plugins, themes and whole sites.  
The purpose of this documentation is to help you set up, run and iterate over your project and test code using all levels of testing.
Throughout the documentation you will find references to "test" jargon: I've tried to condense those into small, digestable chunks to provide a rough idea without and a limited context; where required I tried to provide links to dive deeper into the subjects.

### Codeception
Codeception ([home](http://codeception.com/ "Codeception - BDD-style PHP testing.") is a modern, powerful PHP testing framework written in PHP.  
It comes with a number of [modules](https://codeception.com/docs/06-ModulesAndHelpers) and [extensions](https://codeception.com/extensions) that are comparable to WordPress plugins and themes.  
Modules and extensions are combined in *suites* to be able to run a specific type of test. Each suite will handle a specific type of test for a specific set of code.
wp-browser is none other than a **collection of modules and extensions for Codeception made specifically to test WordPress applications**.

### PHPUnit
Codeception is based, and uses, [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework").  
The two are so compatible one with the other that Codeception can **run** PHPUnit tests with little to no changes.
This documentation will not cover this subject and will only deal with Codeception-native test methods but you can find more information [here](https://codeception.com/docs/05-UnitTests).

### Is Codeception/wp-browser PHP 5.2 compatible?
No, Codeception, and wp-browser by extension, will require PHP 5.6 minimum.  
This does **not** mean your code cannot be PHP 5.2 compatible: you can test your code using all the possibilities of newer PHP versions and still keep it PHP 5.2 compatible.  
Just because you can doesn't mean you should though: this documentation will assume a minimum PHP version, for the example and test code, of PHP 5.6.

### Can I run unit tests with wp-browser/Codeception?
Yes, with some distinctions.  
In the WordPress echosystem there's a tendency to call **any** kind of test a "unit test". Under that definition will fall tests that are not "unit" tests at all.  
Without drowning into a long and painful battle for definitions this guide will use the following definitions for different levels of testing.  
The [next section](levels-of-testing.md) will detail the conventions this documentation uses to define different levels of testing in more detail.

### Isn't WordPress untestable?
No; it's sometimes **difficult** to test and not as straightforward as other PHP frameworks but it's definitely not untestable.  
**You** are writing code that **runs** on WordPress, not the Core code for WordPress so the question should really be: will **you** write testable code?  
It's up to **you** to decide at what level you want to make your code testable and how much you want to test it.
