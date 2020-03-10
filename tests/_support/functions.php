<?php

namespace tad\WPBrowser\Tests\Support;

use Dotenv\Environment\DotenvFactory;
use Dotenv\Loader;

function importDump($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost')
{
    if (strpos($dbHost, ':') > 0) {
        list($dbHost, $dbPort) = explode(':', $dbHost);
        $dbHost = sprintf('%s -P %d', $dbHost, $dbPort);
    }

    $commandTemplate = 'mysql -h %s -u %s %s %s < %s';
    $dbPassEntry = $dbPass ? '-p' . $dbPass : '';

    $sql = file_get_contents($dumpFile);

    if (false === $sql) {
        return false;
    }

    $command = sprintf($commandTemplate, $dbHost, $dbUser, $dbPassEntry, $dbName, $dumpFile);
    exec($command, $output, $status);

    return (int)$status === 0;
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
 * Open a database connection and returns a callable to run queries on it.
 *
 * @param string $host The database host.
 * @param string $user The database user.
 * @param string $pass The database password.
 * @return \Closure A callable to run queries on the database.
 */
function db($host, $user, $pass)
{
    $pdo = new \PDO("mysql:host={$host}", $user, $pass);
    return static function ($query) use ($pdo, $host, $user, $pass) {
        $result = $pdo->exec($query);
        if (false === $result) {
            throw new \RuntimeException('Could not create the subdir db; ' . json_encode([
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass,
                    'query' => $query
                ], JSON_PRETTY_PRINT));
        }

        return $result;
    };
}

/**
 * Returns the name of the environment file to load in tests.
 *
 * @return string The name of the environment file to load in tests.
 */
function envFile()
{
    return '.env.testing.docker';
}

/**
 * Returns a closure to get the value of an environment variable, loading a specific env file first.
 *
 * @param string|null $file The name of the environment file to load, or `null` to use the default one.
 * @return \Closure A closure taking one argument, the environment variable name, to return it.
 */
function env($file = null)
{
    $envFile = $file ?: envFile();
    $env = new Loader([codecept_root_dir($envFile)], new DotenvFactory());
    return static function ($name) use ($env) {
        return $env->getEnvironmentVariable($name);
    };
}
