<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class Composer
{
    public static function vendorDir(?string $path = null): string
    {
        $vendorDir = dirname(self::autoloadPath());
        return $path ? $vendorDir . DIRECTORY_SEPARATOR . ltrim($path) : $vendorDir;
    }

    public static function autoloadPath(): string
    {
        global $_composer_autoload_path;
        return realpath($_composer_autoload_path) ?: $_composer_autoload_path;
    }

    public static function binDir(?string $path = null): string
    {
        global $_composer_bin_dir;
        $binDir = rtrim($_composer_bin_dir ?? self::vendorDir('/bin'), '\\/');
        return $path ? $binDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/') : $binDir;
    }
}
