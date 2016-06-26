<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

$indexFile = $argv[1];

$env = unserialize(base64_decode($argv[2]));

foreach ($env['cookie'] as $key => $value) {
    $_COOKIE[$key] = $value;
}
foreach ($env['server'] as $key => $value) {
    $_SERVER[$key] = $value;
}
foreach ($env['files'] as $key => $value) {
    $_FILES[$key] = $value;
}
foreach ($env['request'] as $key => $value) {
    $_REQUEST[$key] = $value;
}
if (!empty($env['get'])) {
    foreach ($env['get'] as $key => $value) {
        $_GET[$key] = $value;
    }
}
if (!empty($env['post'])) {
    foreach ($env['post'] as $key => $value) {
        $_POST[$key] = $value;
    }
}
foreach ($env['headers'] as $header) {
    header($header);
}

function wpbrowser_handle_shutdown()
{
    $response = [];

    $contents = ob_get_contents();
    ob_end_clean();

    $response['content'] = $contents;

    $headers = [];
    $php_headers = headers_list();
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

    $response['headers'] = $headers;
    $response['cookie'] = $_COOKIE;
    $response['server'] = $_SERVER;
    $response['files'] = $_FILES;
    $response['request'] = $_REQUEST;
    $response['get'] = $_GET;
    $response['post'] = $_POST;

    echo(base64_encode(serialize($response)));
}

register_shutdown_function('wpbrowser_handle_shutdown');

ob_start();
include $indexFile;
