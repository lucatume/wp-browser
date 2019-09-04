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
 * @param string $sep    The separator character to use, defaults to `-`.
 * @param bool   $let    Whether to let other common separators be or not.
 *
 * @return string The slug version of the string.
 */
function slug($string, $sep = '-', $let = false)
{
    $unquotedSeps = $let ? [ '-', '_', $sep ] : [$sep];
    $seps   = implode('', array_map(static function ($s) {
        return preg_quote($s, '~');
    }, array_unique($unquotedSeps)));

    // Prepend the separator to the first uppercase letter and trim the string.
    $string = preg_replace('/(?<![A-Z'. $seps .'])([A-Z])/u', $sep.'$1', trim($string));

    // Prepend the separator to the first number not preceded by a number and trim the string.
    $string = preg_replace('/(?<![0-9'. $seps .'])([0-9])/u', $sep.'$1', trim($string));


    // Replace non letter or digits with the separator.
    $string = preg_replace('~[^\pL\d'. $seps .']+~u', $sep, $string);

    // Transliterate.
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);

    // Remove anything that is not a word or a number or the separator(s).
    $string = preg_replace('~[^'. $seps .'\w]+~', '', $string);

    // Trim excess separator chars.
    $string = trim($string, $seps);

    // Remove duplicate separators and lowercase.
    $string = strtolower(preg_replace('~['. $seps .']{2,}~', $sep, $string));

    // Empty strings are fine here.
    return $string;
}

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
