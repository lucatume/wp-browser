## Some common questions
There are questions I keep receiving via email, GitHub or in person at conferences.  
I tried to address some of them here.

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

### Do I need to use a specific local development environment to use wp-browser?
No. I've started using wp-browser on a vanilla PHP built-in server to, then, move to [MAMP](https://www.mamp.info/en/) (or [XAMP](https://www.apachefriends.org/download.html)) and, from there, to other solutions.  
I've configured and used wp-browser on Docker, Vagrant, [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV), [Valet](https://laravel.com/docs/5.7/valet) and various CI solutions.  
To this day I keep using different setups on different machines and personally prefer [Docker](https://www.docker.com/) for its portability.

### Can I only test plugins with wp-browser?
No, you can test any kind of WordPress application.  
With "application" I mean any PHP software built on top of WordPress: plugins, themes, whole sites.

### If I'm testing a site do I have to use the default WordPress file structure?
No, you can use any file structure you want.  
Some wp-browser modules will need a little help to find your code but, so far, I've never been unable to set it up.

### Can I use wp-browser even if my WordPress application doesn't use Composer?
Yes, although wp-browser, as a development tool, cannot be installed without [Composer](https://getcomposer.org/).

### Should I use wp-browser to test my production servers?
No. Unless you know very well what you're doing that's a dangerous idea that might leave you with a broken site and an empty database.  
As almost any testing tool wp-browser should be used locally on local installations of WordPress that do not contain any valuable information.

### Can I run all my tests with one command?
Theoretically: yes, in practice: no.  
When you use `codecept run` Codeception will run all the tests from all the suites.  
This, done in the context of other frameworks, will generally not create any problem but, in the context of WordPress it will.  
While handling a single HTTP request WordPress will set, and use, a number of constants and globals and, likewise, will do plugins and themes that follow WordPress standards.  
This means that the global context (variable scope) will be left "dirty" and contain "left-over" constants and globals from the previous tests.  
An example is one where a test for the handling of Ajax requests sets the `DOING_AJAX` constant: this will be now set for **any** test after the one that set it thus breaking, or worse altering, all the following ones.
So, in short, **run each suite separately**.

### Can I have more than one suite of one kind?
Yes, you should.  
As an example you might have a `frontend` suite running [acceptance tests](levels-of-testing.md#acceptance-tests) on the site frontend and a `backend` suite running acceptance tests on the site backend.  
Think of suites as a tool to organize your tests: there's a good measure between too organized and not organized at all.

### I've used PHPUnit before for my unit tests, can I reuse that knowledge and code with wp-browser?
Yes.
Codeception uses PHPUnit as one of its main components and can run PHPUnit tests with little or no modification.  
As such you can just move your existing PHPUnit tests in a dedicated suite and be ready to run in minutes.

### I've already set up my tests to run using the Core PHPUnit-based test suite, can I keep using my tests?
Yes.  
Codeception uses PHPUnit as one of its main components and can run PHPUnit tests with little or no modification.  
One of the goals of wp-browser was to make it easier to test WordPress application at an integration level (or "WordPress unit" level).  
As such migrating those tests could be a matter of minutes requiring no modification to the tests if not for moving some files and creating a dedicated suite.

### Why is the project called wp-browser?
When I started working with Codeception to run my acceptance tests I kept creating steps that I would reuse over and over in my projects.  
I packed them in a module extending [the `PHPBrowser` module](https://codeception.com/docs/modules/PhpBrowser).  
Being a natural talent in naming things I've called the module `WPBrowser` and published it. As I started relying on Codeception more and more I kept adding modules but the name remained.
