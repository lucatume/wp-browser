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
