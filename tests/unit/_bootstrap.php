<?php
// Here you can initialize variables that will be available to your tests
include_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

if (!class_exists('WP_Theme')) {
    class WP_Theme
    {
    }
}
