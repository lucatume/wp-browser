<?php

namespace tad\WPBrowser\Module\Support;

interface WPFacadeInterface
{
    public function home_url($path = '', $scheme = null);

    public function admin_url($path = '', $scheme = 'admin');

    public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1);

    public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1);

    public function update_option($option, $new_value, $autoload = null);

    public function flush_rewrite_rules($hard = true);

    public function getAdminPath();
}