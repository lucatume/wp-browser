<?php

add_action('after_switch_theme', 'test_child_after_switch_theme');
function test_child_after_switch_theme($stylesheet)
{
    update_option('test_child_after_switch_theme_called', $stylesheet);
}

add_action('switch_theme', 'test_child_switch_theme');
function test_child_switch_theme($stylesheet)
{
    update_option('test_child_switch_theme_called', $stylesheet);
}

function test_child_theme_function()
{
    // no-op
}
