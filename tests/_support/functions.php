<?php

namespace tad\WPBrowser\Tests\Support;

use function tad\WPBrowser\db;
use function tad\WPBrowser\envFile;
use function tad\WPBrowser\mysqlBin;
use function tad\WPBrowser\process;
use const tad\WPBrowser\PROC_ERROR;
use const tad\WPBrowser\PROC_READ;
use const tad\WPBrowser\PROC_STATUS;

/**
 * Imports a dump file using the `mysql` binary.
 *
 * @param string $dumpFile The path to the SQL dump file to import.
 * @param string $dbName   The name of the database to import the SQL dump file to.
 * @param string $dbUser
 * @param string $dbPass
 * @param string $dbHost
 * @return bool
 */
function importDumpWithMysqlBin($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost')
{
    $dbPort = false;
    if (strpos($dbHost, ':') > 0) {
        list($dbHost, $dbPort) = explode(':', $dbHost);
    }

    $command = [mysqlBin(), '-h', $dbHost, '-u', $dbUser];
    if (!empty($dbPass)) {
        $command[] = '-p';
        $command[] = $dbPass;
    }
    if (!empty($dbPort)) {
        $command[] = '-P';
        $command[] = $dbPort;
    }

    $command = array_merge($command, [$dbName, '<', $dumpFile]);

    $import = process($command);

    codecept_debug('Import output:' . $import(PROC_READ));
    codecept_debug('Import error:' . $import(PROC_ERROR));

    $status = $import(PROC_STATUS);

    codecept_debug('Import status: ' . $status);

    return $status === 0;
}

/**
 * Normalizes a string new line bytecode for comparison
 * through Unix and Windows environments.
 *
 * @see https://stackoverflow.com/a/7836692/2056484
 */
function normalizeNewLine($str)
{
    return preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $str);
}

/**
 * Returns the name of the environment file to load in tests.
 *
 * @return string The name of the environment file to load in tests.
 */
function testEnvFile()
{
    return '.env.testing.docker';
}

/**
 * Creates the databases required by the tests, if they do not exist.
 */
function createTestDatabasesIfNotExist()
{
    $env = envFile(testEnvFile());
    $host = $env('WORDPRESS_DB_HOST');
    $user = $env('WORDPRESS_DB_USER');
    $pass = $env('WORDPRESS_DB_PASSWORD');
    $db = db($host, $user, $pass);
    $db('CREATE DATABASE IF NOT EXISTS ' . $env('WORDPRESS_SUBDIR_DB_NAME'));
    $db('CREATE DATABASE IF NOT EXISTS ' . $env('WORDPRESS_SUBDOMAIN_DB_NAME'));
    $db('CREATE DATABASE IF NOT EXISTS ' . $env('WORDPRESS_EMPTY_DB_NAME'));
}

/**
 * Imports the dumps into the test databases.
 */
function importTestDatabasesDumps()
{
    createTestDatabasesIfNotExist();
    $import = static function (array $filesMap) {
        $env = envFile(testEnvFile());
        foreach ($filesMap as $file => $dbNameEnvVar) {
            importDumpWithMysqlBin(
                codecept_data_dir($file),
                $env($dbNameEnvVar),
                $env('WORDPRESS_DB_USER'),
                $env('WORDPRESS_DB_PASSWORD'),
                $env('WORDPRESS_DB_HOST')
            );
        }
    };

    $import(
        [
            'dump.sql' => 'WORDPRESS_DB_NAME',
            'mu-subdir-dump.sql' => 'WORDPRESS_SUBDIR_DB_NAME',
            'mu-subdomain-dump.sql' => 'WORDPRESS_SUBDOMAIN_DB_NAME'
        ]
    );
}
