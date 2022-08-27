<?php
namespace lucatume\WPBrowser\Utils;

class Arr
{

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
}
