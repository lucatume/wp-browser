<?php
global $cli_headers, $cli_statuses;
/**
 * An associative array in the [header => status] format.
 */
$cli_headers = [];

/**
 * An array of statuses in the format `HTTP/1.1 200 Ok`
 */
$cli_statuses = [];

function cli_headers_list()
{
    $headers = [];
    global $cli_headers;
    $php_headers = array_keys($cli_headers);
    foreach ($php_headers as $value) {
        // Get the header name
        $parts = explode(':', $value);
        if (count($parts) > 1) {
            $name = trim(array_shift($parts));
            // Build the header hash map
            $headers[$name] = trim(implode(':', $parts));
        }
    }
    $headers['Content-type'] = isset($headers['Content-type'])
        ? $headers['Content-type']
        : "text/html; charset=UTF-8";

    return $headers;
}

function cli_headers_last_status()
{
    global $cli_statuses;
    if (empty($cli_statuses)) {
        return 200;
    }

    return end($cli_statuses);
}

/**
 * Shameless copies of WordPress functions defined in the Core suite
 */

function wpbrowser_buildFilterUniqueId($tag, $function, $priority)
{
    if (is_string($function)) {
        return $function;
    }

    if (is_object($function)) {
        // Closures are currently implemented as objects
        $function = array( $function, '' );
    } else {
        $function = (array) $function;
    }

    if (is_object($function[0])) {
        return spl_object_hash($function[0]) . $function[1];
    } elseif (is_string($function[0])) {
        // Static Calling
        return $function[0].$function[1];
    }
}

function wpbrowser_addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
{
    global $wp_filter;

    if (function_exists('add_filter')) {
        add_filter($tag, $function_to_add, $priority, $accepted_args);
    } else {
        $idx = wpbrowser_buildFilterUniqueId($tag, $function_to_add, $priority);
        $wp_filter[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
    }
    return true;
}
