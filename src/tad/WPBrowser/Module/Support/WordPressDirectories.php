<?php
/**
 * Provides methods to retrieve information about WordPress directories based on currently defined constants.
 *
 * @package tad\WPBrowser\Module\Support
 */

namespace tad\WPBrowser\Module\Support;

use tad\WPBrowser\Environment\Constants;

/**
 * Class WordPressDirectories
 * @package tad\WPBrowser\Module\Support
 */
class WordPressDirectories
{
    /**
     * ${CARET}
     * @var Constants
     */
    protected $constants;

    /**
     * WordPressDirectories constructor.
     * @param Constants $constants An instance of the constants wrapper/adapter.
     */
    public function __construct(Constants $constants)
    {
        $this->constants = $constants;
    }

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
     * Returns the absolute path to the plugins directory.
     *
     * @return string The absolute path to the plugins directory.
     */
    public function getPluginsDir()
    {
        return $this->constants->constant('WP_PLUGIN_DIR', $this->getWpContentDir() . '/plugins');
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
        return $this->getWpRoot() ?
            $this->constants->constant('ABSPATH')
            : '';
    }

    /**
     * Returns the absolute path, with a trailing slash, to the WordPress root folder.
     *
     * @return string The absolute path, with a trailing slash, to the WordPress root folder or an empty string.
     */
    public function getWpRoot()
    {
        return ($this->constants->defined('ABSPATH')
            && file_exists($this->constants->constant('ABSPATH') . '/wp-load.php'))
            ? $this->constants->constant('ABSPATH') . '/wp-load.php'
            : '';
    }
}
