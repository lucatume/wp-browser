<?php

namespace lucatume\WPBrowser;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Memo;

/**
 * @param callable|array{0: string, 1: string}|string $callback
 * @param array<scalar> $dependencies
 */
function useMemo(callable|array|string $callback, array $dependencies = []): mixed
{
    if (!is_callable($callback)) {
        throw new InvalidArgumentException('The callback is not callable.');
    }

    /** @noinspection DebugFunctionUsageInspection */
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $key = md5(serialize($backtrace));

    $cached = Memo::get($key, serialize($dependencies), '__not_cached');

    if ($cached !== '__not_cached') {
        return $cached;
    }

    $result = $callback();

    Memo::set($key, serialize($dependencies), $result);

    return $result;
}

/**
 * @param callable|array{0: string, 1: string}|string $callback
 * @param array<scalar> $dependencies
 */
function useMemoString(callable|array|string $callback, array $dependencies = []): string
{
    $result = useMemo($callback, $dependencies);
    if (!is_string($result)) {
        throw new RuntimeException('The result of the callback is not a string.');
    }
    return $result;
}
