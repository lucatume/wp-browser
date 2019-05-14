<?php
/**
 * Loads the correct version of the compatible test case depending on the PHPUnit version.
 */

if (class_exists('\PHPUnit\Runner\Version')) {
    $phpunitSeries = \PHPUnit\Runner\Version::series();
} elseif (class_exists('PHPUnit_Runner_Version')) {
    $phpunitSeries = PHPUnit_Runner_Version::series();
} else {
    $phpunitSeries = '5.0';
}

if (version_compare($phpunitSeries, '8.0.0', '<')) {
    require_once __DIR__ . '/Compat/PHPUnit/Base/Testcase.php';
} else {
    require_once __DIR__ . '/Compat/PHPUnit/Version8/Testcase.php';
}
