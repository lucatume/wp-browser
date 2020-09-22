<?php
/**
 * Checks on a WordPress installation health w/o using WordPress methods.
 *
 * @package tad\WPBrowser\Module\Support
 */

namespace tad\WPBrowser\Module\Support;

use tad\WPBrowser\Environment\Constants;
use tad\WPBrowser\Generators\Tables as Tables;

/**
 * Class WPHealthcheck
 * @package tad\WPBrowser\Module\Support
 */
class WPHealthcheck
{
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
     * @var string|array<string,mixed>
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
     * @var string|array<string>
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
     * @var array<string,string>
     */
    protected $plugins;

    /**
     * The current plugins errors, if any.
     *
     * @var string|array<string,array|string>
     */
    protected $pluginsErrors;

    /**
     * An instance of the constants wrapper.
     *
     * @var Constants
     */
    protected $constants;

    /**
     * Whether to use relative paths or not.
     *
     * @var bool
     */
    protected $useRelative = false;
    /**
     * An instance of the WordPress database connection handler.
     *
     * @var WordPressDatabase
     */
    protected $database;

    /**
     * An instance of the WordPress directories navigation object.
     *
     * @var WordPressDirectories
     */
    protected $directories;

    /**
     * WPHealthcheck constructor.
     *
     * @param Constants|null $constants An instance of the constants wrapper or null to build one.
     * @param WordPressDatabase $database An instance of the WordPress database connection handler.
     * @param WordPressDirectories $directories An instance of the WordPress directories handler.
     */
    public function __construct(
        Constants $constants = null,
        WordPressDatabase $database = null,
        WordPressDirectories $directories = null
    ) {
        $this->constants = $constants ? $constants : new Constants();
        $this->database = $database ? $database : new WordPressDatabase($this->constants);
        $this->directories = $directories ? $directories : new WordPressDirectories($this->constants);
    }

    /**
     * Runs a battery of checks on the WordPress installation and returns the results.
     *
     * @return array<string,array> An array of results, by category.
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
     * @return array<string,mixed> An associative array of WordPress constants and their value/status.
     */
    protected function getConstants()
    {
        $abspath = $this->constants->constant('ABSPATH', 'not set');
        $pluginsDir = $this->constants->constant('WP_PLUGIN_DIR', 'not set');
        $contentDir = $this->constants->constant('WP_CONTENT_DIR', 'not set');
        $muPluginsDir = $this->constants->constant('WPMU_PLUGIN_DIR', 'not set');

        return [
            'ABSPATH' => $this->useRelative ? $this->relative($abspath, codecept_root_dir()) : $abspath,
            'WP_DEFAULT_THEME' => $this->constants->constant('WP_DEFAULT_THEME', 'not set'),
            'WP_CONTENT_DIR' => $this->useRelative ? $this->relative($contentDir) : $contentDir,
            'WP_PLUGIN_DIR' => $this->useRelative ? $this->relative($pluginsDir) : $pluginsDir,
            'WP_HOME' => $this->constants->constant('WP_HOME', 'not set'),
            'WP_SITEURL' => $this->constants->constant('WP_SITEURL', 'not set'),
            'WPMU_PLUGIN_DIR' => $this->useRelative ? $this->relative($muPluginsDir) : $muPluginsDir,
            'DB_HOST' => $this->constants->constant('DB_HOST', 'not set'),
            'DB_NAME' => $this->constants->constant('DB_NAME', 'not set'),
            'DB_PASSWORD' => $this->constants->constant('DB_PASSWORD', 'not set'),
            'DB_USER' => $this->constants->constant('DB_USER', 'not set'),
            'CUSTOM_USER_TABLE' => $this->constants->constant('CUSTOM_USER_TABLE', 'not set'),
            'CUSTOM_USER_META_TABLE' => $this->constants->constant('CUSTOM_USER_META_TABLE', 'not set'),
            'DISABLE_WP_CRON' => $this->constants->constant('DISABLE_WP_CRON', 'not set')
        ];
    }

    /**
     * Returns the path relative to the WordPress root folder.
     *
     * @param string $path The path to modify.
     * @param string|null $root The root to use for the relative path resolution, defaults to the ABSPATH.
     *
     * @return string The path, relative to the WordPress root folder.
     */
    public function relative($path, $root = null)
    {
        return \Codeception\Util\PathResolver::getRelativeDir(
            $path,
            $root === null ? $this->directories->getAbspath() : $root,
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * Returns an array of WordPress globals and their value or status.
     *
     * @return array<string,mixed> An associative array of WordPress globals and their value/status.
     */
    public function getGlobals()
    {
        return [
            'table_prefix' => $this->database->getTablePrefix('wp_ (guessed, global not set)'),
        ];
    }

    /**
     * Runs and returns a battery of checks on the site filesystem and status.
     *
     * @return array<string,string|array> An associative array reporting the checks statuses.
     */
    protected function runChecks()
    {
        $this->database->checkDbConnection(true);

        return [
            'Site is multisite' => $this->isMultisite() ? 'yes' : 'no',
            'ABSPATH points to valid WordPress directory' => $this->checkWpRoot() ?
                'Yes, wp-load.php file found in WordPress root directory.'
                : 'No, wp-load.php file not found in WordPress root directory.',
            'Database connection works' => $this->database->checkDbConnection() ?
                'Yes, connection successful.'
                : 'No, connection errors: ' . $this->database->getDbConnectionError(),
            'Database structure as expected' => $this->checkDatabaseStructure() ?
                'Yes, as expected.'
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
            return (bool)$this->constants->constant('MULTISITE');
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
        return (bool)$this->directories->getWpRoot();
    }

    /**
     * Checks the WordPress installation structure.
     *
     * @return bool Whether the database installation structure is as expected or not.
     */
    public function checkDatabaseStructure()
    {
        if (!$this->database->checkDbConnection()) {
            $this->dbStructureError = 'cannot connect to database to check structure.';
            return false;
        }

        $tables = $this->database->query('SHOW TABLES');
        if ($tables === false || !$tables instanceof \PDOStatement) {
            $this->dbStructureError = 'could not check on the database tables.';
            return false;
        }

        if ($tables->rowCount() === 0) {
            $this->dbStructureError = 'no tables found in database.';
            return false;
        }

        $table_prefix = $this->database->getTablePrefix();

        $allTables = $tables->fetchAll(\PDO::FETCH_COLUMN);

        if ($allTables === false) {
            $this->dbStructureError = 'unable to fetch all the tables from the database.';
            return false;
        }

        $matchingTables = array_filter($allTables, static function ($table) use ($table_prefix) {
            return strpos($table, $table_prefix) === 0;
        });

        if (empty($matchingTables)) {
            $this->dbStructureError = "no tables found for table prefix [{$table_prefix}].";
            return false;
        }

        if (isset($GLOBALS['wpdb']) && get_class($GLOBALS['wpdb']) === 'wpdb') {
            $expectedTables = $GLOBALS['wpdb']->tables('all', true);
        } else {
            $expectedTables = $this->isMultisite() ?
                $this->getMultiSiteDefaultTables()
                : $this->getSingleSiteDefaultTables();
        }

        if (empty($expectedTables)) {
            $this->dbStructureError = "the \$wpdb global object has no registered tables.";
            return false;
        }

        $tablesDiff = array_diff($expectedTables, $allTables);

        if (!empty($tablesDiff)) {
            $this->dbStructureError = 'some tables are missing (' . implode(', ', $tablesDiff) . ')';
            return false;
        }

        return true;
    }

    /**
     * Returns a list of default multi-site tables.
     *
     * @return array<string> A list of default multi-site tables, including the table prefix.
     */
    public function getMultiSiteDefaultTables()
    {
        $tablePrefix = $this->database->getTablePrefix();

        $multiSiteTables = array_map(static function ($tableName) use ($tablePrefix) {
            return $tablePrefix . $tableName;
        }, Tables::multisiteTables());

        return array_merge($this->getSingleSiteDefaultTables(), $multiSiteTables);
    }

    /**
     * Returns a list of default single site tables.
     *
     * @return array<string> A list of default single site tables, including the table prefix.
     */
    public function getSingleSiteDefaultTables()
    {
        $tablePrefix = $this->database->getTablePrefix();

        return array_map(static function ($tableName) use ($tablePrefix) {
            return $tablePrefix . $tableName;
        }, Tables::blogTables());
    }

    /**
     * Checks if the blog, or the main blog in a multi-site installation, is correctly installed.
     *
     * @return bool Whether the blog, or a site main blog, is installed or not.
     */
    public function checkBlogInstalled()
    {
        if (!$this->database->checkDbConnection()) {
            $this->blogNotInstalledError = 'cannot connect to database to check if blog is installed.';
            return false;
        }

        $siteUrl = $this->database->getOption('siteurl', false);

        if ($siteUrl === false) {
            $this->blogNotInstalledError = sprintf(
                "database table [%s] does not contain a 'siteurl' option.",
                $this->database->getTable('options')
            );
            return false;
        }

        if (empty($siteUrl)) {
            $this->blogNotInstalledError = sprintf(
                "database table [%s] does contain a 'siteurl' option but it's empty.",
                $this->database->getTable('options')
            );
            return false;
        }

        if ($this->isMultisite()) {
            $sitesTable = $this->database->getTable('blogs');
            $domain = parse_url($siteUrl, PHP_URL_HOST);
            $blogId = $this->database->query("SELECT blog_id FROM {$sitesTable} WHERE domain = '{$domain}'");

            if ($blogId === false) {
                $this->blogNotInstalledError = sprintf(
                    'cannot query table [%s] for blog with domain [%s].',
                    $sitesTable,
                    $domain
                );
                return false;
            }

            if ($blogId->rowCount() === 0) {
                $this->blogNotInstalledError = sprintf(
                    'database table [%s] does not contain a blog with domain [%s].',
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
        if (!$this->database->checkDbConnection()) {
            $this->themeError = 'Cannot connect to database to check for current theme.';
            return false;
        }

        // Try to read the template and stylesheet from overriding globals if set.
        $template   = isset($GLOBALS['wp_tests_options']['template']) ?
            $GLOBALS['wp_tests_options']['template']
            : $this->database->getOption('template', false);
        $stylesheet = isset($GLOBALS['wp_tests_options']['stylesheet']) ?
            $GLOBALS['wp_tests_options']['stylesheet']
            : $this->database->getOption('stylesheet', false);

        if (false === $template) {
            $this->themeError = "Cannot find the 'template' option in the database.";
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
            $path = $this->directories->getThemesDir() . '/' . $target;
            if (!file_exists($path)) {
                $themes["{$target} directory not found"] = $this->relative($path, codecept_root_dir());
            } else {
                $themes["{$target} directory"] = $this->relative($path, codecept_root_dir());
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

        $muPluginsFolder = $this->directories->getWpmuPluginsDir();

        if (!file_exists($muPluginsFolder)) {
            $this->muPlugins = "mu-plugins directory({$this->relative($muPluginsFolder)}) does not exist.";
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

        sort($fileBasenames);

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
            $this->pluginsErrors = 'Cannot check on plugins as root directory is not valid.';
            return false;
        }

        if (!$this->database->checkDbConnection()) {
            $this->pluginsErrors = 'Cannot connect to database to check on plugins.';
            return false;
        }

        $activePlugins = $this->database->getOption('active_plugins', false);

        if ($activePlugins === false) {
            $this->pluginsErrors = "The 'active_plugins' option was not found in the database . ";
            return false;
        }

        $activePlugins = unserialize($activePlugins);

        $pluginErrors = [];
        $foundPlugins = [];
        $inactivePlugins = [];
        /** @var \SplFileInfo $file */
        foreach (new \FilesystemIterator(
            $this->directories->getPluginsDir(),
            \FilesystemIterator::SKIP_DOTS
        ) as $file) {
            if (!$file->isDir()) {
                continue;
            }

            $pathname = $file->getPathname();
            $inactivePlugins['Inactive plugin [' . basename($pathname) . ']'] = $this->relative($pathname);
        }

        foreach ($activePlugins as $activePlugin) {
            $pluginFile = $this->directories->getPluginsDir() . '/' . $activePlugin;
            if (!file_exists($pluginFile)) {
                $pluginErrors["Active plugin [{$activePlugin}]"] = sprintf(
                    'file [%s] does not exist.',
                    $this->relative($pluginFile)
                );
            } else {
                $foundPlugins["Active plugin [{$activePlugin}]"] = sprintf(
                    'file [%s] found.',
                    $this->relative($pluginFile)
                );
            }
            if ($i = array_search($this->relative(dirname($pluginFile)), $inactivePlugins, true)) {
                unset($inactivePlugins[$i]);
            }
        }

        ksort($foundPlugins);
        ksort($inactivePlugins);

        if (count($pluginErrors)) {
            $this->pluginsErrors = array_merge($pluginErrors, $foundPlugins, $inactivePlugins);
            return false;
        }

        $this->plugins = array_merge($foundPlugins, $inactivePlugins);

        return true;
    }

    /**
     * Sets the flag to print first-level paths as relative.
     *
     * ABSPATH will be relative to the Codeception project root, the other paths will be relative to the absolute path.
     * Second level paths will always be printed as relative; first-level paths
     *
     * @param bool $useRelativePaths Whether to print all paths as relative paths.
     *
     * @return void
     */
    public function useRelativePaths($useRelativePaths)
    {
        $this->useRelative = (bool)$useRelativePaths;
    }
}
