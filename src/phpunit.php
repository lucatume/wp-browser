<?php
/**
 * Funcions related to PHPUnit.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser;

/**
 * Returns the PHPUnit version currently installed.
 *
 * Falls back on version 5 if none can be found.
 *
 * @return string The current PHPUnit version.
 */
function phpunitVersion()
{
    if (class_exists('PHPUnit\Runner\Version')) {
        return \PHPUnit\Runner\Version::series();
    }

    if (class_exists('PHPUnit_Runner_Version')) {
        return PHPUnit_Runner_Version::series();
    }

    return '5.0';
}

/**
 * Sets up the global variable PHPUnit will look up to hydrate the PHPUnit bootstrap file
 * when running tests in isolation.
 *
 * The global is set in the main PHP thread to be hard-coded in the generated template to
 * have, then, autoloading work in the context of test methods running in separate processes.
 *
 * @since TBD
 *
 * @return void The method does nto return any value and has the side effect of setting the
 *              `__PHPUNIT_BOOTSTRAP` global variable.
 */
function setupPhpunitBootstrapGlobal()
{
    $composerAutoloadFilePath       = isset($GLOBALS['_composer_autoload_path']) ?
        $GLOBALS['_composer_autoload_path']
        : vendorDir('autoload.php');
    $composerAutoloadFileRealpth    = realpath($composerAutoloadFilePath);
    $GLOBALS['__PHPUNIT_BOOTSTRAP'] = $composerAutoloadFileRealpth ?: $composerAutoloadFilePath;
}
