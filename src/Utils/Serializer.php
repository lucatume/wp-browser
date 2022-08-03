<?php
/**
 * Provides methods to serialize and unserialize data.
 *
 * @package lucatume\WPBrowser\Utils;
 */

namespace lucatume\WPBrowser\Utils;

/**
 * Class Serializer.
 *
 * @package lucatume\WPBrowser\Utils;
 */
class Serializer
{
    public static function maybeUnserialize(mixed $value): mixed
    {
        if (!self::isSerialized($value)) {
            return $value;
        }

        return match (substr($value, 0, 4)) {
            'N;' => null,
            'b:1;' => true,
            'b:0;' => false,
            default => @unserialize($value, ['allowed_classes' => true])
        };
    }

    public static function isSerialized(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return match (substr($value, 0, 2)) {
            'N;', 'b:', 'i:', 'd:', 's:', 'a:', 'O:', 'C:' => true,
            default => false
        };
    }

    public static function maybeSerialize(mixed $value): mixed
    {
        return self::isSerialized($value) ? $value : serialize($value);
    }
}
