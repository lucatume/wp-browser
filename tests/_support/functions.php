<?php

namespace lucatume\WPBrowser\Tests\Support;

use lucatume\WPBrowser\Utils\Db;
use lucatume\WPBrowser\Utils\Env;

/**
 * Returns the name of the environment file to load in tests.
 *
 * @return string The name of the environment file to load in tests.
 */
function testEnvFile(): string
{
    return '.env.testing';
}

/**
 * Creates the databases required by the tests, if they do not exist.
 */
function createTestDatabasesIfNotExist(): void
{
    $env = Env::envFile(testEnvFile());
    $host = $env['WORDPRESS_DB_HOST'];
    $user = $env['WORDPRESS_DB_USER'];
    $pass = $env['WORDPRESS_DB_PASSWORD'];
    $db = Db::db('mysql:host=' . $host, $user, $pass);
    $db('CREATE DATABASE IF NOT EXISTS ' . $env['WORDPRESS_SUBDIR_DB_NAME']);
    $db('CREATE DATABASE IF NOT EXISTS ' . $env['WORDPRESS_SUBDOMAIN_DB_NAME']);
    $db('CREATE DATABASE IF NOT EXISTS ' . $env['WORDPRESS_EMPTY_DB_NAME']);
}

/**
 * Imports the dumps into the test databases.
 */
function importTestDatabasesDumps(): void
{
    createTestDatabasesIfNotExist();
    $import = static function (array $filesMap) {
        $env = Env::envFile(testEnvFile());
        foreach ($filesMap as $file => $dbNameEnvVar) {
            Db::importDumpWithMysqlBin(
                codecept_data_dir($file),
                $env[$dbNameEnvVar],
                $env['WORDPRESS_DB_USER'],
                $env['WORDPRESS_DB_PASSWORD'],
                $env['WORDPRESS_DB_HOST']
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
