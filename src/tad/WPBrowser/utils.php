<?php
/**
 * Miscellaneous utility functions for the wp-browser library.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

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
    if (empty($command)|| is_array($command)) {
        return array_filter((array)$command);
    }

    $escapedCommandLine = ( new \Symfony\Component\Process\Process($command) )->getCommandLine();
    $commandLineFrags   = explode(' ', $escapedCommandLine);

    if (count($commandLineFrags) === 1) {
        return $commandLineFrags;
    }

    $open = false;
    $unescapedQuotesPattern  = '/(?<!\\\\)"/u';

    return array_reduce($commandLineFrags, static function (array $acc, $v) use (&$open, $unescapedQuotesPattern) {
        $containsUnescapedQuotes = preg_match($unescapedQuotesPattern, $v);
        $v                       = $open && $containsUnescapedQuotes ? array_pop($acc) . ' ' . $v : $v;
        $open                    = ! $open && $containsUnescapedQuotes;
        $acc[]                   = preg_replace($unescapedQuotesPattern, '', $v);

        return $acc;
    }, []);
}
