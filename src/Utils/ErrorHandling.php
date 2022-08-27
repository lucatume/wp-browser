<?php

namespace lucatume\WPBrowser\Utils;

class ErrorHandling
{
    public const E_All_WARNINGS = E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING;
    public const E_ALL_NOTICES = E_NOTICE | E_USER_NOTICE;

    public static function throwErrors(int $errorLevels): void
    {
        $errorHandler = static function (int $errno, string $errstr): bool {
            throw new \Error($errstr, $errno);

            return false;
        };
        set_error_handler($errorHandler, $errorLevels);
    }

    public static function traceAsString(array $trace): string
    {
        $lines = [];
        foreach ($trace as $k => $item) {
            $lines[] = sprintf(
                '#%d %s(%d): %s',
                $k,
                $item['file'] ?? '',
                $item['line'] ?? '',
                $item['function'] ?? ''
            );
        }

        return implode("\n", $lines);
    }

    public static function makeTraceSerializable(array $serializableTrace): array
    {
        foreach ($serializableTrace as &$frame) {
            if (!isset($frame['args'])) {
                continue;
            }
            foreach ($frame['args'] as &$arg) {
                if (is_object($arg) && !method_exists($arg, '__serialize')) {
                    $arg = get_class($arg);
                } elseif (is_resource($arg)) {
                    $arg = 'resource';
                }
            }
        }

        return $serializableTrace;
    }
}
