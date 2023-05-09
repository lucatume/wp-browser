<?php
/**
 * Mock wp_mail() function.
 *
 * @return bool
 */
if (!function_exists('wp_mail')) {
    function wp_mail()
    {
        return true;
    }
}
