# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

## Configuration

* `dsn` *required* - the database POD DSN connection details; read more [on PHP PDO documentation](https://secure.php.net/manual/en/ref.pdo-mysql.connection.php).
* `user` *required* - the database user.
* `password` *required* - the database password.
* `url` *required* - the full URL, including the HTTP scheme, of the website whose database is being accessed. WordPress uses hard-codece URLs in the databas, that URL will be set by this module when applying the SQL dump file during population or cleanup.
* `dump` *required* - defaults to `null`; sets the path, relative to the project root folder, or absolute to the SQL dump file that will be used to set the tests initial database fixture. If set to `null` then the `populate`, `cleanup` and `populator` parameters will be ignored.
* `populate` - defaults to `true` to empty the target database and import the SQL dump specified in the `dump` argument once, before any test starts.
* `cleanup` - defaults to `true` empty the target database and import the SQL dump specified in the `dump` argument before each test starts. 
* `urlReplacement` - defaults to `true` to replace, while using the built-in, PHP-based, dump import solution the hard-coded WordPress URL in the database with the specified one.
* `populator` - defaults to `null`, if set to an executable shell command then that command will be used to populate the database in place of the built-in PHP solution; URL replacement will not apply in this case. Read more about this [on Codeception documentation](https://codeception.com/docs/modules/Db#Populator).
* `reconnect` - defaults to `true` to force the module to reconnect to the database before each test in place of only connecting at the start of the tests.
* `waitlock` - defaults to `10`; wait lock (in seconds) that the database session should use for DDL statements.
* `tablePrefix` - defaults to `wp_`; sets the prefix of the tables that the module will manipulate.

### Example configuration
```yaml
  modules:
      enabledr
          - WPDb
      config:
          WPDb:
              dsn: 'mysql:host=localhost;dbname=wordpress'
              user: 'root'
              password: 'password'
              dump: 'tests/_data/dump.sql'
              populate: true
              cleanup: true
              waitlock: 10
              url: 'http://wordpress.localhost'
              urlReplacement: true
              tablePrefix: 'wp_'
```

<!--doc-->
<!--/doc-->
