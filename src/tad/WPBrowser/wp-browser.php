<?php
/**
 * Functions related to wp-browser inner workings.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Returns the wp-browser package root directory.
 *
 * @param string $path An optional path to append to the root directory absolute path.
 *
 * @return string The absolute path to the package root directory or to a path from it.
 */
function rootDir($path = '')
{
    $root = dirname(dirname(dirname(__DIR__)));

    return $path ? $root . DIRECTORY_SEPARATOR . ltrim($path, '\\/') : $root;
}

/**
 * Gets the absolute path to the `vendorDir` dir optionally appending a path.
 *
 * @param string $path An optional, relative path to append to the vendorDir directory path.
 *
 * @return string The absolute path to the file.
 */
function vendorDir($path = '')
{
    $root = rootDir();

    if (file_exists($root . '/vendor')) {
        // We're in the wp-browser package itself context.
        $vendorDir = $root . '/vendor';
    } else {
        $vendorDir = dirname($root);
    }

    return empty($path) ?
        $vendorDir
        : $vendorDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
}

/**
 * Returns the absolute path to the package `includes` directory.
 *
 * @param string $path An optional path to append to the includes directory absolute path.
 *
 * @return string The absolute path to the package `includes` directory.
 */
function includesDir($path = '')
{
    $includesDir = rootDir('/src/includes');

    return empty($path) ?
        $includesDir
        : $includesDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
}
