<?php
/**
 * Loads the correct version of the compatible test case depending on the Codeception version.
 */

// @phpstan-ignore-next-line
if (version_compare(\Codeception\Codecept::VERSION, '3.0.0', '<')) {
    class_alias(
        '\\tad\WPBrowser\\Compat\\Codeception\\Version2\\Unit',
        '\\tad\\WPBrowser\\Compat\\Codeception\\Unit'
    );
} else {
    class_alias(
        '\\Codeception\\Test\\Unit',
        '\\tad\\WPBrowser\\Compat\\Codeception\\Unit'
    );
}
