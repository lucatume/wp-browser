<?php

namespace Codeception\Lib\Driver;


class ExtendedDb extends Db
{

    /**
     * @static
     *
     * @param $dsn
     * @param $user
     * @param $password
     *
     * @return Db|MsSql|ExtendedMySql|Oracle|PostgreSql|Sqlite
     */
    public static function create($dsn, $user, $password)
    {
        $provider = self::getProvider($dsn);

        switch ($provider) {
            case 'sqlite':
                return new Sqlite($dsn, $user, $password);
            case 'mysql':
                return new MySql($dsn, $user, $password);
            case 'pgsql':
                return new PostgreSql($dsn, $user, $password);
            case 'mssql':
                return new MsSql($dsn, $user, $password);
            case 'oracle':
                return new Oracle($dsn, $user, $password);
            case 'sqlsrv':
                return new SqlSrv($dsn, $user, $password);
            case 'oci':
                return new Oci($dsn, $user, $password);
            default:
                return new Db($dsn, $user, $password);
        }
    }
}