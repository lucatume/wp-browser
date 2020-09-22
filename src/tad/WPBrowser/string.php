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
    return (string)preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $str);
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
    $step1 = preg_replace('/(?<![A-Z' . $seps . '])([A-Z])/u', $sep . '$1', trim($string));

    if ($step1 === null) {
        throw new \InvalidArgumentException('Failed to slugify string');
    }

    // Replace non letter or digits with the separator.
    $step2 = preg_replace('~[^\pL\d' . $seps . ']+~u', $sep, $step1);

    if ($step2 === null) {
        throw new \InvalidArgumentException('Failed to slugify string');
    }

    // Transliterate.
    $step3 = iconv('utf-8', 'us-ascii//TRANSLIT', $step2);

    if ($step3 === false) {
        throw new \InvalidArgumentException('Failed to slugify string');
    }

    // Remove anything that is not a word or a number or the separator(s).
    $step4 = preg_replace('~[^' . $seps . '\w]+~', '', $step3);

    if ($step4 === null) {
        throw new \InvalidArgumentException('Failed to slugify string');
    }

    // Trim excess separator chars.
    $step5 = trim(trim($step4), $seps);

    // Remove duplicate separators and lowercase.
    $step6 = strtolower((string)preg_replace('~[' . $seps . ']{2,}~', $sep, $step5));

    // Empty strings are fine here.
    return $step6;
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
        $php = \LightnCandy\LightnCandy::compile($template);

        if ($php === false) {
            throw new \RuntimeException('Failed to compile template');
        }

        /** @var \Closure $compiler */
        $compiler = \LightnCandy\LightnCandy::prepare($php);

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
 * @return array<string,string|int> An array of parsed components, or an array of default values.
 *
 * @throws \InvalidArgumentException If the URL cannot be parsed at all.
 */
function parseUrl($url)
{
    $parsed = \parse_url($url);

    if (!is_array($parsed)) {
        throw new \InvalidArgumentException("Failed to parse URL {$url}");
    }

    return array_merge([
        'scheme'   => '',
        'host'     => '',
        'port'     => 0,
        'user'     => '',
        'pass'     => '',
        'path'     => '',
        'query'    => '',
        'fragment' => ''
    ], $parsed);
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
        // @phpstan-ignore-next-line
        return @preg_match($string, null) !== false;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Builds the string list using `and` for the last element.
 *
 * @param array<string> $elements The list elements.
 *
 * @return string|false The list in string format or `false` if the list is empty.
 */
function andList(array $elements)
{
    $list  = '';
    $count = count($elements);

    if ($count === 0) {
        return $list;
    }

    if ($count === 1) {
        return reset($elements);
    }

    for ($i = 0; $i < $count; $i ++) {
        if ($i === 0) {
            $list .= $elements[$i];
            continue;
        }
        $glue = $i === $count - 1 ? ' and ' : ', ';
        $list .= $glue . $elements[$i];
    }

    return $list;
}

/**
 * Returns the domain from the full URL.
 *
 * @param string $fullUrl The full URL to build the domain from.
 *
 * @return string The domain built from the full URL.
 */
function urlDomain($fullUrl)
{
    $frags = parseUrl($fullUrl);

    return sprintf(
        '%s%s%s',
        $frags['host'],
        $frags['port'] ? ':' . $frags['port'] : '',
        $frags['path']
    );
}
