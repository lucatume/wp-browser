<?php
// This is global bootstrap for autoloading.
use Codeception\Util\Autoload;

use function lucatume\WPBrowser\Tests\Support\createTestDatabasesIfNotExist;

createTestDatabasesIfNotExist();

// Make sure traits can be autoloaded from tests/_support/Traits
Autoload::addNamespace('\lucatume\WPBrowser\Tests\Traits', codecept_root_dir('tests/_support/Traits'));

// If the `uopz` extension is installed, then ensure `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}
