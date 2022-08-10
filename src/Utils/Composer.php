<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class Composer
{
    private static ?string $vendorDir = null;

    public static function vendorDir(string $path = null): string
    {
        if (self::$vendorDir === null) {
            global $_composer_autoload_path;
            $composerAutoloadPathRealpath = realpath($_composer_autoload_path);
            self::$vendorDir = dirname($composerAutoloadPathRealpath);
        }

        return $path ? self::$vendorDir . DIRECTORY_SEPARATOR . ltrim($path) : self::$vendorDir;
    }
}
