# WPQueries module

This module provides assertions for WordPress queries.

This module can be used in any test context where the global `$wpdb` variable is defined, this usually means in any
suite where [the WPLoader module] is used.

## Configuration

The method does not require configuration.

## Methods

The module provides the following methods:

* `assertCountQueries(int $n, string [$message])` : `void`
* `assertNotQueries(string [$message])` : `void`
* `assertNotQueriesByAction(string $action, string [$message])` : `void`
* `assertNotQueriesByFilter(string $filter, string [$message])` : `void`
* `assertNotQueriesByFunction(string $function, string [$message])` : `void`
* `assertNotQueriesByMethod(string $class, string $method, string [$message])` : `void`
* `assertNotQueriesByStatement(string $statement, string [$message])` : `void`
* `assertNotQueriesByStatementAndAction(string $statement, string $action, string [$message])` : `void`
* `assertNotQueriesByStatementAndFilter(string $statement, string $filter, string [$message])` : `void`
* `assertNotQueriesByStatementAndFunction(string $statement, string $function, string [$message])` : `void`
* `assertNotQueriesByStatementAndMethod(string $statement, string $class, string $method, string [$message])` : `void`
* `assertQueries(string [$message])` : `void`
* `assertQueriesByAction(string $action, string [$message])` : `void`
* `assertQueriesByFilter(string $filter, string [$message])` : `void`
* `assertQueriesByFunction(string $function, string [$message])` : `void`
* `assertQueriesByMethod(string $class, string $method, string [$message])` : `void`
* `assertQueriesByStatement(string $statement, string [$message])` : `void`
* `assertQueriesByStatementAndAction(string $statement, string $action, string [$message])` : `void`
* `assertQueriesByStatementAndFilter(string $statement, string $filter, string [$message])` : `void`
* `assertQueriesByStatementAndFunction(string $statement, string $function, string [$message])` : `void`
* `assertQueriesByStatementAndMethod(string $statement, string $class, string $method, string [$message])` : `void`
* `assertQueriesCountByAction(int $n, string $action, string [$message])` : `void`
* `assertQueriesCountByFilter(int $n, string $filter, string [$message])` : `void`
* `assertQueriesCountByFunction(int $n, string $function, string [$message])` : `void`
* `assertQueriesCountByMethod(int $n, string $class, string $method, string [$message])` : `void`
* `assertQueriesCountByStatement(int $n, string $statement, string [$message])` : `void`
* `assertQueriesCountByStatementAndAction(int $n, string $statement, string $action, string [$message])` : `void`
* `assertQueriesCountByStatementAndFilter(int $n, string $statement, string $filter, string [$message])` : `void`
* `assertQueriesCountByStatementAndFunction(int $n, string $statement, string $function, string [$message])` : `void`
* `assertQueriesCountByStatementAndMethod(int $n, string $statement, string $class, string $method, string [$message])` : `void`
* `countQueries(?wpdb [$wpdb])` : `int`
* `getQueries(?wpdb [$wpdb])` : `array`
