<?php
/**
 * Utility functions to support wp-browser.
 */

if (!function_exists('wpbrowser_vendor_path')) {
    /**
     * Gets the absolute path to the `vendor` dir optionally appending a path.
     *
     * @param  string  $path  The relative path.
     *
     * @return string The absolute path to the file.
     * @throws \ReflectionException
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

if (!function_exists('rrmdir')) {
    /**
     * Recursively removes a directory and all its content.
     *
     * @param string $src The absolute path to the directory to remove.
     */
    function rrmdir($src)
    {
        if (!file_exists($src)) {
            return;
        }

        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    rrmdir($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

}

if (!function_exists('wpbrowser_includes_dir')) {
    /**
     * Returns the absolute path to a file or folder in the `src/includes` folder or the path to it.
     *
     * @param  string $path An optional path fragment to a file or folder from the the `src/includes` path
     *
     * @return string The absolute path to a file or folder in the `src/includes` folder.
     */
    function wpbrowser_includes_dir($path = '')
    {
        $root = dirname(__DIR__, 2). '/includes/';

        return !empty($path) ? $root . ltrim($path, '/') : $root;
    }
}
