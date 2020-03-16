<?php
/**
 * Miscellaneous utility functions for the wp-browser library.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

const PROC_CLOSE = 'proc_close';
const PROC_STATUS = 'proc_status';
const PROC_WRITE = 'proc_write';
const PROC_READ = 'proc_read';
const PROC_ERROR = 'proc_error';

/**
 * A function that does nothing, safe return when closures are expected.
 *
 * @param mixed|null The return value the noop will return.
 *
 * @return \Closure The noop function.
 */
function noop($return = null)
{
    return static function () use ($return) {
        return $return;
    };
}

/**
 * Builds an array format command line, compatible with the Symfony Process component, from a string command line.
 *
 * @param string|array $command The command line to parse, if in array format it will not be modified.
 *
 * @return array The parsed command line, in array format. Untouched if originally already an array.
 *
 * @uses \Symfony\Component\Process\Process To parse and escape the command line.
 */
function buildCommandline($command)
{
    if (empty($command) || is_array($command)) {
        return array_filter((array)$command);
    }

    $escapedCommandLine = (new \Symfony\Component\Process\Process($command))->getCommandLine();
    $commandLineFrags = explode(' ', $escapedCommandLine);

    if (count($commandLineFrags) === 1) {
        return $commandLineFrags;
    }

    $open = false;
    $unescapedQuotesPattern = '/(?<!\\\\)("|\')/u';

    return array_reduce($commandLineFrags, static function (array $acc, $v) use (&$open, $unescapedQuotesPattern) {
        $containsUnescapedQuotes = preg_match_all($unescapedQuotesPattern, $v);
        $v = $open ? array_pop($acc) . ' ' . $v : $v;
        $open = $containsUnescapedQuotes ?
            $containsUnescapedQuotes & 1 && (bool)$containsUnescapedQuotes !== $open
            : $open;
        $acc[] = preg_replace($unescapedQuotesPattern, '', $v);

        return $acc;
    }, []);
}

/**
 * Create the slug version of a string.
 *
 * This will also convert `camelCase` to `camel-case`.
 *
 * @param string $string The string to create a slug for.
 * @param string $sep    The separator character to use, defaults to `-`.
 * @param bool   $let    Whether to let other common separators be or not.
 *
 * @return string The slug version of the string.
 */
function slug($string, $sep = '-', $let = false)
{
    $unquotedSeps = $let ? ['-', '_', $sep] : [$sep];
    $seps = implode('', array_map(static function ($s) {
        return preg_quote($s, '~');
    }, array_unique($unquotedSeps)));

    // Prepend the separator to the first uppercase letter and trim the string.
    $string = preg_replace('/(?<![A-Z' . $seps . '])([A-Z])/u', $sep . '$1', trim($string));

    // Replace non letter or digits with the separator.
    $string = preg_replace('~[^\pL\d' . $seps . ']+~u', $sep, $string);

    // Transliterate.
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);

    // Remove anything that is not a word or a number or the separator(s).
    $string = preg_replace('~[^' . $seps . '\w]+~', '', $string);

    // Trim excess separator chars.
    $string = trim(trim($string), $seps);

    // Remove duplicate separators and lowercase.
    $string = strtolower(preg_replace('~[' . $seps . ']{2,}~', $sep, $string));

    // Empty strings are fine here.
    return $string;
}

/**
 * Renders a string using it as a template, with Handlebars-compativle syntax.
 *
 * @param string              $template The string template to render.
 * @param array<string,mixed> $data     An map of data to replace in the template.
 * @param array<mixed>        $fnArgs   An array of arguments that will be passed to each value, part of the data, that
 *                                      is a callable.
 * @return string The compiled template string.
 */
function renderString($template, array $data = [], array $fnArgs = [])
{
    $fnArgs = array_values($fnArgs);

    $replace = array_map(
        static function ($value) use ($fnArgs) {
            return is_callable($value) ? $value(...$fnArgs) : $value;
        },
        $data
    );

    if (false !== strpos($template, '{{#')) {
        /** @var \Closure $compiler */
        $compiler = \LightnCandy\LightnCandy::prepare(\LightnCandy\LightnCandy::compile($template));

        return $compiler($replace);
    }

    $search = array_map(
        static function ($k) {
            return '{{' . $k . '}}';
        },
        array_keys($data)
    );

    return str_replace($search, $replace, $template);
}

/**
 * Ensures a condition else throws an invalid argument exception.
 *
 * @param bool   $condition The condition to assert.
 * @param string $message   The exception message.
 */
function ensure($condition, $message)
{
    if ($condition) {
        return;
    }
    throw new \InvalidArgumentException($message);
}

/**
 * A safe wrapper around the `parse_url` function to ensure consistent return format.
 *
 * Differently from the internal implementation this one does not accept a component argument.
 *
 * @param string $url The input URL.
 *
 * @return array An array of parsed components, or an array of default values.
 */
function parseUrl($url)
{
    return \parse_url($url) ?: [
        'scheme' => '',
        'host' => '',
        'port' => 0,
        'user' => '',
        'pass' => '',
        'path' => '',
        'query' => '',
        'fragment' => ''
    ];
}

/**
 * Builds a \DateTimeImmutable object from another object, timestamp or `strtotime` parsable string.
 *
 * @param mixed $date A dates object, timestamp or `strtotime` parsable string.
 *
 * @return \DateTimeImmutable The built date or `now` date if the date is not parsable by the `strtotime` function.
 * @throws \Exception If the `$date` is a string not parsable by the `strtotime` function.
 */
function buildDate($date)
{
    if ($date instanceof \DateTimeImmutable) {
        return $date;
    }
    if ($date instanceof \DateTime) {
        return \DateTimeImmutable::createFromMutable($date);
    }

    return new \DateTimeImmutable(is_numeric($date) ? '@' . $date : $date);
}

/**
 * Converts the `preg_last_error` code into human-readable format.
 *
 * @param int $error The `preg_last_error` error code.
 *
 * @return string The `preg_last_error` message, translated in a human-readable form.
 */
function pregErrorMessage($error)
{
    return array_flip(array_filter(get_defined_constants(true)['pcre'], static function ($value) {
        return substr($value, -6) === '_ERROR';
    }, ARRAY_FILTER_USE_KEY))[preg_last_error()];
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
 * Returns a closure to get the value of an environment variable, loading a specific env file first.
 *
 * The function uses the Dotenv library to load the env file.
 *
 * @param string $file The name of the environment file to load.
 * @return \Closure A closure taking one argument, the environment variable name, to return its value or `null` if the
 *                     value is not defined.
 *
 * @throws \RuntimeException If the env file does not exist or is not readable.
 */
function envFile($file)
{
    if (!(file_exists($file))) {
        throw new \InvalidArgumentException('File ' . $file . ' does not exist.');
    }
    if (!is_readable($file)) {
        throw new \InvalidArgumentException('File ' . $file . ' is not readable.');
    }

    $envFileContents = file_get_contents($file);

    if ($envFileContents === false) {
        throw new \InvalidArgumentException('Could not read ' . $file . ' contents.');
    }

    $vars = array_reduce(array_filter(explode(PHP_EOL, $envFileContents)), static function (array $lines, $line) {
        if (preg_match('/^\\s*#/', $line)) {
            return $lines;
        }

        list($key, $value) = explode('=', $line);
        $lines[$key] = $value;
        return $lines;
    }, []);

    return static function ($key) use ($vars) {
        return isset($vars[$key]) ? $vars[$key] : null;
    };
}

/**
 * Returns the pretty, human-readable name of the current Operating System.
 *
 * @return string The human-readable name of the OS PHP is running on. One of `Windows`, `Linux`, 'macOS`, 'Solaris`,
 *                `BSD` or`Unknown`.
 */
function os()
{
    $constant = defined(PHP_OS_FAMILY) ? 'PHP_OS_FAMILY' : 'PHP_OS';
    $osSlug = strtolower(substr(constant($constant), 0, 3));

    $map = [
        'win' => 'Windows',
        'lin' => 'Linux',
        'dar' => 'macOS',
        'bsd' => 'BSD',
        'sol' => 'Solaris'
    ];

    return isset($map[$osSlug]) ? $map[$osSlug] : 'Unknown';
}

/**
 * Returns the path to the MySQL binary, aware of the current Operating System.
 *
 * @return string The name, and path, to the MySQL binary.
 */
function mysqlBin()
{
    return'mysql';
}

/**
 * Opens a process handle, starting the process, and returns a closure to read, write or terminate the process.
 *
 * The command is NOT escaped and should be escaped before being input into this function.
 *
 * @param array|string             $cmd The command to run, escaped if required..
 * @param string|string            $cwd The process working directory, or `null` to use the current one.
 * @param array<string,mixed>|null $env A map of the process environment variables; or `null` to use the current ones.
 *
 * @return \Closure A closure to read ($what = PROC_READ), write ($what = PROC_WRITE), read errors ($what = PROC_ERROR)
 *                  or close the process ($what = PROC_STATUS) and get its exit status.
 *
 * @throws \RuntimeException If the process cannot be started.
 */
function process($cmd = [], $cwd = null, $env = null)
{
    if (PHP_VERSION_ID < 70400) {
        $escapedCommand = implode(' ', $cmd);
    } else {
        // PHP 7.4 has introduced support for array commands and will handle the escaping.
        $escapedCommand = $cmd;
    }

    // `0` is STDIN, `1` is STDOUT, `2` is STDERR.
    $descriptors = [
        // Read from STDIN.
        0 => ['pipe', 'r'],
        // Write to STDOUT.
        1 => ['pipe', 'w'],
        // Write to STDERR.
        2 => ['pipe', 'w'],
    ];

    if (is_string($escapedCommand)) {
        codecept_debug('Running command: ' . $escapedCommand);
    } else {
        codecept_debug('Running command: ' . implode(' ', $escapedCommand));
    }

    $proc = proc_open($escapedCommand, $descriptors, $pipes, $cwd, $env);

    if (!is_resource($proc)) {
        throw new \RuntimeException('Process `' . $cmd . '` could not be started.');
    }

    return static function ($what = PROC_STATUS, ...$args) use ($proc, $pipes) {
        switch ($what) {
            case PROC_WRITE:
                return fwrite($pipes[0], reset($args));
                break;
            case PROC_READ:
                $length = isset($args[0]) ? (int)$args[0] : null;
                return $length !== null ? fgets($pipes[1], $length) : fgets($pipes[1]);
                break;
            case PROC_ERROR:
                $length = isset($args[0]) ? (int)$args[0] : null;
                return $length !== null ? fgets($pipes[2], $length) : fgets($pipes[2]);
                break;
            case PROC_CLOSE:
            case PROC_STATUS:
            default:
                $stdinClosed = fclose($pipes[0]);
                if (!$stdinClosed) {
                    throw new \RuntimeException('Could not close the process STDIN pipe.');
                }
                $stdinClosed = fclose($pipes[1]);
                if (!$stdinClosed) {
                    throw new \RuntimeException('Could not close the process STDOUT pipe.');
                }
                $stderrClosed = fclose($pipes[2]);
                if (!$stderrClosed) {
                    throw new \RuntimeException('Could not close the process STDERR pipe.');
                }

                return proc_close($proc);
                break;
        }
    };
}

/**
 * Imports a dump file using the `mysql` binary.
 *
 * @param string $dumpFile The path to the SQL dump file to import.
 * @param string $dbName   The name of the database to import the SQL dump file to.
 * @param string $dbUser The database user to use to import the dump.
 * @param string $dbPass The database password to use to import the dump.
 * @param string $dbHost The database host to use to import the dump.
 *
 * @return bool Whether the import was successful, exit status `0`, or not.
 */
function importDumpWithMysqlBin($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost')
{
    $dbPort = false;
    if (strpos($dbHost, ':') > 0) {
        list($dbHost, $dbPort) = explode(':', $dbHost);
    }

    $command = [mysqlBin(), '--host=' . escapeshellarg($dbHost), '--user='. escapeshellarg($dbUser)];
    if (!empty($dbPass)) {
        $command[] = '--password=' . escapeshellarg($dbPass);
    }
    if (!empty($dbPort)) {
        $command[] = '--port=' . escapeshellarg($dbPort);
    }

    $command = array_merge($command, [escapeshellarg($dbName), '<', escapeshellarg($dumpFile)]);

    $import = process($command);

    codecept_debug('Import output:' . $import(PROC_READ));
    codecept_debug('Import error:' . $import(PROC_ERROR));

    $status = $import(PROC_STATUS);

    codecept_debug('Import status: ' . $status);

    return $status === 0;
}

/**
 * Normalizes a string new line bytecode for comparison through Unix and Windows environments.
 *
 * @param string $str The string to normalize.
 *
 * @return string The normalized string.
 *
 * @see https://stackoverflow.com/a/7836692/2056484
 */
function normalizeNewLine($str)
{
    return preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $str);
}
