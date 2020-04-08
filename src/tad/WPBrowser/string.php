<?php
/**
 * String manipulation functions.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

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
 * Checks whether a string is a regular expression or not.
 *
 * @param string $string The candidate regular expression to check.
 *
 * @return bool Whether a string is a regular expression or not.
 */
function isRegex($string)
{
    try {
        return preg_match($string, null) !== false;
    } catch (\Exception $e) {
        return false;
    }
}
