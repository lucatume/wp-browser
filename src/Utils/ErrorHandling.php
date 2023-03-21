<?php

namespace lucatume\WPBrowser\Utils;

class ErrorHandling
{
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
