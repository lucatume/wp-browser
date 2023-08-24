## Using a custom configuration to run tests

If you decide to skip the default configuration, or are working
on [a project that cannot use the default configuration](default-configuration.md#when-not-to-use-the-default-configuration)
you will be able to set up `wp-browser` to suit your needs using a custom configuration.

Choose "no", to not use the default configuration, when running the `vendor/bin/codecept init wpbrowser` command.

The command will set up the file structure to be able to run integration and end-to-end tests and will
leverage [Codeception dynamic configuration using parameters][1] to control the testing stack using the `tests/.env`
file.

### Walkthrough of the `tests/.env` file

[1]: https://codeception.com/docs/ModulesAndHelpers#Dynamic-Configuration-With-Parameters

* `WORDPRESS_ROOT_DIR` - the path to the root WordPress installation directory. This is the directory that contains
  WordPress files, like `wp-load.php`. This path can be absolute or relative to the root project directory;
  e.g. `vendor/wordpress` (relative) or `/var/www/wordpress` (absolute) will work.
* `WORDPRESS_URL` - the URL of the WordPress installation. This is the URL that will be used by the browser to access
  the WordPress
  installation in the context of end-to-end tests; e.g. `http://localhost:8080` or `https://wordpress.local`.
* `WORDPRESS_DOMAIN` - the domain of the WordPress installation; this value should follow the `WORDPRESS_URL` value.
  E.g. if `WORDPRESS_URL` is `http://localhost:8080` the `WORDPRESS_DOMAIN` value should be `localhost:8080`;
  if `WORDPRESS_URL` is `https://wordpress.local` the `WORDPRESS_DOMAIN` value should be `wordpress.local`.
* `WORDPRESS_DB_URL` - the user, password, host, and name of the database used by the tests. If the database is a MySQL
  database, the value should be in the form `mysql://user:password@host:port/database_name`.
  If the database is a SQLite database, the value should be in the form `sqlite://path/to/database/file`.
* `WORDPRESS_TABLE_PREFIX` - the database table prefix used by the WordPress installation, the one served
  at `WORDPRESS_URL`.
  This value is usually `wp_` but can be different if the WordPress installation has been configured to use a different
  prefix.
* `TEST_TABLE_PREFIX` - the database table prefix used by [the WPLoader module](modules/WPLoader.md#configuration) to
  install WordPress and run the tests. This value is usually `test_` and should be different from
  the `WORDPRESS_TABLE_PREFIX` value.
* `WORDPRESS_ADMIN_USER` - the username of the WordPress administrator user. E.g. `admin`.
* `WORDPRESS_ADMIN_PASSWORD` - the password of the WordPress administrator user. E.g. `secret!password`.
* `CHROMEDRIVER_HOST` - the host of the Chromedriver server. This value is usually `localhost` if you're running
  Chromedriver on the same machine as the tests. If you're running your tests using a container stack, it will be the
  name of the container running Chromedriver, e.g. `chromedriver`.
* `CHROMEDRIVER_PORT` - the port of the Chromedriver server. This value is usually `9515` if you're running Chromedriver
  on the same machine as the tests. If you're running your tests using a container stack, it will be the port exposed by
  the container running Chromedriver, e.g. `4444`. Note [the default configuration](default-configuration.md) will set
  this value to a random port during set up to avoid conflicts with other services running on the same machine.

### Handling custom file structures

If your site uses a customized file structure to manage WordPress, you will need to further
configure [the WPLoader module](modules/WPLoader.md#configuration) to correctly look for the site content.
[Read more about setting up WPLoader to correctly load plugins and themes from custom locations here.](modules/WPLoader.md#handling-a-custom-site-structure)
