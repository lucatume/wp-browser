<?php
/**
 * String manipulation functions.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser;

/**
 * Normalizes a string new line bytecode for comparison through Unix and Windows environments.
 *
 * @param string $str The string to normalize.
 *
 * @return string The normalized string.
 *
 * @see https://stackoverflow.com/a/7836692/2056484
 */
function normalizeNewLine($str): string
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
function slug($string, $sep = '-', $let = false): string
{
    $unquotedSeps = $let ? ['-', '_', $sep] : [$sep];
    $seps = implode('', array_map(static function ($s): string {
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
    if (function_exists('transliterator_transliterate')) {
        // From the `intl` extension
        $step3 = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7fff] remove', $step2);
    } elseif (function_exists('iconv')) {
        // From the `iconv` extension.
        $step3 = iconv('utf-8', 'us-ascii//TRANSLIT', $step2);
    } else {
        // No extension available: fallback on a simpler approach.
        $step3 = preg_replace('/[^A-Za-z0-9' . preg_quote($sep, '/') . ']/', '-', $step2);
    }

    if (!is_string($step3)) {
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
 * Renders a string using it as a template, with Handlebars-compatible syntax.
 *
 * @param string              $template The string template to render.
 * @param array<string,mixed> $data     An map of data to replace in the template.
 * @param array<mixed>        $fnArgs   An array of arguments that will be passed to each value, part of the data, that
 *                                      is a callable.
 * @return string The compiled template string.
 */
function renderString($template, array $data = [], array $fnArgs = []): string
{
    $fnArgs = array_values($fnArgs);

    $replace = array_map(
        static function ($value) use ($fnArgs) {
            return is_callable($value) ? $value(...$fnArgs) : $value;
        },
        $data
    );

    if (str_contains($template, '{{#')) {
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
