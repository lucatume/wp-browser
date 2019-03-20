# WPDb module
This module should be used in acceptance and functional tests, see [levels of testing for more information](./../levels-of-testing.md).  
This module extends the [Db module](https://codeception.com/docs/modules/Db) adding WordPress-specific configuration parameters and methods.  
The module provides methods to read, write and update the WordPress database **directly**, without relying on WordPress methods, using WordPress functions or triggering WordPress filters.  

<!--doc-->

Fatal error: Cannot redeclare Codeception\Module\WPDb::grabUsersTableName() in /Users/luca/Repos/wp-browser/src/Codeception/Module/WPDb.php on line 2967
<!--/doc-->
