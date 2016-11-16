<?php
// for phar
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

// Include Codeception own autoload.php file
require_once(wpbrowser_vendor_path('codeception/codeception/autoload.php'));
