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

/**
 * Create the slug version of a string.
 *
 * This will also convert `camelCase` to `camel-case`.
 *
 * @param string $string The string to create a slug for.
 * @param string $sep The separator character to use, defaults to `-`.
 *
 * @return string The slug version of the string.
 */
function slug($string, $sep = '-')
{
    // Prepend the separator to the first uppercase letter and trim the string.
    $string = preg_replace('/(?<![A-Z])([A-Z])/u', $sep.'$1', trim($string));

    // Prepend the separator to the first number not preceded by a number and trim the string.
    $string = preg_replace('/(?<![0-9])([0-9])/u', $sep.'$1', trim($string));

    // Replace non letter or digits with the separator.
    $string = preg_replace('~[^\pL\d]+~u', $sep, $string);


    // Transliterate.
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);

    // Remove anything that is not a word or a number or the separator.
    $string = preg_replace('~[^'.preg_quote($sep, '~').'\w]+~', '', $string);

    // Trim excess separator chars.
    $string = trim($string, $sep);

    // Remove duplicate separators and lowercase.
    $string = strtolower(preg_replace('~'.preg_quote($sep, '~').'+~', $sep, $string));

    // Empty strings are fine here.
    return $string;
}
