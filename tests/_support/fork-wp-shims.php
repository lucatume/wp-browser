<?php
/**
 * Global-namespace WordPress function shims for fork children.
 * The mocked WPLoader flows exercised in forks call these without loading WordPress.
 */

if (!function_exists('do_action')) {
    function do_action(string $hook_name, ...$arg): void
    {
        $GLOBALS['__fork_did_actions'][$hook_name] = ($GLOBALS['__fork_did_actions'][$hook_name] ?? 0) + 1;
    }
}

if (!function_exists('did_action')) {
    function did_action(string $hook_name): int
    {
        return $GLOBALS['__fork_did_actions'][$hook_name] ?? 0;
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool
    {
        return true;
    }
}

if (!function_exists('remove_filter')) {
    function remove_filter(string $hook_name, callable $callback, int $priority = 10): bool
    {
        return true;
    }
}

if (!function_exists('wp_cache_flush')) {
    function wp_cache_flush(): bool
    {
        return true;
    }
}
