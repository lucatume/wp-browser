<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class CorePHPUnit
{
    public static function path(?string $string = null): string
    {
        $path = dirname(__DIR__, 2) . '/includes/core-phpunit';

        return $string ? $path . '/' . ltrim($string, '\\/') : $path;
    }

    public static function includes(string $path)
    {
    }
}
