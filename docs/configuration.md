## Preparing for the installation
As mentioned in [Installation](installation.md) section wp-browser will **not** download, configure and install WordPress for you.  
On a high level, once WordPress is installed and configured, whatever local development environment solution you've used, there are some informations you'll need to gather before moving into wp-browser configuration.  
While there will be a section dedicated to different environments and setups I will outline below the example setup I will use, in the next section, to configure wp-browser:

* WordPress is installed, on my machine, at `/Users/luca/Sites/wordpress`.
* I'm running MySQL server locally; I can connect to the the MySQL server with the command `mysql -u root -h 127.0.0.1 -P 3306`; there is no password.
* I've created two databases, `wordpress` and `tests`, with the command:
    ```bash
    mysql -u root -h 127.0.0.1 -P 3306 -e "create database if not exists wordpress; create database if not exists tests"
    ```
* I've configured the `/Users/luca/Sites/wordpress/wp-config.php` file like below (redacted for brevity):
    ```php
    <?php
    define( 'DB_NAME', 'wordpress' );
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', '' );
    define( 'DB_HOST', '127.0.0.1' );
    define( 'DB_CHARSET', 'utf8' );
    define( 'DB_COLLATE', '' );
    
    $table_prefix = 'wp_';
  
    if ( ! defined( 'ABSPATH' ) )
    	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    
    require_once ABSPATH . 'wp-settings.php';
    ```
* To serve the site I'm usign PHP built-in server with the command:
    ```bash
    (cd /Users/luca/Sites/wordpress; php -S localhost:8080)
    ```
* I can access the WordPress homepage at `http://localhost:8080` and the administration area at `http://localhost:8080/wp-admin`.
* I've installed WordPress via its UI (`http://localhost:8080/wp-admin`), the administrator username is `admin`, the administrator password is `password`.
* I'm testing a plugin and that plugin is in the folder, relative to the WordPress root folder, `wp-content/plugins/acme-plugin`.
    
With all the steps above done I can now move into the actual wp-browser configuration phase.

## Configuring wp-browser
While wp-browser can be configured manually creating each file the fastest way to bootstrap its configuration is by using its initialization template.  
From the root folder of the project, `/Users/luca/Sites/wordpress/wp-content/plugins/acme-plugin` in the example, run the command:

```bash
vendor/bin/codecept init wpbrowser
```

Composer installed Codeception binary (`codecept`) in the `vendor` folder of my project.  
With the command above I'm telling Codeception to initialize a wp-browser project in the current folder.  
When I click enter I'm presented with a list of questions, each answer will be used to configure the wp-browser project for me; one by one.  
In the screenshot the answers I've provided to each question, with reference to the setup above:

![codecept init wpbrowser 1](images/codecept-init-wpbrowser-01.png)
![codecept init wpbrowser 2](images/codecept-init-wpbrowser-02.png)

wp-browswer will try to provide a brief explanation of what each question is but below is the long version.

### Long question explanation
#### How would you like the acceptance suite to be called?
With reference to the [testing levels definition](levels-of-testing.md) this question provides you with the possibility to change the name of the acceptance-like test suite.  
Common, alternative, names are `ui`, `rest` and `user`.  

#### How would you like the functional suite to be called?
With reference to the [testing levels definition](levels-of-testing.md) this question provides you with the possibility to change the name of the functional-like test suite.  
A common alternative name is `service`.  

#### How would you like the WordPress unit and integration suite to be called?
With reference to the [testing levels definition](levels-of-testing.md) this question provides you with the possibility to change the name of the suite dedicated to integration and "WordPress unit" tests.  
A common alternative name is `integration`.  

#### How would you like to call the env configuration file? (Should start with ".env")
Instead of configuring each module in each suite with the same parameters over and over [Codeception supports dynamic configuration via environment files](https://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Parameters).  
wp-browser will scaffold such a configuration for you and will use, by default, a file called `.env` to store the configuration parameters.  
The file name might not suit all setups especially and this question allows changing that file name; common, alternative, file names are `.env.tests`, `.env.codeception` and similar.  

#### Where is WordPress installed?
During tests the test code will need to access WordPress code, precisely wp-browser requires being pointed to the folder that contains the `wp-load.php` file.  
The answer can be an absolute path, like `/Users/luca/Sites/wordrpress`, or a path relative to the folder where Codeception is installed like `vendor/wordpress`.  
This path should be accessible **by the machine that is running the tests**; if you're running the tests from your machine (e.g. your laptop) that's just the path to the folder where WordPress is installed, '/Users/luca/Sites/wordpress' in the example configuration above.  
If you are, instead, running the tests from withing a virtualized machine (e.g. [Vagrant](https://www.vagrantup.com/) or [Docker](https://www.docker.com/)) then the path should be the one used by the virtualized machine.  
To make an example:
* on my machine WordPress is installed at `/Users/luca/Sites/wordpress`
* I've created a Docker container using [the official WordPress image](https://hub.docker.com/_/wordpress) and bound the above folder into the container
* internall the container will put WordPress in the `/var/www/html` folder

If I run the tests from my host machine then WordPress root directory will be `/Users/luca/Sites/wordpress`, if I run the tests from **within** the Docker container then WordPress root folder will be `/var/www/html`.  
Another example is [Local by Flywheel](https://local.getflywheel.com/):
* in the host machine the path to the WordPress root folder will be `/Users/luca/Local\ Sites/wordpress/app/public`
* from within the Docker container managed by Local the path will be `/app/public`

If you need a solution that will work in both instances **use a relative path**: wp-browser will accept paths like `./../../../wordpress` and will attempt to resolve them.

#### What is the path, relative to WordPress root URL, of the admin area of the test site?
This is usually `/wp-admin` but you might have the web-server, or a plugin, redirect or hide requests for the administration area to another path.  
Some examples are `/admin`, `/login` and the like.  
Mind that this is **not** the path to the login page but the path to the administrationo area; this will be used by wp-browser to find to the administration area in acceptance and functional tests.

#### What is the name of the test database used by the test site?
In my example setup it's `wordpress`.  
This is the name of the database that is storing the information used by the site I can reach at `http://localhost:8080`.  
>I want to underline the word "test". Any site and any database you use and expose to wp-browser should be intended for tests; this means that it does not contain any data you care about as **it will be lost**.

#### What is the host of the test database used by the test site?
In my example setup it's `127.0.0.1:3306`.  
Here the same principle valid for [Where is WordPress installed?](#where-is-wordpress-installed) applies: the database host is **relative to the machine that is running the tests**.  
In my example I'm hosting the database locally, on my laptop, and my machine can reach it at the localhost address (`127.0.0.1`) on MySQL default port (`3306`).  
If I am using the database of a [Local by Flywheel](https://local.getflywheel.com/) site from my host machine then it might be something like `192.168.92.100:4050` (from the site "Database" tab); the same principle applies if I am using a Vagrant-based or Docker-based solution.  
If I am running the tests from within a virtualized machine (a Docker container, a Vagrant box et cetera) then it would probably be `localhost` or `1270.0.0.1`.  
This detail will be used in the context of acceptance and functional tests by the [WPDb module](modules/WPDb.md).

#### What is the user of the test database used by the test site?
In my example setup it's `root` as I'm using MySQL server root user to access the database during tests.  
Depending on your setup it might be different; since wp-browser will need to not only read but write too to the database make sure to use a user that has full access to the database specified in the answer to the [What is the host of the test database used by the test site?](#what-is-the-host-of-the-test-database-used-by-the-test-site) question.
This detail will be used in the context of acceptance and functional tests by the [WPDb module](modules/WPDb.md).

#### What is the password of the test database used by the test site?
In my example setup it's empty as I've not set any password for the root account. 
In your case it might be different and it should be the password associated with the user specified in the answer to the [What is the user of the test database used by the test site?](#what-is-the-user-of-the-test-database-used-by-the-test-site) question.
This detail will be used in the context of acceptance and functional tests by the [WPDb module](modules/WPDb.md).

#### What is the table prefix of the test database used by the test site?
In my example setup it's `wp_`; that value is taken from the [WordPress installation configuration file](#preparing-for-the-installation).  
To have any influence on the site wp-browser will need to modify the same database tables WordPress is using; as I did you can take this value from the `wp-config.php` file directly: it's the value of the `$table_prefix` variable.  
This detail will be used in the context of acceptance and functional tests by the [WPDb module](modules/WPDb.md).

 ## Final touches
To complete the setup I have removed any demo content from the site and activated my plugin in the plugins administration page.
In the `tests/acceptance.suite.yml` file and in the `tests/functional.suite.yml` file, the configuration file for the `acceptance` and `functional` suites respectively, the `WPDb` module configuration contains a `dump` configuration parameter:

```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - WPDb
    config:
        WPDb:
            dump: 'tests/_data/dump.sql'
```

The `dump` parameter is inherited by the `WPDb` module from [the Codeception `Db` module](https://codeception.com/docs/modules/Db) and defines the SQL dump file that should be loaded before, and between, tests to reset the testing environment to a base known state.  
As for any other database-related operation wp-browser **will not** create the dump for me.
I use MySQL binary to export the database state (a dump) with the command:
```php
mysqldump -u root -h 127.0.0.1 -P 3306 wordpress > /Users/luca/Sites/wordpress/wp-content/plugins/acme-plugin/tests/_data/dump.sql
```
I could use, really, any other combination of tools to produce the dump; using `mysql` binary is not a requirement. Graphic interfaces like [SequelPro](https://sequelpro.com/), [Adminer](https://www.adminer.org/) and the like would be perfectly fine.

## Pre-flight check
There is one last check I need to make before jumping into the creation of tests: making sure all the paths and credentials I've configured wp-browser with are correct.  
The bootstrap process generated four suites for me: `acceptance`, `functional`, `integration` and `unit`. If you have modified the default suite names during the setup your suites names might differ though.  
To test the setup I will run each suite and make sure it can run correctly empty of any test. To run a suite of tests I will use the `codecept run` command:

```bash
codecept run acceptance
codecept run functional
codecept run integration
codecept run unit
```

How comes I'm not using the command `codecept run` (without specifying the suite names)? See the [FAQ entry](faq.md/#can-i-run-all-my-tests-with-one-command).

![Pre-flight check](images/codecept-run.png)

