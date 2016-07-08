<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

include dirname(dirname(dirname(dirname(__FILE__)))) . '/src/includes/functions.php';
include dirname(__FILE__) . '/support-functions.php';
include dirname(__FILE__) . '/filters.php';
include dirname(__FILE__) . '/pluggable-functions-override.php';

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

// disable CRON tasks to avoid parallel processes running on an empty database
define('DISABLE_WP_CRON', true);
