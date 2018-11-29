<?php

namespace Codeception\Lib\Driver;

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
     * @param $dsn
     * @param $user
     * @param $password
     * @param [optional] $options
     *
     * @see   http://php.net/manual/en/pdo.construct.php
     * @see   http://php.net/manual/de/ref.pdo-mysql.php#pdo-mysql.constants
     *
     * @return Db|SqlSrv|MySql|Oci|PostgreSql|Sqlite
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
