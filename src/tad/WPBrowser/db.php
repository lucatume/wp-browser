<?php
/**
 * Database related functions.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

use tad\WPBrowser\Utils\Map;

/**
 * Imports a dump file using the `mysql` binary.
 *
 * @param string $dumpFile The path to the SQL dump file to import.
 * @param string $dbName   The name of the database to import the SQL dump file to.
 * @param string $dbUser   The database user to use to import the dump.
 * @param string $dbPass   The database password to use to import the dump.
 * @param string $dbHost   The database host to use to import the dump.
 *
 * @return bool Whether the import was successful, exit status `0`, or not.
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

    debug('Import output:' . $import(PROC_READ));
    debug('Import error:' . $import(PROC_ERROR));

    $status = $import(PROC_STATUS);

    debug('Import status: ' . $status);

    return $status === 0;
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

/**
 * Identifies the database host type used from host string for the purpose of using it to build a database connection
 * dsn string.
 *
 * @param string $dbHost The database host to map.
 *
 * @return Map The database DSN connection `host`, `port` and `socket` map.
 */
function dbDsnMap($dbHost)
{
    $map = new Map([]);

    if (preg_match('/dbname=(?<dbname>[^;]+;*)/', $dbHost, $m)) {
        $map['dbname'] = $m['dbname'];
        $dbHost        = str_replace($m[0], '', $dbHost);
    }

    $dbHost = rtrim($dbHost, ';');

    $frags = array_replace([ '', '' ], explode(':', $dbHost));

    $mask =
        // It's an IP Address or `localhost`
        preg_match('/(localhost|((\\d+\\.){3}\\d+))/', $frags[0])
        // It's a port number.
        + 2 * is_numeric($frags[1])
        // It's a unix socket.
        + 4 * ( preg_match('/(?:[^:]*:)*([^=]*=)*(?<socket>.*\\.sock(\\w)*)$/', $dbHost, $unixSocketMatches) )
        // It's a sqlite database file.
        + 8 * ( preg_match(
            '/^((?<version>sqlite(\\d)*):)*((?<file>(\\/.*(\\.sq(\\w)*))))$/um',
            $dbHost,
            $sqliteFileMatches
        ) )
        // It's a sqlite in-memory database.
        + 16 * preg_match('/^(?<version>sqlite(\\d)*)::memory:$/um', $dbHost, $sqliteMemoryMatches);

    switch ($mask) {
        default:
            // Empty?
            $map['type'] = 'mysql';
            $map['host'] = 'localhost';
            break;
        case 1:
            // IP Address.
            $map['type'] = 'mysql';
            $map['host'] = $frags[0];
            break;
        case 2:
            // Just a port number, assume host is `localhost`.
            $map['type'] = 'mysql';
            $map['host'] = 'localhost';
            $map['port'] = $frags[0];
            break;
        case 3:
            // IP Address or `localhost` and port.
            $map['type'] = 'mysql';
            $map['host'] = $frags[0];
            $map['port'] = $frags[1];
            break;
        case 4:
        case 5:
        case 6:
        case 7:
            // Socket, `localhost:<socket>` or socket and port and socket: just keep the socket.
            $map['type'] = 'mysql';
            $map['unix_socket'] = $unixSocketMatches['socket'];
            break;
        case 8:
            // sqlite file.
            $map['type'] = 'sqlite';
            $map['version'] = $sqliteFileMatches['version'];
            $map['file'] = $sqliteFileMatches['file'];
            break;
        case 16:
            // sqlite in-memory db.
            $map['type'] = 'sqlite';
            $map['version'] = $sqliteMemoryMatches['version'];
            $map['memory'] = true;
    }

    return $map;
}

/**
 * Builds a map of the dsn, user and password credentials to connect to a database.
 *
 * @param Map        $dsn    The dsn map.
 * @param string      $dbuser The database user.
 * @param string      $dbpass The database password for the user.
 * @param null|string $dbname The optional database name.
 *
 * @return Map The database credentials map: dsn string, user and password.
 */
function dbCredentials($dsn, $dbuser, $dbpass, $dbname = null)
{
    $dbname = $dsn->get('dbname', $dbname);
    $dbuser = empty($dbuser) ? 'root' : $dbuser;
    $dbpass = empty($dbpass) ? 'password' : $dbpass;

    $dsnFrags = [
        $dsn('host') ? 'host=' . $dsn('host') : null,
        $dsn('port') ? 'port=' . $dsn('port') : null,
        $dsn('unix_socket') ? 'unix_socket=' . $dsn('unix_socket') : null,
        $dbname ? 'dbname=' . $dbname : null
    ];

    $type = $dsn('type', 'mysql');

    $dsnString = $type . ':' . implode(';', array_filter($dsnFrags));

    return new Map([
        'dsn'      => $dsnString,
        'user'     => $dbuser,
        'password' => $dbpass
    ]);
}

/**
 * Builds the database DSN string from a database DSN map.
 *
 * @param Map  $dbDsnMap    The database DSN map.
 * @param bool $forDbHost   Whether to format for `DB_HOST`, or similar, use or not.
 *
 * @throws \InvalidArgumentException If the database type is not supported or is not set.
 */
function dbDsnString(Map $dbDsnMap, $forDbHost = false)
{
    $type = $dbDsnMap('type', 'mysql');

    if ($type === 'mysql') {
        $dsn = $forDbHost ? '' : 'mysql:';
        $dbname = $dbDsnMap('dbname');

        if ($dbDsnMap('unix_socket')) {
            $dsn .= $forDbHost ?
                $dbDsnMap('host', 'localhost') . ':' . $dbDsnMap('unix_socket')
                : 'unix_socket=' . $dbDsnMap('unix_socket');

            return $dbname && !$forDbHost ? $dsn . ';dbname=' . $dbname : $dsn;
        }

        $dsn .= $forDbHost ?
            $dbDsnMap('host', 'localhost')
            : 'host=' . $dbDsnMap('host', 'localhost');

        $port = $dbDsnMap('port');

        if ($port) {
            $dsn .= $forDbHost ? ':' . $port : ';port=' . $dbDsnMap('port');
        }

        return $dbname && ! $forDbHost ? $dsn . ';dbname=' . $dbname : $dsn;
    }

    if ($type === 'sqlite') {
        $dsn = $forDbHost ?
            $dbDsnMap('file')
            : $dbDsnMap('version') . ':' . $dbDsnMap('file');
    }

    return $dsn;
}
