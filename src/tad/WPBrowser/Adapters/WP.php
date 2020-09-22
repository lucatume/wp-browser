<?php
/**
 * A wrapper around common WordPress functions.
 *
 * @package tad\WPBrowser\Adapters
 */

namespace tad\WPBrowser\Adapters;

use tad\WPBrowser\Traits\WPHealthcheck;

/**
 * Class WP
 * @package tad\WPBrowser\Adapters
 */
class WP
{

    /**
     * Proxy to the `locate_template` function.
     *
     * @param string|array<string> $templateNames Template file(s) to search for, in order.
     * @param bool $load Whether to load the found templates.
     * @param bool $require_once Whether to require the found templates or not.
     *
     * @return string The template filename if one is located.
     */
    public function locate_template($templateNames, $load = false, $require_once = true)
    {
        return locate_template($templateNames, $load, $require_once);
    }

    /**
     * Proxy to the `add_action` function.
     *
     * @param     string   $tag          The tag of the action to attach the callback to.
     * @param     callable $callback     The callback to add .
     * @param int          $priority     The callback priority.
     * @param int          $acceptedArgs The number or arguments the callback will accept.
     *
     * @return true The actions are always added.
     */
    public function add_action($tag, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return add_action($tag, $callback, $priority, $acceptedArgs);
    }

    /**
     * Proxy to the `add_filter` function.
     *
     * @param     string   $tag          The tag of the filter to attach the callback to.
     * @param     callable $callback     The callback to add .
     * @param int          $priority     The callback priority.
     * @param int          $acceptedArgs The number or arguments the callback will accept.
     *
     * @return true The filters are always added.
     */
    public function add_filter($tag, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return add_filter($tag, $callback, $priority, $acceptedArgs);
    }

    /**
     * Proxy to the `update_option` function.
     *
     * @param      string $option   The option name.
     * @param      mixed  $value    The option value.
     * @param bool|null   $autoload Whether the option should be auto-loaded or not.
     *
     * @return bool True if the value was updated, false otherwise.
     */
    public function update_option($option, $value, $autoload = null)
    {
        return update_option($option, $value, $autoload);
    }

    /**
     * Proxy to the `flush_rewrite_rules` function.
     *
     * @param bool $hard Whether to update .htaccess (hard flush) or just update
     *                   rewrite_rules option (soft flush). Default is true (hard).
     *
     * @return void
     */
    public function flush_rewrite_rules($hard = true)
    {
        flush_rewrite_rules($hard);
    }

    /**
     * Proxy to the `home_url` function.
     *
     * @param string $path   An optional path to append to the home URL.
     * @param string|null $scheme The HTTP scheme to use.
     *
     * @return string|void The home URL.
     */
    public function home_url($path = '', $scheme = null)
    {
        return home_url($path, $scheme);
    }

    /**
     * Proxy to the `admin_url` function.
     *
     * @param string $path   An optional path to append to the admin URL.
     * @param string $scheme The HTTP scheme to use.
     *
     * @return string The admin URL.
     */
    public function admin_url($path = '', $scheme = 'admin')
    {
        return admin_url($path, $scheme);
    }

    /**
     * Proxy to the `set_site_transient` function.
     *
     * @param string $transient  The name of the transient to set.
     * @param mixed  $value      The value to set for the transient.
     * @param int    $expiration The transient expiration time, in seconds.
     *
     * @return bool `true` if the value was set, `false` otherwise.
     */
    public function set_site_transient($transient, $value, $expiration = 0)
    {
        return set_site_transient($transient, $value, $expiration = 0);
    }

    /**
     * Proxy to the `switch_theme` function.
     *
     * @param string $stylesheet The theme to switch to.
     *
     * @return void
     */
    public function switch_theme($stylesheet)
    {
        switch_theme($stylesheet);
    }

    /**
     * Proxy to the `do_action` function.
     *
     * @param string $tag The action to fire.
     * @param mixed  ...$context The action context.
     *
     * @return void
     */
    public function do_action($tag, ...$context)
    {
        do_action($tag, ...$context);
    }

    /**
     * Proxy to the `apply_filters` function.
     *
     * @param string       $tag        The filter handle.
     * @param mixed        $value      The value to filter.
     * @param array<mixed> ...$context The filter context.
     *
     * @return mixed The filter result.
     */
    public function apply_filters($tag, $value, ...$context)
    {
        return apply_filters($tag, $value, ...$context);
    }

    /**
     * Returns the absolute path to the content directory.
     *
     * @return string The absolute path to the content directory.
     */
    public function getWpContentDir()
    {
        if (defined('WP_CONTENT_DIR')) {
            return WP_CONTENT_DIR;
        }
        if (defined('ABSPATH')) {
            return ABSPATH . 'wp-content';
        }

        return '';
    }
}
