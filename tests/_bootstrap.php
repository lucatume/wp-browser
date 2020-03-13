<?php
// This is global bootstrap for autoloading.
use function tad\WPBrowser\Tests\Support\createTestDatabasesIfNotExist;

createTestDatabasesIfNotExist();

// If the `uopz` extension is installed, then ensure `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}
