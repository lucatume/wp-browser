<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class Random
{
    private static string $saltChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' .
    '!"#$%&()*+,-./:;<=>?@[]^_`{|}~';
    private static int $saltCharsCount = 92;
    private static string $alphaChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    private static int $alphaCharsCount = 52;
    private static string $dbNameChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';
    private static int $dbNameCharsCount = 63;

    public static function salt(int $length = 64): string
    {
        $generated = '';
        $generatedCount = 0;
        while ($generatedCount < $length) {
            $generated .= self::$saltChars[random_int(0, self::$saltCharsCount - 1)];
            $generatedCount++;
        }

        return $generated;
    }

    public static function dbName(int $length = 24): string
    {
        // Between 2 and 24 chars long.
        $length = max(2, min($length, 24));

        do {
            // Generate the first two alpha characters, avoid 'ii' as it's a reserved prefix.
            $generated = self::$alphaChars[random_int(0, self::$alphaCharsCount - 1)]
                . self::$alphaChars[random_int(0, self::$alphaCharsCount - 1)];
        } while ($generated === 'ii');

        // Use a safe limit of 24 characters, 2 already used.
        $generatedCount = 2;
        while ($generatedCount < $length) {
            $generated .= self::$dbNameChars[random_int(0, self::$dbNameCharsCount - 1)];
            $generatedCount++;
        }

        return $generated;
    }
}
