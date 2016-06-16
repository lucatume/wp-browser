<?php

namespace tad\WPBrowser\Module\Support;

interface WPFacadeInterface
{
    public function initialize();

    public function home_url($path = '', $scheme = null);

    public function admin_url($path = '', $scheme = 'admin');

    public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1);

    public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1);

    public function getTemplateIncluder();

    public function resetInclusions();

    public function update_option($option, $new_value, $autoload = null);

    public function flush_rewrite_rules($hard = true);

    public function includeTemplate($template);

    public function getHeader($header);

    public function getFooter($footer);

    public function getSidebar($sidebar);

    public function handleAjaxDie();

    public function handleXmlrpcDie();

    public function handleDie();

    public function getAdminPath();
}