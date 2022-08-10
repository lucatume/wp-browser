<?php

namespace lucatume\WPBrowser\Lib\Driver;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db;
use Codeception\Lib\Driver\MySql;
use Codeception\Lib\Driver\Oci;
use Codeception\Lib\Driver\PostgreSql;
use Codeception\Lib\Driver\Sqlite;
use Codeception\Lib\Driver\SqlSrv;

/**
 * Extends Codeception default Db driver to return an extended version of MySql driver.
 */
class ExtendedDbDriver extends Db
{

    /**
     * Identical to the original method but changing the MySQL driver.
     *
     * @static
     *
     * @param string $dsn The data source name for the database connection.
     * @param string $user The database access user.
     * @param string $password The database access password.
     * @param array<string,mixed>|null $options An array of connection options.
     *
     * @return Db|SqlSrv|MySql|Oci|PostgreSql|Sqlite
     *
     * @throws ModuleException If the module is not correctly configured to connect.
     *
     * @see   http://php.net/manual/en/pdo.construct.php
     * @see   http://php.net/manual/de/ref.pdo-mysql.php#pdo-mysql.constants
     *
     */
    public static function create($dsn, $user, $password, $options = null): \Codeception\Lib\Driver\Db|\Codeception\Lib\Driver\SqlSrv|\Codeception\Lib\Driver\MySql|\Codeception\Lib\Driver\Oci|\Codeception\Lib\Driver\PostgreSql|\Codeception\Lib\Driver\Sqlite
    {
        $provider = self::getProvider($dsn);

        return match ($provider) {
            'sqlite' => new Sqlite($dsn, $user, $password, $options),
            'mysql' => new ExtendedMySql($dsn, $user, $password, $options),
            'pgsql' => new PostgreSql($dsn, $user, $password, $options),
            'mssql', 'dblib', 'sqlsrv' => new SqlSrv($dsn, $user, $password, $options),
            'oci' => new Oci($dsn, $user, $password, $options),
            default => new Db($dsn, $user, $password, $options),
        };
    }
}
