<?php
/**
 * Checks on a WordPress installation health w/o using WordPress methods.
 *
 * @package tad\WPBrowser\Module\Support
 */

namespace tad\WPBrowser\Module\Support;

use tad\WPBrowser\Environment\Constants;
use tad\WPBrowser\Traits\WordPressDatabase;
use tad\WPBrowser\Traits\WordPressDirectories;

/**
 * Class WPHealthcheck
 * @package tad\WPBrowser\Module\Support
 */
class WPHealthcheck
{
    use WordPressDirectories;
    use WordPressDatabase;

    /**
     * The current database structure error if any.
     *
     * @var string
     */
    protected $dbStructureError;

    /**
     * The current blog installation error, if any.
     *
     * @var string
     */
    protected $blogNotInstalledError;

    /**
     * The name of the currently active theme.
     *
     * @var string
     */
    protected $currentTheme;

    /**
     * The current theme error, if any.
     *
     * @var string
     */
    protected $themeError;

    /**
     * The current mu-plugins information, if any.
     *
     * @var string
     */
    protected $muPlugins;

    /**
     * The current mu-plugins error, if any.
     *
     * @var string
     */
    protected $muPluginsError;
    /**
     * The current plugins information, if any.
     *
     * @var string
     */
    protected $plugins;

    /**
     * The current plugins error, if any.
     *
     * @var string
     */
    protected $pluginsErrors;

    /**
     * An instance of the constants wrapper.
     *
     *@var Constants|null
     */
    protected $constants;

    /**
     * Runs a battery of checks on the WordPress installation and returns the results.
     *
     * @return array An array of results, by category.
     */
    public function run()
    {
        return [
            'constants' => $this->getConstants(),
            'globals' => $this->getGlobals(),
            'checks' => $this->runChecks()
        ];
    }

    /**
     * Returns an array of WordPress constants and their value or status.
     *
     * @return array An associative array of WordPress constants and their value/status.
     */
    protected function getConstants()
    {
        return [
            'ABSPATH' => $this->constants->constant('ABSPATH', 'not set'),
            'WP_DEFAULT_THEME' => $this->constants->constant('WP_DEFAULT_THEME', 'not set'),
            'WP_CONTENT_DIR' => $this->constants->constant('WP_CONTENT_DIR', 'not set'),
            'WP_PLUGIN_DIR' => $this->constants->constant('WP_PLUGIN_DIR', 'not set'),
            'WP_HOME' => $this->constants->constant('WP_HOME', 'not set'),
            'WP_SITEURL' => $this->constants->constant('WP_SITEURL', 'not set'),
            'WPMU_PLUGIN_DIR' => $this->constants->constant('WPMU_PLUGIN_DIR', 'not set'),
            'DB_HOST' => $this->constants->constant('DB_HOST', 'not set'),
            'DB_NAME' => $this->constants->constant('DB_NAME', 'not set'),
            'DB_PASSWORD' => $this->constants->constant('DB_PASSWORD', 'not set'),
            'DB_USER' => $this->constants->constant('DB_USER', 'not set'),
            'CUSTOM_USER_TABLE' => $this->constants->constant('CUSTOM_USER_TABLE', 'not set'),
            'CUSTOM_USER_META_TABLE' => $this->constants->constant('CUSTOM_USER_META_TABLE', 'not set')
        ];
    }

    /**
     * WPHealthcheck constructor.
     *
     * @param Constants|null $constants An instance of the constants wrapper or null to build one.
     */
    public function __construct(Constants $constants = null)
    {
        $this->constants = $constants ? $constants : new Constants();
    }

    /**
     * Returns an array of WordPress globals and their value or status.
     *
     * @return array An associative array of WordPress globals and their value/status.
     */
    public function getGlobals()
    {
        return [
            'table_prefix' => $this->getTablePrefix('not set'),
        ];
    }

    /**
     * Runs and returns a battery of checks on the site filesystem and status.
     *
     * @return array An associative array reporting the checks statuses.
     */
    protected function runChecks()
    {
        return [
            'Site is multisite' => $this->isMultisite() ? 'yes' : 'no',
            'ABSPATH points to valid WordPress directory' => $this->checkWpRoot() ?
                'Yes, wp-load.php file found in WordPress root directory.'
                : 'No, wp-load.php file not found in WordPress root directory.',
            'Database connection works' => $this->checkDbConnection() ?
                'Yes, connection successful.'
                : 'No, connection errors: ' . $this->dbConnectionError,
            'Database structure as expected' => $this->checkDatabaseStructure() ?
                'Yes, structure as expected.'
                : 'No, structure errors: ' . $this->dbStructureError,
            'Blog installed' => $this->checkBlogInstalled() ?
                'Yes, blog is installed.'
                : 'No, blog is not installed: ' . $this->blogNotInstalledError,
            'Theme :' => $this->checkTheme() ? $this->currentTheme : $this->themeError,
            'Must-use plugins health-check:' => $this->checkMuPlugins() ? $this->muPlugins : $this->muPluginsError,
            'Plugins health-check:' => $this->checkPlugins() ? $this->plugins : $this->pluginsErrors,
        ];
    }

    /**
     * Whether the current WordPress environment is multi-site or not.
     *
     * The method will check the `MULTISITE` related constants.
     *
     * @return bool Whether the current installation is multi-site or not.
     */
    public function isMultisite()
    {
        if ($this->constants->defined('MULTISITE')) {
            return (bool)$this->constants->constant(MULTISITE);
        }

        if ($this->constants->defined('SUBDOMAIN_INSTALL')
            || $this->constants->defined('VHOST')
            || $this->constants->defined('SUNRISE')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the `ABSPATH` constant is defined and points to a valid WordPress root directory.
     *
     * @return bool Whether the `ABSPATH` constant is defined and points to a valid WordPress root directory.
     */
    public function checkWpRoot()
    {
        return ($this->constants->defined('ABSPATH')
            && file_exists($this->constants->constant(ABSPATH) . 'wp-load.php'));
    }

    /**
     * Checks the WordPress installation structure.
     *
     * @return bool Whether the database installation structure is as expected or not.
     */
    public function checkDatabaseStructure()
    {
        if (!$this->checkDbConnection()) {
            $this->dbStructureError = 'cannot connect to database to check structure.';
            return false;
        }

        $tables = $this->pdo->query('SHOW TABLES');
        if ($tables === false || !$tables instanceof \PDOStatement) {
            $this->dbStructureError = 'could not check on the database tables.';
            return false;
        }

        if ($tables->rowCount() === 0) {
            $this->dbStructureError = 'no tables found in database.';
            return false;
        }

        global $table_prefix, $wpdb;

        $allTables = $tables->fetchAll(\PDO::FETCH_COLUMN);

        $matchingTables = array_filter($allTables, static function ($table) use ($table_prefix) {
            return strpos($table, $table_prefix) === 0;
        });

        if (empty($matchingTables)) {
            $this->dbStructureError = "no tables found for table prefix [{$table_prefix}].";
            return false;
        }

        $expectedTables = $wpdb->tables('all', true);

        if (empty($expectedTables)) {
            $this->dbStructureError = "the \$wpdb global object has no registered tables.";
            return false;
        }

        $tablesDiff = array_diff($expectedTables, $allTables);

        if (!empty($tablesDiff)) {
            $this->dbStructureError = 'some tables are missing (' . implode(', ', $tablesDiff) . ')';
            return false;
        }
    }

    /**
     * Checks if the blog, or the main blog in a multi-site installation, is correctly installed.
     *
     * @return bool Whether the blog, or a site main blog, is installed or not.
     */
    public function checkBlogInstalled()
    {
        if (!$this->checkDbConnection()) {
            $this->blogNotInstalledError = 'cannot connect to database to check if blog is installed.';
            return false;
        }

        $siteUrl = $this->getOption('siteurl', false);

        if ($siteUrl === false) {
            $this->blogNotInstalledError = sprintf(
                "database table [%s] does not contain a 'siteurl' option.",
                $this->getTable('options')
            );
            return false;
        }

        if (empty($siteUrl)) {
            $this->blogNotInstalledError = sprintf(
                "database table [%s] does contain a 'siteurl' option but it's empty.",
                $this->getTable('options')
            );
            return false;
        }

        if ($this->isMultisite()) {
            $sitesTable = $this->getTable('blogs');
            $domain = parse_url($siteUrl, PHP_URL_HOST);
            $blogId = $this->pdo->query("SELECT blog_id FROM {$sitesTable} WHERE domain = '{$domain}'");

            if ($blogId === false) {
                $this->blogNotInstalledError = sprintf(
                    "cannot query table [%s] for blog with domain [%s].",
                    $sitesTable,
                    $domain
                );
                return false;
            }

            if ($blogId->rowCount() === 0) {
                $this->blogNotInstalledError = sprintf(
                    "database table [%s] does not contain a blog with domain [%s].",
                    $sitesTable,
                    $domain
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Checks the theme (template and stylesheet) in the database and the filesystem.
     *
     * @return bool Whether the theme is correctly set or not.
     */
    public function checkTheme()
    {
        if (!$this->checkDbConnection()) {
            $this->themeError = 'cannot connect to database to check for current theme.';
            return false;
        }

        $template = $this->getOption('template', false);
        $stylesheet = $this->getOption('stylesheet', false);

        if (false === $template) {
            $this->themeError = "cannot find the 'template' option in the database.";
            return false;
        }
        $themes = [
            'template' => $template,
            'stylesheet' => $stylesheet ? $stylesheet : 'n/a',
        ];

        foreach ([$template, $stylesheet] as $target) {
            if (empty($target)) {
                continue;
            }
            $path = $this->getThemesDir() . '/' . $target;
            if (!file_exists($path)) {
                $themes["{$target} directory not found"] = $path;
            } else {
                $themes["{$target} directory"] = $path;
            }
        }

        $this->currentTheme = $themes;

        return true;
    }

    /**
     * Checks the status of the mu-plugins.
     *
     * @return bool Whether all the active plugins are installed and ok, or not.
     */
    public function checkMuPlugins()
    {
        if (!$this->checkWpRoot()) {
            $this->muPluginsError = 'Cannot check on mu-plugins as root directory is not valid.';
            return false;
        }

        $muPluginsFolder = $this->getWpmuPluginsDir();

        if (!file_exists($muPluginsFolder)) {
            $this->muPlugins = "mu - plugins directory({$muPluginsFolder}) does not exist . ";
            return true;
        }

        $files = new \CallbackFilterIterator(
            new \FilesystemIterator($muPluginsFolder),
            function (\SplFileInfo $file) {
                return $file->getExtension() === 'php';
            }
        );

        if (!iterator_count($files)) {
            $this->muPlugins = 'No mu-plugins found in mu-plugins directory.';
            return true;
        }

        $fileBasenames = [];
        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            $fileBasenames[] = $file->getBasename();
        }

        $this->muPlugins = $fileBasenames;

        return true;
    }

    /**
     * Checks the status of the plugins.
     *
     * @return bool Whether all the active plugins are installed and ok, or not.
     */
    public function checkPlugins()
    {
        if (!$this->checkWpRoot()) {
            $this->pluginsErrors = 'cannot check on plugins as root directory is not valid.';
            return false;
        }

        if (!$this->checkDbConnection()) {
            $this->pluginsErrors = 'cannot connect to database to check on plugins.';
            return false;
        }

        $activePlugins = $this->getOption('active_plugins', false);

        if ($activePlugins === false) {
            $this->pluginsErrors = "The 'active_plugins' option was not found in the database . ";
            return false;
        }

        $activePlugins = unserialize($activePlugins);

        $pluginErrors = [];
        $foundPlugins = [];
        $inactivePlugins = [];
        /** @var \SplFileInfo $file */
        foreach (new \FilesystemIterator($this->getPluginsDir(), \FilesystemIterator::SKIP_DOTS) as $file) {
            if (!$file->isDir()) {
                continue;
            }

            $pathname = $file->getPathname();
            $inactivePlugins['Inactive plugin ['.basename($pathname).']' ] = $pathname;
        }

        foreach ($activePlugins as $activePlugin) {
            $pluginFile = $this->getPluginsDir() . '/' . $activePlugin;
            if (!file_exists($pluginFile)) {
                $pluginErrors["Active plugin [{$activePlugin}]"] = sprintf(
                    'file [%s] does not exist.',
                    $pluginFile
                );
            } else {
                $foundPlugins["Active plugin [{$activePlugin}]"] = sprintf(
                    'file [%s] found.',
                    $pluginFile
                );
            }
            if ($i = array_search(dirname($pluginFile), $inactivePlugins, true)) {
                unset($inactivePlugins[$i]);
            }
        }

        if (count($pluginErrors)) {
            $this->pluginsErrors = array_merge($pluginErrors, $foundPlugins, $inactivePlugins);
            return false;
        }

        $this->plugins = array_merge($foundPlugins, $inactivePlugins);

        return true;
    }
}
