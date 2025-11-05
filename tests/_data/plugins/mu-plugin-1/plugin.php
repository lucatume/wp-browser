<?php

/**
 * Plugin Name: Multisite Plugin 1
 * Plugin URI: http://theAverageDev.com
 * Description: Multisite Plugin 1
 * Version: 1.0
 * Author: theAverageDev
 * Author URI: http://theAverageDev.com
 * License: GPL 2.0
 */
class MUPlugin1
{
    public static function activate(): void
    {
        if (is_multisite()) {
            update_network_option(null, 'muplugin1', 'mu-activated');
        } else {
            update_option('muplugin1', 'not-mu-activated');
        }
    }
}
register_activation_hook(__FILE__, [MUPlugin1::class, 'activate']);

// Override the get_avatar pluggable function to verify the pluggable override works.
if (!function_exists('get_avatar')) {
    function get_avatar($id_or_email, $size = 96, $default_value = '', $alt = '', $args = null)
    {
        return "<img class='avatar' height=23 width=89 src='https://example.com/avatar.jpg'>";
    }
}

