<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use Hoa\Compiler\Llk\Sampler\Exception;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use Throwable;

class Random
{
    /**
     * @var string
     */
    private static $saltChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789' .
    '!#$%&()*+,-./:;<>?@[]^_`{|}~';
    /**
     * @var int
     */
    private static $saltCharsCount = 90;
    /**
     * @var string
     */
    private static $alphaChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    /**
     * @var int
     */
    private static $alphaCharsCount = 52;
    /**
     * @var string
     */
    private static $dbNameChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';
    /**
     * @var int
     */
    private static $dbNameCharsCount = 63;

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

    /**
     * @throws \RuntimeException|\Exception
     */
    public static function openLocalhostPort(): int
    {
        $attempts = 0;
        $testedPorts = [];
        while ($attempts++ < 10) {
            do {
                $port = random_int(1025, 65535);
            } while (in_array($port, $testedPorts, true));

            $testedPorts[] = $port;

            if (!Ports::isPortOccupied($port)) {
                return $port;
            }
        }

        throw new RuntimeException(
            'Could not start PHP built-in server to find free localhost port after many attempts.'
        );
    }
}
