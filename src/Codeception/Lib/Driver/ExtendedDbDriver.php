<?php

namespace Codeception\Lib\Driver;

use Codeception\Exception\ModuleException;

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
    public static function create($dsn, $user, $password, $options = null)
    {
        $provider = self::getProvider($dsn);

        switch ($provider) {
            case 'sqlite':
                return new Sqlite($dsn, $user, $password, $options);
            case 'mysql':
                return new ExtendedMySql($dsn, $user, $password, $options);
            case 'pgsql':
                return new PostgreSql($dsn, $user, $password, $options);
            case 'mssql':
            case 'dblib':
            case 'sqlsrv':
                return new SqlSrv($dsn, $user, $password, $options);
            case 'oci':
                return new Oci($dsn, $user, $password, $options);
            default:
                return new Db($dsn, $user, $password, $options);
        }
    }
}
