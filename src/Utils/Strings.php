<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use Exception;
use InvalidArgumentException;

class Strings
{
    /**
     * @param array<mixed> $elements
     */
    public static function andList(array $elements): string
    {
        if (!Arr::containsOnly($elements, static fn($v) => is_scalar($v))) {
            throw new InvalidArgumentException('The elements of the list must be scalars.');
        }

        /** @var array<string|int|float|bool> $elements */
        return match (count($elements)) {
            0 => '',
            1 => (string)reset($elements),
            default => implode(', ', array_slice($elements, 0, -1)) . ' and ' . end($elements)
        };
    }

    public static function isRegex(string $string): bool
    {
        try {
            return @preg_match($string, '') !== false;
        } catch (Exception) {
            return false;
        }
    }

    public static function slug(string $string, string $sep = '-', bool $let = false): string
    {
        $unquotedSeps = $let ? ['-', '_', $sep] : [$sep];
        $seps = implode('',
            array_map(static function ($s): string {
                return preg_quote($s, '~');
            }, array_unique($unquotedSeps)));

        // Prepend the separator to the first uppercase letter and trim the string.
        $step1 = preg_replace('/(?<![A-Z' . $seps . '])([A-Z])/u', $sep . '$1', trim($string));

        if ($step1 === null) {
            throw new InvalidArgumentException('Failed to slugify string');
        }

        // Replace non letter or digits with the separator.
        $step2 = preg_replace('~[^\pL\d' . $seps . ']+~u', $sep, $step1);

        if ($step2 === null) {
            throw new InvalidArgumentException('Failed to slugify string');
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
            throw new InvalidArgumentException('Failed to slugify string');
        }

        // Remove anything that is not a word or a number or the separator(s).
        $step4 = preg_replace('~[^' . $seps . '\w]+~', '', $step3);

        if ($step4 === null) {
            throw new InvalidArgumentException('Failed to slugify string');
        }

        // Trim excess separator chars.
        $step5 = trim(trim($step4), $seps);

        // Remove duplicate separators and lowercase.
        return strtolower((string)preg_replace('~[' . $seps . ']{2,}~', $sep, $step5));
    }

    /**
     * @param array<string,mixed> $data
     * @param array<mixed> $fnArgs
     */
    public static function renderString(string $template, array $data = [], array $fnArgs = []): string
    {
        $fnArgs = array_values($fnArgs);

        $replace = array_map(
            static fn($value) => is_callable($value) ? $value(...$fnArgs) : $value,
            $data
        );

        $search = array_map(static fn($k): string => '{{' . $k . '}}', array_keys($data));

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
        $username = self::removeAccents(self::stripTags($username));
        $username = (string)preg_replace(['|%([a-fA-F0-9][a-fA-F0-9])|', '/&.+?;/'], ['', ''], $username);

        if ($strict) {
            $username = (string)preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
        }

        return (string)preg_replace('|\s+|', ' ', trim($username));
    }

    public static function removeAccents(string $string): string
    {
        // Kind of light version of the WordPress `remove_accents` function.
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $chars = [
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A',
            chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A',
            chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A',
            chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I',
            chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O',
            chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O',
            chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O',
            chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U',
            chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U',
            chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a',
            chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e',
            chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e',
            chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i',
            chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i',
            chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A',
            chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A',
            chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A',
            chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C',
            chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C',
            chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C',
            chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C',
            chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D',
            chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D',
            chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E',
            chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E',
            chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E',
            chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E',
            chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E',
            chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G',
            chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G',
            chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G',
            chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G',
            chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H',
            chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H',
            chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I',
            chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I',
            chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I',
            chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I',
            chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I',
            chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ',
            chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J',
            chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K',
            chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k',
            chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l',
            chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l',
            chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l',
            chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l',
            chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l',
            chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n',
            chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n',
            chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n',
            chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n',
            chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O',
            chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O',
            chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O',
            chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE',
            chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R',
            chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R',
            chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R',
            chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S',
            chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S',
            chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S',
            chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S',
            chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T',
            chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T',
            chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T',
            chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U',
            chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U',
            chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U',
            chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U',
            chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U',
            chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U',
            chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W',
            chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y',
            chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y',
            chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z',
            chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z',
            chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z',
            chr(197) . chr(191) => 's'
        ];

        return strtr($string, $chars);
    }
}
