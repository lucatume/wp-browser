<?php
/**
 * Handles the aliasing of classes managed by wp-browser.
 */

if (! class_exists('\\Dotenv\\Dotenv')) {
    /*
     * In version 2.5.0 the requirement of vlucas/dotenv package was removed from wp-browser causing issues w/ projects
     * that, implicitly, relied on it.
     * This alias should mitigate the issue.
     */
    class_alias('\\tad\WPBrowser\\Polyfills\\Dotenv\\Dotenv', '\\Dotenv\\Dotenv');
}
