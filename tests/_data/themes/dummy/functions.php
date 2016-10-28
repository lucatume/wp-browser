<?php

add_action('after_switch_theme', function ($stylesheet) {
    if ($stylesheet === 'dummy') {
        update_option('dummy_theme_activated', true);
    }
});

add_action('switch_theme', function () {
    delete_option('dummy_theme_activated');
});

function dummy_theme_function()
{
    // no-op
}
