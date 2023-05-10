<?php

namespace lucatume\WPBrowser\Utils;

class Arr
{

    /**
     * @param array<int|string,mixed> $haystack
     */
    public static function searchWithCallback(callable $isNeedle, array $haystack): int|string|false
    {
        $index = false;
        foreach ($haystack as $key => $value) {
            if ($isNeedle($value, $key)) {
                $index = $key;
                break;
            }
        }
        return $index;
    }

    public static function firstFrom(mixed $value, mixed $default = null): mixed
    {
        if (is_array($value)) {
            return reset($value);
        }

        return $default;
    }
}
