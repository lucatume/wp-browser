<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

include dirname(__FILE__) . '/cli-headers-patch.php';

$indexFile = $argv[1];

// @todo: add a constants file possibility to disable updates and checks by default

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

    $response['content'] = ob_get_clean();
    $response['headers'] = cli_headers_list();
    $response['status'] = cli_headers_last_status();
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
