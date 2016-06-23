<?php

namespace tad\WPBrowser\Module\Support;


use tad\WPBrowser\Adapters\WP;

class WPFacade implements WPFacadeInterface
{

    /**
     * @var WP
     */
    protected $wpAdapter;

    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = [], WP $wpAdapter = null)
    {
        $this->config = $config;
        $this->wpAdapter = $wpAdapter ? $wpAdapter : new WP();
    }

    public function home_url($path = '', $scheme = null)
    {
        return $this->wpAdapter->home_url($path, $scheme);
    }

    public function admin_url($path = '', $scheme = 'admin')
    {
        return $this->wpAdapter->admin_url($path, $scheme);
    }

    public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return $this->wpAdapter->add_filter($tag, $function_to_add, $priority, $accepted_args);
    }

    public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return $this->wpAdapter->add_action($tag, $function_to_add, $priority, $accepted_args);
    }

    public function update_option($option, $new_value, $autoload = null)
    {
        return $this->wpAdapter->update_option($option, $new_value, $autoload);
    }

    public function flush_rewrite_rules($hard = true)
    {
        $this->wpAdapter->flush_rewrite_rules($hard);
    }

    public function getAdminPath()
    {
        return rtrim(str_replace($this->wpAdapter->home_url(), '', $this->wpAdapter->admin_url()), '/');
    }
}
