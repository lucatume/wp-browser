<?php
$indexFile = $argv[1];

$env = unserialize(base64_decode($argv[2]));

$_COOKIE = empty($env['cookie']) ? [] : $env['cookie'];
$_SERVER = empty($env['server']) ? [] : $env['server'];
$_FILES = empty($env['files']) ? [] : $env['files'];
$_REQUEST = empty($env['request']) ? [] : $env['request'];
$_GET = empty($env['get']) ? [] : $env['get'];
$_POST = empty($env['post']) ? [] : $env['post'];

foreach ($env['headers'] as $header) {
    $header($header);
}

function tad_handle_shutdown()
{
    $response = [];

    $contents = ob_get_clean();

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

register_shutdown_function('tad_handle_shutdown');

ob_start();
include $indexFile;
