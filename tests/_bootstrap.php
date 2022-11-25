<?php
// This is global bootstrap for autoloading.
use Codeception\Util\Autoload;

use lucatume\WPBrowser\Utils\Filesystem;
use function lucatume\WPBrowser\Tests\Support\createTestDatabasesIfNotExist;

createTestDatabasesIfNotExist();

// Make sure traits can be autoloaded from tests/_support/Traits
Autoload::addNamespace('\lucatume\WPBrowser\Tests\Traits', codecept_root_dir('tests/_support/Traits'));

// If the `uopz` extension is installed, then ensure `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}

// Clean the `tests/_output/tmp` directory before each suite run using the system `rm` command.
exec('rm -rf ' . codecept_output_dir('tmp') . '/*', $output, $status);
if ($status !== 0) {
    throw new \RuntimeException('Could not clean the `tests/_output/tmp` directory: ' . implode(PHP_EOL, $output));
}
