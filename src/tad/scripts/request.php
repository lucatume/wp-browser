<?php
include __DIR__ . '/request-script-bootstrap.php';

function wpbrowser_handle_shutdown()
{
    $response = [];
    $response['content'] = ob_get_clean();
    $response['headers'] = cli_headers_list();
    if (!empty($_COOKIE)) {
        $response['headers']['Set-Cookie'] = [];
        foreach ($_COOKIE as $key => $value) {
            $response['headers']['Set-Cookie'][] = "{$key}={$value}";
        }
    }

    $response['status'] = cli_headers_last_status();
    $response['server'] = $_SERVER;
    $response['files'] = $_FILES;
    $response['request'] = $_REQUEST;
    $response['get'] = $_GET;
    $response['post'] = $_POST;

    echo(base64_encode(serialize($response)));
}

register_shutdown_function('wpbrowser_handle_shutdown');
ob_start();
/** @noinspection PhpIncludeInspection */
include $indexFile;
