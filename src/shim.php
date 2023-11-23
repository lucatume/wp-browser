<?php
// Set the global for other possible uses.
global $_composer_vendor_dir;

// Project.
$_composer_vendor_dir = dirname(__FILE__, 2) . '/vendor';

if (!is_dir($_composer_vendor_dir)) {
    // Library.
    $_composer_vendor_dir = dirname(__FILE__, 3);
}

$codeceptionShims = $_composer_vendor_dir . '/codeception/codeception/shim.php';

if (is_file($codeceptionShims)) {
    require_once $codeceptionShims;
}
