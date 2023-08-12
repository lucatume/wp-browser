<?php

namespace lucatume\WPBrowser\Utils;

class Arr
{

    /**
     * @param array<int|string,mixed> $haystack
     */
    public static function searchWithCallback(callable $isNeedle, array $haystack): int|string|false
    {
        foreach ($haystack as $key => $value) {
            if ($isNeedle($value, $key)) {
                return $key;
            }
        }
        return false;
    }

    public static function firstFrom(mixed $value, mixed $default = null): mixed
    {
        if ($value && is_array($value)) {
            return reset($value);
        }

        return $default;
    }

    /**
     * @param array<string|int,mixed> $array
     * @param array<string|int,string|callable> $types
     */
    public static function hasShape(array $array, array $types): bool
    {
        foreach ($types as $k => $type) {
            if (!isset($array[$k])) {
                return false;
            }

            if (is_string($type)) {
                $check = is_scalar($array[$k]) && function_exists('is_' . $type) ? 'is_' . $type : 'is_a';
            } else {
                $check = $type;
            }

            $args = $check === 'is_a' ? [$array[$k], $type, true] : [$array[$k]];

            if (!$check(...$args)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array<string|int,mixed> $array
     */
    public static function containsOnly(array $array, string|callable $type): bool
    {
        if (is_string($type)) {
            $check = function_exists('is_' . $type) ? 'is_' . $type : 'is_a';
        } else {
            $check = $type;
        }

        foreach ($array as $value) {
            $args = $check === 'is_a' ? [$value, $type, true] : [$value];
            if (!$check(...$args)) {
                return false;
            }
        }

        return true;
    }

    public static function isAssociative(array $array): bool
    {
        return array_filter(array_keys($array), 'is_string') === array_keys($array);
    }
}
