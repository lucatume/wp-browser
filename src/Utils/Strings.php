<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

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
}
