<?php

namespace tad\WPBrowser\Services\Db;

class MySQLDumpFactory implements MySQLDumpFactoryInterface
{

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param array|null $dbName
     * @param string $charset
     *
     * @return MySQLDumpInterface
     */
    public function makeDump($host, $username, $password, $dbName, $charset = 'utf8')
    {
        $mysqli = new \mysqli($host, $username, $password, $dbName);

        if ($mysqli->connect_error) {
            throw new \PDOException('Could not connecto to [' . $dbName . '] database: ' . $mysqli->connect_error);
        }

        return new \MySQLDump($mysqli, $charset);
    }
}
