<?php

namespace Env;

class Env
{
    const CONVERT_BOOL = 1;
    const CONVERT_NULL = 2;
    const CONVERT_INT = 4;
    const STRIP_QUOTES = 8;
    const USE_ENV_ARRAY = 16;
    const LOCAL_FIRST = 32;
    const USE_SERVER_ARRAY = 64;

    public static $options = 15;   // CONVERT_* + STRIP_QUOTES enabled
    public static $default = null; // Default value if not exists

    /**
     * Returns an environment variable.
     */
    public static function get(string $name)
    {
        if (self::$options & self::USE_ENV_ARRAY) {
            $value = isset($_ENV[$name]) ? $_ENV[$name] : false;
        } elseif (self::$options & self::USE_SERVER_ARRAY) {
            $value = isset($_SERVER[$name]) ? $_SERVER[$name] : false;
        } elseif (self::$options & self::LOCAL_FIRST) {
            $value = getenv($name, true);

            if ($value === false) {
                $value = getenv($name);
            }
        } else {
            $value = getenv($name);
        }

        if ($value === false) {
            return self::$default;
        }

        return self::convert($value);
    }

    /**
     * Converts the type of values like "true", "false", "null" or "123".
     * 
     * @return mixed
     */
    public static function convert(string $value, int $options = null)
    {
        if ($options === null) {
            $options = self::$options;
        }

        switch (strtolower($value)) {
            case 'true':
                return ($options & self::CONVERT_BOOL) ? true : $value;

            case 'false':
                return ($options & self::CONVERT_BOOL) ? false : $value;

            case 'null':
                return ($options & self::CONVERT_NULL) ? null : $value;
        }

        if (($options & self::CONVERT_INT) && ctype_digit($value)) {
            return (int) $value;
        }

        if (($options & self::STRIP_QUOTES) && !empty($value)) {
            return self::stripQuotes($value);
        }

        return $value;
    }

    /**
     * Strip quotes.
     */
    private static function stripQuotes(string $value): string
    {
        if (
            ($value[0] === '"' && substr($value, -1) === '"')
         || ($value[0] === "'" && substr($value, -1) === "'")
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
