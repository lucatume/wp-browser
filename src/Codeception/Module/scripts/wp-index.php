<?php
if (!defined('WP_USE_THEMES')) {
    define('WP_USE_THEMES', true);
}

if (TEMPLATEPATH !== STYLESHEETPATH && file_exists(STYLESHEETPATH . '/functions.php')) {
    include_once(STYLESHEETPATH . '/functions.php');
}
if (file_exists(TEMPLATEPATH . '/functions.php')) {
    include_once(TEMPLATEPATH . '/functions.php');
}

if (empty($_SERVER['SERVER_PROTOCOL'])) {
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
}

if (empty($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = __FILE__;
}

wp();

include ABSPATH . WPINC . '/template-loader.php';
