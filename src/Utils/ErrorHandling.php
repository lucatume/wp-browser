<?php

namespace lucatume\WPBrowser\Utils;

class ErrorHandling
{
    /**
     * @param array<array<string,mixed>> $trace
     */
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

    /**
     * @param array<array<string,mixed>> $serializableTrace
     * @return array<array<string,mixed>>
     */
    public static function makeTraceSerializable(array $serializableTrace): array
    {
        foreach ($serializableTrace as &$frame) {
            if (!isset($frame['args'])) {
                continue;
            }
            foreach ($frame['args'] as &$arg) {
                if (is_object($arg) && !method_exists($arg, '__serialize')) {
                    $arg = $arg::class;
                } elseif (is_resource($arg)) {
                    $arg = 'resource';
                }
            }
        }

        return $serializableTrace;
    }
}
