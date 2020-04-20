<?php
/**
 * Database related functions.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

use tad\WPBrowser\Utils\Map;

/**
 * Converts a string DSN into a map of values.
 *
 * @param string $dsnString The string DSN to convert.
 *
 * @return Map The map of the parsed values.
 */
function dsnToMap($dsnString)
{
    preg_match('/^(?:(?<prefix>[^:]*):)?(?<data>.*)$/um', $dsnString, $m);

    $type = !empty($m['prefix']) ? $m['prefix'] : 'unknown';

    switch ($type) {
        case 'sqlite':
        case 'sqlite2':
            $map = [
                'file' => isset($m['data']) ? $m['data'] : null,
                'memory' => isset($m['data']) && $m['data'] === ':memory:'
            ];
            break;
        default:
        case 'mysql':
            if (isset($m['data'])) {
                if (strpos($m['data'], ';')) {
                    $frags = array_map(static function ($frag) {
                        return explode('=', $frag);
                    }, explode(';', (string)preg_replace('/^[^:]*:/', '', $dsnString)));

                    $map = array_combine(
                        array_column($frags, 0),
                        array_column($frags, 1)
                    );
                }
            }
            break;
    }

    $map['type'] = $type;
    $map['prefix'] = $type;

    return new Map($map);
}

/**
 * Imports a dump file using the `mysql` binary.
 *
 * @param string $dumpFile The path to the SQL dump file to import.
 * @param string $dbName   The name of the database to import the SQL dump file to.
 * @param string $dbUser   The database user to use to import the dump.
 * @param string $dbPass   The database password to use to import the dump.
 * @param string $dbHost   The database host to use to import the dump.
 *
 * @throws \RuntimeException If there's an error while importing the database.
 */
function importDumpWithMysqlBin($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost')
{
    $dbPort = false;
    if (strpos($dbHost, ':') > 0) {
        list($dbHost, $dbPort) = explode(':', $dbHost);
    }

    $command = [mysqlBin(), '--host=' . escapeshellarg($dbHost), '--user=' . escapeshellarg($dbUser)];
    if (!empty($dbPass)) {
        $command[] = '--password=' . escapeshellarg($dbPass);
    }
    if (!empty($dbPort)) {
        $command[] = '--port=' . escapeshellarg($dbPort);
    }

    $command = array_merge($command, [escapeshellarg($dbName), '<', escapeshellarg($dumpFile)]);

    $import = process($command);

	$importOutput = $import( PROC_READ );
	$importError = $import( PROC_ERROR );

	debug( 'Import output:' . $importOutput );
	debug( 'Import error:' . $importError );

    $status = $import(PROC_STATUS);

    debug('Import status: ' . $status);

	if ( $status !== 0 ) {
		throw new \RuntimeException( 'Import failed: ' . $importError );
	}
}

/**
 * Returns the path to the MySQL binary, aware of the current Operating System.
 *
 * @return string The name, and path, to the MySQL binary.
 */
function mysqlBin()
{
    return 'mysql';
}

/**
 * Open a database connection and returns a callable to run queries on it.
 *
 * @param string      $host   The database host.
 * @param string      $user   The database user.
 * @param string      $pass   The database password.
 * @param string|null $dbName The optional name of the database to use.
 *
 * @return \Closure A callable to run queries on the database; the function will return the query result
 *                  as \PDOStatement.
 *
 * @throws \PDOException If the database connection attempt fails.
 */
function db($host, $user, $pass, $dbName = null)
{
    $dsn = "mysql:host={$host}";

    if ($dbName !== null) {
        $dsn .= ';dbname=' . $dbName;
    }

    $pdo = new \PDO($dsn, $user, $pass);

    return static function ($query) use ($pdo, $host, $user, $pass) {
        $result = $pdo->query($query);
        if (!$result instanceof \PDOStatement) {
            throw new \RuntimeException('Query failed: ' . json_encode([
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass,
                    'query' => $query,
                    'error' => $pdo->errorInfo(),
                ], JSON_PRETTY_PRINT));
        }

        return $result;
    };
}
