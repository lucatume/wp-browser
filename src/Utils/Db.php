<?php

declare(strict_types=1);

/**
 * Database related functions.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser\Utils;

use Closure;
use InvalidArgumentException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Db
{
    /**
     * Imports a dump file using the `mysql` binary.
     *
     * @param string $dumpFile The path to the SQL dump file to import.
     * @param string $dbName The name of the database to import the SQL dump file to.
     * @param string $dbUser The database user to use to import the dump.
     * @param string $dbPass The database password to use to import the dump.
     * @param string $dbHost The database host to use to import the dump.
     *
     * @return void
     * @throws RuntimeException If there's an error while importing the database.
     *
     */
    public static function importDumpWithMysqlBin(
        string $dumpFile,
        string $dbName,
        string $dbUser = 'root',
        string $dbPass = 'root',
        string $dbHost = 'localhost'
    ): void {
        $dbPort = false;
        if (strpos($dbHost, ':') > 0) {
            [$dbHost, $dbPort] = explode(':', $dbHost);
        }

        $command = [self::mysqlBin(), '--host=' . escapeshellarg($dbHost), '--user=' . escapeshellarg($dbUser)];
        if (!empty($dbPass)) {
            $command[] = '--password=' . escapeshellarg($dbPass);
        }
        if (!empty($dbPort)) {
            $command[] = '--port=' . escapeshellarg($dbPort);
        }

        // $command = array_merge($command, [escapeshellarg($dbName), '<', escapeshellarg($dumpFile)]);
        $command = array_merge($command, [escapeshellarg($dbName), '<', escapeshellarg($dumpFile)]);

        $import = Process::process(implode(' ', $command));

        $importOutput = $import(Process::PROC_READ);
        $importError = $import(Process::PROC_ERROR);

        codecept_debug('Import output: ' . $importOutput);
        codecept_debug('Import error: ' . $importError);

        $status = $import(Process::PROC_STATUS);

        codecept_debug('Import status: ' . $status);

        if ($status !== 0) {
            throw new RuntimeException('Import failed: ' . $importError);
        }
    }

    /**
     * Returns the path to the MySQL binary, aware of the current Operating System.
     *
     * @return string The name, and path, to the MySQL binary.
     */
    public static function mysqlBin(): string
    {
        return 'mysql';
    }

    /**
     * Open a database connection and returns a callable to run queries on it.
     *
     * @param string $dsn The database DSN string.
     * @param string $user The database user.
     * @param string $pass The database password.
     * @param string|null $dbName The optional name of the database to use.
     *
     * @return Closure A callable to run queries on the database; the function will return the query result
     *                  as \PDOStatement.
     *
     * @throws PDOException If the database connection attempt fails.
     */
    public static function db(string $dsn, string $user, string $pass, string $dbName = null): Closure
    {
        if ($dbName !== null) {
            // If a dbname is specified, then let's use that and not the one (maybe) specified in the dsn string.
            $dsn = preg_replace('/;*dbname=[^;]+/', '', $dsn);
            $dsn .= ';dbname=' . $dbName;
        }

        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return static function ($query) use ($pdo, $dsn, $user, $pass) {
            $result = $pdo->query($query);
            if (!$result instanceof PDOStatement) {
                throw new RuntimeException('Query failed: ' . json_encode([
                        'dsn' => $dsn,
                        'user' => $user,
                        'pass' => $pass,
                        'query' => $query,
                        'error' => $pdo->errorInfo(),
                    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            }

            return $result;
        };
    }

    /**
     * Identifies the database host type used from host string for the purpose of using it to build a database connection
     * dsn string.
     *
     * @param string $dbHost The database host string to build the DSN map from, e.g. `localhost` or
     *                       `unix_socket=/var/mysock.sql`. This input can be a DSN string too, but, knowing that before
     *                       hand, it would be better to use `dbDsnToMap` function.
     *
     * @return Map The database DSN connection `host`, `port` and `socket` map.
     */
    public static function dbDsnMap(string $dbHost): Map
    {
        if (Db::isDsnString($dbHost)) {
            return Db::dbDsnToMap($dbHost);
        }

        $map = new Map();

        if (preg_match('/dbname=(?<dbname>[^;]+;*)/', $dbHost, $m)) {
            // If the `dbname` is specified in the DSN, then use it.
            $map['dbname'] = $m['dbname'];
            $dbHost = str_replace($m[0], '', $dbHost);
        }

        $type = null;

        if (preg_match('/^(?<type>(mysql|sqlite)[23]*):.*(?!host=)/um', $dbHost, $typeMatches)) {
            // Handle dbHost strings that are, actually, DSN strings.
            $type = $typeMatches['type'];
            // Remove only for MySQL types.
            $dbHost = $type === 'mysql' ? preg_replace('/^' . $type . ':/', '', $dbHost) : $dbHost;
            $typeInInput = true;
        }

        $dbHost = rtrim((string)$dbHost, ';');

        $frags = array_replace(['', ''], explode(':', $dbHost));

        $mask =
            // It's an IP Address, `localhost` or a hostname (e.g. 'db' in the context of Docker containers) for MySQL.
            preg_match('/(localhost|((\\d+\\.){3}\\d+)|\\w+)/', $frags[0])
            // It's a port number.
            + 2 * is_numeric($frags[1])
            // It's a unix socket.
            + 4 * (preg_match('/(?:[^:]*:)*([^=]*=)*(?<socket>.*\\.sock(\\w)*)$/', $dbHost, $unixSocketMatches))
            // It's a sqlite database file.
            + 8 * (preg_match(
                '/^((?<version>sqlite(\\d)*):)*((?<file>(\\/.*(\\.sq(\\w)*))))$/um',
                $dbHost,
                $sqliteFileMatches
            ))
            // It's a sqlite in-memory database.
            + 16 * preg_match('/^(?<version>sqlite(\\d)*)::memory:$/um', $dbHost, $sqliteMemoryMatches);

        $extract = static function ($frag, $key): string|array {
            return str_replace($key . '=', '', $frag);
        };

        switch ($mask) {
            case 1:
                // IP Address.
                $map['type'] = $type ?: 'mysql';
                $map['host'] = $frags[0];
                break;
            case 2:
                // Just a port number, assume host is `localhost`.
                $map['type'] = $type ?: 'mysql';
                $map['host'] = 'localhost';
                $map['port'] = $frags[0];
                break;
            case 3:
                // IP Address or `localhost` and port.
                $map['type'] = $type ?: 'mysql';
                $map['host'] = $frags[0];
                $map['port'] = $frags[1];
                break;
            case 4:
            case 5:
            case 6:
            case 7:
                // Socket, `localhost:<socket>` or socket and port and socket: just keep the socket.
                $map['type'] = $type ?: 'mysql';
                $unixSocket = $unixSocketMatches['socket'];
                if (str_starts_with($unixSocket, '~')) {
                    $unixSocket = str_replace('~', FS::homeDir(), $unixSocket);
                }
                $map['unix_socket'] = $unixSocket;
                break;
            case 8:
            case 9:
                // sqlite file.
                $map['type'] = $type ?: 'sqlite';
                $map['version'] = $sqliteFileMatches['version'];
                $map['file'] = $sqliteFileMatches['file'];
                break;
            case 16:
                // sqlite in-memory db.
                $map['type'] = $type ?: 'sqlite';
                $map['version'] = $sqliteMemoryMatches['version'];
                $map['memory'] = true;
                break;
            default:
                // Empty?
                $map['type'] = $type ?: 'mysql';
                $map['host'] = 'localhost';
                break;
        }

        return $map;
    }

    /**
     * Builds a map of the dsn, user and password credentials to connect to a database.
     *
     * @param Map $dsn The dsn map.
     * @param string $dbuser The database user.
     * @param string $dbpass The database password for the user.
     * @param string|null $dbname The optional database name.
     *
     * @return Map The database credentials map: dsn string, user and password.
     */
    public static function dbCredentials(Map $dsn, string $dbuser, string $dbpass, string $dbname = null): Map
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
            'dsn' => $dsnString,
            'user' => $dbuser,
            'password' => $dbpass
        ]);
    }

    /**
     * Builds the database DSN string from a database DSN map.
     *
     * @param Map $dbDsnMap The database DSN map.
     * @param bool $forDbHost Whether to format for `DB_HOST`, or similar, use or not.
     *
     * @return string The database DSN string in the format required.
     *
     * @throws InvalidArgumentException If the database type is not supported or is not set.
     */
    public static function dbDsnString(Map $dbDsnMap, bool $forDbHost = false): string
    {
        $type = $dbDsnMap('type', 'mysql');
        $dsn = '';

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

            return $dbname && !$forDbHost ? $dsn . ';dbname=' . $dbname : $dsn;
        }

        if ($type === 'sqlite') {
            $dsn = $forDbHost ?
                $dbDsnMap('file')
                : $dbDsnMap('version') . ':' . $dbDsnMap('file');
        }

        return $dsn;
    }

    /**
     * Whether a string is a DSN string or not.
     *
     * @param string $string The string to check.
     *
     * @return bool Whether a string represents a DSN or not.
     */
    public static function isDsnString(string $string): bool
    {
        return (bool)preg_match('/^(mysql|sqlite(\\d)*):/', $string);
    }

    /**
     * Builds a Map from a DSN string.
     *
     * @param string $dsnString The DSN string to process and break down.
     *
     * @return Map The Map built from the DSN string.
     *
     * @throws InvalidArgumentException If the input string is not a valid DSN string.
     */
    public static function dbDsnToMap(string $dsnString): Map
    {
        if (!self::isDsnString($dsnString)) {
            throw new InvalidArgumentException("The string '{$dsnString}' is not a valid DSN string.");
        }

        $version = null;
        $type = 'mysql';

        if (preg_match('/^(?<type>(mysql|sqlite(?<version>\\d)*)):/', $dsnString, $m)) {
            $type = $m['type'] ?? 'mysql';
            $version = $m['version'] ?? null;
            $dsnString = str_replace($type . ':', '', $dsnString);
        }

        $map = [];
        if ($type === 'mysql') {
            $frags = explode(';', $dsnString);
            foreach ($frags as $frag) {
                [$key, $value] = explode('=', $frag);
                $map [$key] = $value;
            }
        } else {
            $memory = $dsnString === ':memory:';
            $map = array_filter([
                'type' => 'sqlite',
                'version' => 'sqlite' . $version,
                'file' => $memory ? null : $dsnString,
                'memory' => $memory
            ]);
        }

        return new Map(array_replace(['type' => 'mysql', 'host' => 'localhost'], $map));
    }
}
