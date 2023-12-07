<?php

namespace lucatume\WPBrowser\WordPress;

use Exception;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\Utils\Property;
use ReflectionException;
use WP_Error;
use function str_starts_with;

class WPDieException extends Exception
{
    /**
     * @param array<int, array<string, mixed>> $trace
     */
    private function traceAsString(array $trace): string
    {
        $lines = [];
        foreach ($trace as $k => $item) {
            $lines[] = sprintf('#%d %s(%d): %s', $k, isset($item['file']) && is_string($item['file']) ? $item['file'] : '', isset($item['line']) && is_string($item['line']) ? $item['line'] : '', isset($item['function']) && is_string($item['function']) ? $item['function'] : '');
        }

        return implode("\n", $lines);
    }

    /**
     * @param string|array<string,mixed>|int $args
     * @throws ReflectionException
     * @param string|\WP_Error $message
     * @param string|int $title
     */
    public function __construct($message = '', $title = '', $args = [])
    {
        if ($message instanceof WP_Error) {
            $title = $message->get_error_data('title');
            $title = is_string($title) ? $title : '';
            $exitCode = (int)$message->get_error_code();
            $message = $message->get_error_message();
        } else {
            $title = $title ?: '';
            $message = $message ?: '';
            $exitCode = $args['code'] ?? 1;
            $exitCode = is_numeric($exitCode) ? (int)$exitCode : 1;
        }

        $message = strip_tags($title ? "$title - $message" : $message);

        parent::__construct($message, $exitCode, null);

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $wpDieCallPos = Arr::searchWithCallback(static function (array $item): bool {
            return $item['function'] === 'wp_die';
        }, $trace);
        $serializableClosurePos = Arr::searchWithCallback(static function (array $item): bool {
            return isset($item['file']) && strncmp($item['file'], 'closure://', strlen('closure://')) === 0;
        }, $trace);

        if (is_int($wpDieCallPos)) {
            if (is_int($serializableClosurePos)) {
                $trace = array_slice($trace, $wpDieCallPos, $serializableClosurePos - $wpDieCallPos);
            } else {
                $trace = array_slice($trace, $wpDieCallPos);
            }
        }

        $traceAsString = $this->traceAsString(array_values($trace));
        $traceAsString = str_replace("\n", "\n\t\t", $traceAsString);

        Property::setPrivateProperties($this, ['trace' => $trace, 'traceAsString' => $traceAsString]);
    }
}
