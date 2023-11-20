<?php
/**
 * Plugin Name: Doing It Right
 */

register_activation_hook(__FILE__, function () {
    update_option('doing_it_right_activation', 'activated');
});

add_action('plugins_loaded', function () {
    global $doing_it_right_plugin_loaded;
    $doing_it_right_plugin_loaded = true;
});
