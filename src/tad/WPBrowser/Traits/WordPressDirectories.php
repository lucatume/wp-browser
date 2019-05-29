<?php
/**
 * Provides methods to retrieve information about WordPress directories based on currently defined constants.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

/**
 * Class WordPressDirectories
 * @package tad\WPBrowser\Traits
 */
trait WordPressDirectories
{
    /**
     * Returns the absolute path to the mu-plugins directory.
     *
     * @return string The absolute path to the mu-plugins directory.
     */
    public function getWpmuPluginsDir()
    {
        return $this->constants->constant('WPMU_PLUGIN_DIR', $this->getWpContentDir() . '/mu-plugins');
    }

    /**
     * Returns the absolute path to the plugins directory.
     *
     * @return string The absolute path to the plugins directory.
     */
    public function getPluginsDir()
    {
        return $this->constants->constant('WP_PLUGIN_DIR', $this->getWpContentDir() . '/plugins');
    }

    /**
     * Returns the absolute path to the content directory.
     *
     * @return string The absolute path to the content directory.
     */
    public function getWpContentDir()
    {
        if ($this->constants->defined('WP_CONTENT_DIR')) {
            return $this->constants->constant('WP_CONTENT_DIR');
        }
        if ($this->constants->defined('ABSPATH')) {
            return $this->constants->constant('ABSPATH') . 'wp-content';
        }

        return '';
    }

    /**
     * Returns the absolute path to the themes directory.
     *
     * @return string The absolute path to the themes directory.
     */
    public function getThemesDir()
    {
        return $this->getWpContentDir() . '/themes';
    }

    /**
     * Returns the absolute path to the WordPress root directory.
     *
     * @return string The absolute path to the WordPress root directory.
     */
    public function getAbspath()
    {
        return $this->checkWpRoot() ?
            ABSPATH . 'wp-load.php'
            : '';
    }
}
