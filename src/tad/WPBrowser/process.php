<?php
/**
 * Process and external command execution functions.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

use tad\WPBrowser\Utils\Map;

const PROC_CLOSE    = 'proc_close';
const PROC_STATUS   = 'proc_status';
const PROC_WRITE    = 'proc_write';
const PROC_READ     = 'proc_read';
const PROC_ERROR    = 'proc_error';
const PROC_REALTIME = 'proc_realtime';

/**
 * Returns a map to check on a process status.
 *
 * @param resource $proc_handle The process handle.
 *
 * @return \Closure A function to check and return the process status map.
 *
 * @throws \RuntimeException If the process current status cannot be fetched.
 */
function processStatus($proc_handle)
{
    return static function ($what, $default = null) use ($proc_handle) {
        $status = proc_get_status($proc_handle);

        if ($status === false) {
            throw new \RuntimeException('Failed to gather the process current status.');
        }

        $map   = new Map($status);
        $value = $map($what, $default);
        unset($map);

        return $value;
    };
}

/**
 * Reads the whole content of a process pipe.
 *
 * @param resource $pipe   The pipe to read from.
 * @param int|null $length Either the length of the string to read, or `null` to read the whole pipe contents.
 *
 * @return string The string read from the pipe.
 */
function processReadPipe($pipe, $length = null)
{
    $read = [];
    while (false !== $line = fgets($pipe)) {
        $read[] = $line;
    }

    $readString = implode('', $read);

    return $length ? (string) substr($readString, 0, $length) : $readString;
}

/**
 * Opens a process handle, starting the process, and returns a closure to read, write or terminate the process.
 *
 * The command is NOT escaped and should be escaped before being input into this function.
 *
 * @param array<string>|string     $cmd The command to run, escaped if required..
 * @param string|null            $cwd The process working directory, or `null` to use the current one.
 * @param array<string,mixed>|null $env A map of the process environment variables; or `null` to use the current ones.
 *
 * @return \Closure A closure to read ($what = PROC_READ), write ($what = PROC_WRITE), read errors ($what = PROC_ERROR)
 *                  or close the process ($what = PROC_STATUS) and get its exit status.
 *
 * @throws \RuntimeException If the process cannot be started.
 */
function process($cmd = [], $cwd = null, $env = null)
{
    if (PHP_VERSION_ID < 70400 && is_array($cmd)) {
        $escapedCommand = implode(' ', $cmd);
    } else {
        // PHP 7.4 has introduced support for array commands and will handle the escaping.
        $escapedCommand = $cmd;
    }

    // `0` is STDIN, `1` is STDOUT, `2` is STDERR.
    $descriptors = [
        // Read from STDIN.
        0 => [ 'pipe', 'r' ],
        // Write to STDOUT.
        1 => [ 'pipe', 'w' ],
        // Write to STDERR.
        2 => [ 'pipe', 'w' ],
    ];

    if (is_string($escapedCommand)) {
        debug('Running command: ' . $escapedCommand);
    } else {
        debug('Running command: ' . implode(' ', $escapedCommand));
    }

    // @phpstan-ignore-next-line
    $proc = proc_open($escapedCommand, $descriptors, $pipes, $cwd, $env);

    if (! is_resource($proc)) {
        $cmd = is_array($cmd) ? implode(' ', $cmd) : $cmd;
        throw new \RuntimeException('Process "' . $cmd . '" could not be started.');
    }

    return static function ($what = PROC_STATUS, ...$args) use ($proc, $pipes) {
        switch ($what) {
            case PROC_WRITE:
                return fwrite($pipes[0], reset($args));
            case PROC_READ:
                $length = isset($args[0]) ? (int) $args[0] : null;

                return processReadPipe($pipes[1], $length);
            case PROC_ERROR:
                $length = isset($args[0]) ? (int) $args[0] : null;

                return processReadPipe($pipes[2], $length);
            /** @noinspection PhpMissingBreakStatementInspection */
            case PROC_REALTIME:
                $callback = $args[0];
                if (! is_callable($callback)) {
                    throw new \InvalidArgumentException('Realtime callback should be callable.');
                }
                do {
                    $currentStatus = processStatus($proc);
                    foreach ([ 2 => PROC_ERROR, 1 => PROC_READ ] as $pipe => $type) {
                        $callback($type, processReadPipe($pipes[ $pipe ]));
                    }
                } while ($currentStatus('running', false));
            // Let the process close after realtime.
            case PROC_CLOSE:
            case PROC_STATUS:
            default:
                $stdinClosed = fclose($pipes[0]);
                if (! $stdinClosed) {
                    throw new \RuntimeException('Could not close the process STDIN pipe.');
                }
                $stdinClosed = fclose($pipes[1]);
                if (! $stdinClosed) {
                    throw new \RuntimeException('Could not close the process STDOUT pipe.');
                }
                $stderrClosed = fclose($pipes[2]);
                if (! $stderrClosed) {
                    throw new \RuntimeException('Could not close the process STDERR pipe.');
                }

                return proc_close($proc);
        }
    };
}

/**
 * Builds an array format command line, compatible with the Symfony Process component, from a string command line.
 *
 * @param string|array<string> $command The command line to parse, if in array format it will not be modified.
 *
 * @return array<string> The parsed command line, in array format. Untouched if originally already an array.
 */
function buildCommandline($command)
{
    if (empty($command) || is_array($command)) {
        return array_filter((array) $command);
    }

    $escapedCommandLine = ( new \Symfony\Component\Process\Process($command) )->getCommandLine();
    $commandLineFrags   = explode(' ', $escapedCommandLine);

    if (count($commandLineFrags) === 1) {
        return $commandLineFrags;
    }

    $open                   = false;
    $unescapedQuotesPattern = '/(?<!\\\\)("|\')/u';

    return array_reduce($commandLineFrags, static function (array $acc, $v) use (&$open, $unescapedQuotesPattern) {
        $containsUnescapedQuotes = preg_match_all($unescapedQuotesPattern, $v);
        $v                       = $open ? array_pop($acc) . ' ' . $v : $v;
        $open                    = $containsUnescapedQuotes ?
            $containsUnescapedQuotes & 1 && (bool) $containsUnescapedQuotes !== $open
            : $open;
        $acc[]                   = preg_replace($unescapedQuotesPattern, '', $v);

        return $acc;
    }, []);
}
