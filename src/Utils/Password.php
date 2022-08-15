<?php
namespace lucatume\WPBrowser\Utils;

class Password
{
    private static string $saltChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!"#$%&()*+,-./:;<=>?@[]^_`{|}~';
    private static int $saltCharsCount = 92;

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
}
