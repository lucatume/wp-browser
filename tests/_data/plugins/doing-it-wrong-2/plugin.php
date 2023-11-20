<?php
/**
 * Plugin Name: Doing It Wrong 2
 */

function doing_it_wrong_2_activation()
{
    _doing_it_wrong(__FUNCTION__, 'This is a test', '1.0.0');
    update_option('doing_it_wrong_2_activation', 'activated');
}

register_activation_hook(__FILE__, 'doing_it_wrong_2_activation');

add_action('plugins_loaded', function () {
    global $doing_it_wrong_2_plugin_loaded;
    $doing_it_wrong_2_plugin_loaded = true;
});
