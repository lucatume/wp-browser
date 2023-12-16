<?php

namespace lucatume\WPBrowser\Utils;

class Memo
{

    /**
     * @var array<string,array<string,mixed>>
     */
    private static $cache = [];

    public static function get(string $key, string $subKey, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])
            && is_array(self::$cache[$key])
            && isset(self::$cache[$key][$subKey])) {
            return self::$cache[$key][$subKey];
        }

        return $default;
    }

    public static function set(string $key, string $subKey, mixed $result): void
    {
        self::$cache[$key] = [];
        self::$cache[$key][$subKey] = $result;
    }

    public static function reset(): void
    {
        self::$cache = [];
    }
}
