<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

include __DIR__ . '/support-functions.php';
include __DIR__ . '/filters.php';
include __DIR__ . '/pluggable-functions-override.php';

global $argv;

$indexFile = $argv[1];

$env = unserialize(base64_decode($argv[2]));

if (!empty($env['cookie'])) {
    foreach ($env['cookie'] as $key => $value) {
        $_COOKIE[$key] = $value;
    }
}

if (!empty($env['server'])) {
    foreach ($env['server'] as $key => $value) {
        $_SERVER[$key] = $value;
    }
}

if (!empty($env['files'])) {
    foreach ($env['files'] as $key => $value) {
        $_FILES[$key] = $value;
    }
}

if (!empty($env['request'])) {
    foreach ($env['request'] as $key => $value) {
        $_REQUEST[$key] = $value;
    }
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

if (!empty($env['headers'])) {
    foreach ($env['headers'] as $header) {
        header($header);
    }
}

// Disable CRON tasks to avoid parallel processes running on an empty database.
define('DISABLE_WP_CRON', true);

// Set an environment variable to signal the context of the request.
putenv('WPBROWSER_HOST_REQUEST=1');

// If the `uopz` extension is installed, then allow `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}
