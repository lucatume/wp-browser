<?php
/**
 * Utility functions to support wp-browser.
 */

if (!function_exists('wpbrowser_vendor_path')) {
    /**
     * Gets the absolute path to the `vendor` dir optionally appending a path.
     *
     * @param string $path The relative path.
     *
     * @return string The absolute path to the file.
     */
    function wpbrowser_vendor_path($path = '')
    {
        $ref = new ReflectionClass('Composer\Autoload\ClassLoader');
        $file = $ref->getFileName();

        $vendorDir = dirname(dirname($file));

        return empty($path) ? $vendorDir : $vendorDir . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('wpbrowser_include_patchwork')) {
    /**
     * Includes the Patchwork library main file
     */
    function wpbrowser_include_patchwork()
    {
        require_once wpbrowser_vendor_path('antecedent/patchwork/Patchwork.php');
    }
}
