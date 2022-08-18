<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use VRia\Utils\NoDiacritic;

class Strings
{
    public static function andList(array $elements): string
    {
        return match (count($elements)) {
            0 => '',
            1 => reset($elements),
            default => implode(', ', array_slice($elements, 0, -1)) . ' and ' . end($elements)
        };
    }

    public static function isRegex(string $string): bool
    {
        try {
            // @phpstan-ignore-next-line
            return @preg_match($string, '') !== false;
        } catch (\Exception) {
            return false;
        }
    }

    public static function slug(string $string, $sep = '-', $let = false): string
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
        return strtolower((string)preg_replace('~[' . $seps . ']{2,}~', $sep, $step5));
    }

    public static function renderString(string $template, array $data = [], array $fnArgs = []): string
    {
        $fnArgs = array_values($fnArgs);

        $replace = array_map(
            static fn($value) => is_callable($value) ? $value(...$fnArgs) : $value,
            $data
        );

        $search = array_map(static fn($k) => '{{' . $k . '}}', array_keys($data));

        return str_replace($search, $replace, $template);
    }

    public static function normalizeNewLine(string $str): string
    {
        return (string)preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $str);
    }

    public static function stripTags(string $string, bool $removeBreaks = false): string
    {
        $woTags = (string)preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);

        $string = strip_tags($woTags);

        if ($removeBreaks) {
            $string = (string)preg_replace('/[\r\n\t ]+/', ' ', $string);
        }

        return trim($string);
    }

    public static function sanitizeUsername(string $username, bool $strict): string
    {
        $username = NoDiacritic::filter(self::stripTags($username));
        $username = (string)preg_replace(['|%([a-fA-F0-9][a-fA-F0-9])|', '/&.+?;/'], ['', ''], $username);

        if ($strict) {
            $username = (string)preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
        }

        return (string)preg_replace('|\s+|', ' ', trim($username));
    }
}
