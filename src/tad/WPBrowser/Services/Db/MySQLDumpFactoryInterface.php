<?php

namespace tad\WPBrowser\Services\Db;

interface MySQLDumpFactoryInterface
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
    public function makeDump($host, $username, $password, $dbName, $charset = 'utf8');
}
