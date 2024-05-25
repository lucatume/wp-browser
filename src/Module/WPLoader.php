<?php
/**
 * A module to load WordPress.
 *
 * @package Codeception\Module;
 */

namespace lucatume\WPBrowser\Module;

use Closure;
use Codeception\Events;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Util\Debug;
use Exception;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\WPLoader\FactoryStore;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\TestCase\WPTestCase;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\Db as DbUtils;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Property;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\InstallationState\Scaffolded;
use lucatume\WPBrowser\WordPress\LoadSandbox;
use lucatume\WPBrowser\WordPress\PreloadFilters;
use Throwable;

/**
 * Class WPLoader
 *
 * Loads a WordPress installation for testing purposes.
 *
 * The class is a Codeception adaptation of WordPress automated testing suite,
 * see [here](http://make.wordpress.org/core/handbook/automated-testing/),
 * and takes care of configuring and installing a WordPress installation.
 * To work properly the \WP_UnitTestCase should be used to run the tests
 * in a PHPUnit-like behaviour.
 *
 * @package Codeception\Module
 */
class WPLoader extends Module
{
    public const EVENT_BEFORE_INSTALL = 'wploader.before_install';
    public const EVENT_BEFORE_LOADONLY = 'wploader.before_loadonly';
    public const EVENT_AFTER_LOADONLY = 'wploader.after_loadonly';
    public const EVENT_AFTER_INSTALL = 'wploader.after_install';
    public const EVENT_AFTER_BOOTSTRAP = 'wploader.after_bootstrap';

    /**
     * The fields the user will have to set to legit values for the module to
     * run.
     *
     * wpRootFolder - the absolute path to the root folder of the WordPress
     * installation to use for testing, the ABSPATH global value.
     * dbNAme - the name of the database to use for the tests, will be trashed
     * during tests so take care, will be the DB_NAME global.
     * dbHost - the host the database can be found at, will be the DB_HOST
     * global.
     * dbUser - the database privileged user, should GRANT ALL on the database,
     * will be the DB_USER global.
     * dbPassword - the password for the user, will be the DB_PASSWORD global.
     *
     * @var array<string>
     */
    protected $requiredFields = [
        'wpRootFolder',
        'dbName',
        'dbHost',
        'dbUser',
        'dbPassword',
    ];

    /**
     * The fields the user will be able to override while running tests.
     *
     * Most of the fields have a corresponding in the standard
     * `wp-tests-config.php` file found in [WordPress automated testing
     * suite.](http://make.wordpress.org/core/handbook/automated-testing/)
     *
     * @var array{
     *     loadOnly: bool,
     *     multisite: bool,
     *     dbCharset: string,
     *     dbCollate: string,
     *     tablePrefix: string,
     *     domain: string,
     *     adminEmail: string,
     *     title: string,
     *     phpBinary: string,
     *     language: string,
     *     configFile: string|string[],
     *     pluginsFolder: string,
     *     plugins: string[],
     *     silentlyActivatePlugins: string[],
     *     bootstrapActions: string|string[],
     *     theme: string|string[],
     *     AUTH_KEY: string,
     *     SECURE_AUTH_KEY: string,
     *     LOGGED_IN_KEY: string,
     *     NONCE_KEY: string,
     *     AUTH_SALT: string,
     *     SECURE_AUTH_SALT: string,
     *     LOGGED_IN_SALT: string,
     *     NONCE_SALT: string,
     *     AUTOMATIC_UPDATER_DISABLED: bool,
     *     WP_HTTP_BLOCK_EXTERNAL: bool,
     *     WP_CONTENT_DIR?: ?string,
     *     WP_PLUGIN_DIR?: ?string,
     *     WPMU_PLUGIN_DIR?: ?string,
     *     dump: string|string[],
     *     dbUrl?: string,
     *     backupGlobals?: bool,
     *     backupGlobalsExcludeList?: string[],
     *     backupStaticAttributes?: bool,
     *     backupStaticAttributesExcludeList?: array<string,string[]>,
     *     skipInstall?: bool,
     *     beStrictAboutWpdbConnectionId?: bool
     * }
     */
    protected $config = [
        'loadOnly' => false,
        'multisite' => false,
        'dbCharset' => 'utf8',
        'dbCollate' => '',
        'tablePrefix' => 'wptests_',
        'domain' => 'example.org',
        'adminEmail' => 'admin@example.org',
        'title' => 'Test Blog',
        'phpBinary' => 'php',
        'language' => '',
        'configFile' => '',
        'pluginsFolder' => '',
        'plugins' => [],
        'silentlyActivatePlugins' => [],
        'bootstrapActions' => '',
        'theme' => '',
        'AUTH_KEY' => '',
        'SECURE_AUTH_KEY' => '',
        'LOGGED_IN_KEY' => '',
        'NONCE_KEY' => '',
        'AUTH_SALT' => '',
        'SECURE_AUTH_SALT' => '',
        'LOGGED_IN_SALT' => '',
        'NONCE_SALT' => '',
        'AUTOMATIC_UPDATER_DISABLED' => true,
        'WP_HTTP_BLOCK_EXTERNAL' => true,
        'WP_CONTENT_DIR' => null,
        'WP_PLUGIN_DIR' => null,
        'WPMU_PLUGIN_DIR' => null,
        'dump' => '',
        'backupGlobals' => false,
        'backupGlobalsExcludeList' => [],
        'backupStaticAttributes' => false,
        'backupStaticAttributesExcludeList' => [],
        'skipInstall' => false,
        'beStrictAboutWpdbConnectionId' => true
    ];

    /**
     * @var string
     */
    private $wpBootstrapFile;
    /**
     * @var \lucatume\WPBrowser\Module\WPLoader\FactoryStore
     */
    private $factoryStore;
    /**
     * @var \lucatume\WPBrowser\WordPress\Installation
     */
    private $installation;
    /**
     * @var string
     */
    private $bootstrapOutput = '';
    /**
     * @var string
     */
    private $installationOutput = '';
    /**
     * @var bool
     */
    private $earlyExit = true;
    /**
     * @var \lucatume\WPBrowser\WordPress\Database\DatabaseInterface|null
     */
    private $db;
    /**
     * @var \lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory|null
     */
    private $codeExecutionFactory;

    public function _getBootstrapOutput(): string
    {
        return $this->bootstrapOutput;
    }

    public function _getInstallationOutput(): string
    {
        return $this->installationOutput;
    }

    protected function validateConfig(): void
    {
        // Coming from required fields, the values are now defined.
        $this->config['wpRootFolder'] = $this->config['ABSPATH'] ?? $this->config['wpRootFolder'] ?? '';

        $this->parseDbCredentials();

        $this->config['dbCharset'] = $this->config['DB_CHARSET'] ?? $this->config['dbCharset'] ?? '';
        $this->config['dbCollate'] = $this->config['DB_COLLATE'] ?? $this->config['dbCollate'] ?? '';
        $this->config['multisite'] = (bool)($this->config['WP_TESTS_MULTISITE'] ?? $this->config['multisite'] ?? false);

        if (!(
            is_array($this->config['plugins'])
            && Arr::containsOnly($this->config['plugins'], 'string'))
        ) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `plugins` configuration parameter must be an array of plugin names ' .
                'in the my-plugin/plugin.php or plugin.php format.'
            );
        }

        if (!(
            is_array($this->config['silentlyActivatePlugins'])
            && Arr::containsOnly($this->config['silentlyActivatePlugins'], 'string'))
        ) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `silentlyActivatePlugins` configuration parameter must be an array of plugin names ' .
                'in the my-plugin/plugin.php or plugin.php format.'
            );
        }

        if (count(array_intersect($this->config['plugins'], $this->config['silentlyActivatePlugins']))) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `plugins` and `silentlyActivatePlugins` configuration parameters must not contain the ' .
                'same plugins.'
            );
        }

        $this->config['theme'] = $this->config['WP_TESTS_MULTISITE'] ?? $this->config['theme'] ?? '';

        if (!(
            is_string($this->config['theme'])
            || (is_array($this->config['theme']) && Arr::hasShape($this->config['theme'], ['string', 'string'])))
        ) {
            throw new ModuleConfigException(
                __CLASS__,
                "The `theme` configuration parameter must be either a string, or an array of two strings."
            );
        }

        $this->config['loadOnly'] = !empty($this->config['loadOnly']);

        if ($this->config['loadOnly'] && empty($this->config['domain'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'When using the WPLoader module to load WordPress,' .
                ' the `domain` configuration parameter must be set.'
            );
        }

        $this->config['domain'] = $this->config['WP_TESTS_DOMAIN'] ?? $this->config['domain'] ?? 'example.org';
        $this->config['adminEmail'] = $this->config['WP_TESTS_EMAIL']
            ?? $this->config['adminEmail'] ?? 'admin@example.org';
        $this->config['title'] = $this->config['WP_TESTS_TITLE'] ?? $this->config['title'] ?? 'Test Blog';
        $this->config['bootstrapActions'] = array_values(
            array_filter((array)($this->config['bootstrapActions'] ?? []))
        );
        $this->config['configFile'] = array_values(array_filter((array)($this->config['configFile'] ?? [])));

        $this->config['dump'] = array_filter((array)$this->config['dump']);
        foreach ($this->config['dump'] as $dumpFile) {
            if (!(is_string($dumpFile) && file_exists($dumpFile))) {
                throw new ModuleConfigException(
                    __CLASS__,
                    'Each `dump` configuration parameter entry must be a valid file path.'
                );
            }
        }

        if (isset($this->config['backupGlobals']) && !is_bool($this->config['backupGlobals'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `backupGlobals` configuration parameter must be a boolean.'
            );
        }

        if (isset($this->config['backupGlobalsExcludeList'])
            && !(
                is_array($this->config['backupGlobalsExcludeList'])
                && Arr::containsOnly($this->config['backupGlobalsExcludeList'], 'string')
            )
        ) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `backupGlobalsExcludeList` configuration parameter an array of strings.'
            );
        }

        if (isset($this->config['backupStaticAttributes']) && !is_bool($this->config['backupStaticAttributes'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `backupStaticAttributes` configuration parameter must be a boolean.'
            );
        }

        if (isset($this->config['backupStaticAttributesExcludeList'])
            && !(
                is_array($this->config['backupStaticAttributesExcludeList'])
                && Arr::isAssociative($this->config['backupStaticAttributesExcludeList'])
                && Arr::containsOnly(
                    $this->config['backupStaticAttributesExcludeList'],
                    static function ($v) {
                        return Arr::containsOnly($v, 'string');
                    }
                )
            )
        ) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `backupStaticAttributesExcludeList` configuration parameter an array of strings.'
            );
        }

        if (isset($this->config['skipInstall'])
            && !is_bool($this->config['skipInstall'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `skipInstall` configuration parameter must be a boolean.'
            );
        }

        if (isset($this->config['beStrictAboutWpdbConnectionId'])
            && !is_bool($this->config['beStrictAboutWpdbConnectionId'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'The `beStrictAboutWpdbConnectionId` configuration parameter must be a boolean.'
            );
        }

        parent::validateConfig();
    }

    /**
     * The function that will initialize the module.
     *
     * The function will set up the WordPress testing configuration and will
     * take care of installing and loading WordPress. The simple inclusion of
     * the module in an test helper class will hence trigger WordPress loading,
     * no explicit method calling on the user side is needed.
     *
     * @throws Throwable
     */
    public function _initialize(): void
    {
        /**
         * The config is now validated and the values are defined.
         *
         * @var array{
         *     loadOnly: bool,
         *     multisite: bool,
         *     dbCharset: string,
         *     dbCollate: string,
         *     tablePrefix: string,
         *     domain: string,
         *     adminEmail: string,
         *     title: string,
         *     phpBinary: string,
         *     language: string,
         *     configFile: string|string[],
         *     pluginsFolder: string,
         *     plugins: string[],
         *     silentlyActivatePlugins: string[],
         *     bootstrapActions: string|string[],
         *     theme: string|string[],
         *     AUTH_KEY: string,
         *     SECURE_AUTH_KEY: string,
         *     LOGGED_IN_KEY: string,
         *     NONCE_KEY: string,
         *     AUTH_SALT: string,
         *     SECURE_AUTH_SALT: string,
         *     LOGGED_IN_SALT: string,
         *     NONCE_SALT: string,
         *     AUTOMATIC_UPDATER_DISABLED: bool,
         *     WP_HTTP_BLOCK_EXTERNAL: bool,
         *     dump: string|string[],
         *     wpRootFolder: string,
         *     ABSPATH: string,
         *     dbHost: string,
         *     dbUser: string,
         *     dbPassword: string,
         *     dbName: string,
         *     tablePrefix: string,
         *     WP_CONTENT_DIR?: string,
         *     WP_PLUGIN_DIR?: string,
         *     WPMU_PLUGIN_DIR?: string,
         *     backupGlobals: bool,
         *     backupGlobalsExcludeList: string[],
         *     backupStaticAttributes: bool,
         *     backupStaticAttributesExcludeList: array<string,string[]>,
         *     skipInstall: bool,
         *     beStrictAboutWpdbConnectionId: bool
         * } $config
         */
        $config = $this->config;
        try {
            global $_wpTestsBackupGlobals, $_wpTestsBackupGlobalsExcludeList,
                   $_wpTestsBackupStaticAttributes, $_wpTestsBackupStaticAttributesExcludeList;
            $_wpTestsBackupGlobals = (bool)$config['backupGlobals'];
            $_wpTestsBackupGlobalsExcludeList = (array)$config['backupGlobalsExcludeList'];
            $_wpTestsBackupStaticAttributes = (bool)$config['backupStaticAttributes'];
            $_wpTestsBackupStaticAttributesExcludeList = (array)$config['backupStaticAttributesExcludeList'];

            if (empty($config['dbHost']) && strncmp($config['dbName'], codecept_root_dir(), strlen(codecept_root_dir())) === 0) {
                $dbFile = (array_reverse(explode(DIRECTORY_SEPARATOR, $config['dbName']))[0]);
                $dbDir = rtrim(str_replace($dbFile, '', $config['dbName']), DIRECTORY_SEPARATOR);
                $db = new SqliteDatabase($dbDir, $dbFile);
            } else {
                $db = new MysqlDatabase(
                    $config['dbName'],
                    $config['dbUser'],
                    $config['dbPassword'],
                    $config['dbHost'],
                    $config['tablePrefix']
                );
            }

            // Try and initialize the database connection now.
            $db->create();
            $db->setEnvVars();
            $this->db = $db;

            $this->installation = new Installation($config['wpRootFolder'], false);

            // Update the config to the resolved path.
            $config['wpRootFolder'] = $this->installation->getWpRootDir();
            $installationState = $this->installation->getState();

            // The WordPress root directory should be at least scaffolded, it cannot be empty.
            if ($installationState instanceof EmptyDir) {
                $wpRootDir = $this->installation->getWpRootDir();
                Installation::scaffold($wpRootDir);
                $this->installation = new Installation($wpRootDir);
            }

            if ($db instanceof SqliteDatabase && !is_file($this->installation->getContentDir('db.php'))) {
                Installation::placeSqliteMuPlugin(
                    $this->installation->getMuPluginsDir(),
                    $this->installation->getContentDir()
                );
            }

            $config['wpRootFolder'] = $this->installation->getWpRootDir();

            $configurationSalts = $this->installation->isConfigured() ?
                $this->installation->getSalts()
                : [];

            foreach ([
                    'AUTH_KEY',
                    'SECURE_AUTH_KEY',
                    'LOGGED_IN_KEY',
                    'NONCE_KEY',
                    'AUTH_SALT',
                    'SECURE_AUTH_SALT',
                    'LOGGED_IN_SALT',
                    'NONCE_SALT',
                ] as $salt
            ) {
                if (empty($config[$salt])) {
                    $config[$salt] = $configurationSalts[$salt] ?? Random::salt();
                }
            }
        } catch (DbException|InstallationException $e) {
            throw new ModuleConfigException($this, $e->getMessage(), $e);
        }

        // Define the path-related constants read from the installation, if any.
        if ($this->installation->isConfigured()) {
            foreach (['WP_CONTENT_DIR', 'WP_PLUGIN_DIR', 'WPMU_PLUGIN_DIR'] as $pathConst) {
                $constValue = $this->installation->getState()->getConstant($pathConst);
                if ($constValue && is_string($constValue)) {
                    $config[$pathConst] = $constValue;
                }
            }
        }

        // Refresh the configuration.
        $this->config = $config;

        $this->wpBootstrapFile = CorePHPUnit::bootstrapFile();

        // The `bootstrap.php` file will seek this tests configuration file before loading the test suite.
        defined('WP_TESTS_CONFIG_FILE_PATH')
        || define('WP_TESTS_CONFIG_FILE_PATH', CorePHPUnit::path('/wp-tests-config.php'));

        if (!empty($config['loadOnly'])) {
            $this->checkInstallationToLoadOnly();
            $this->debug('The WordPress installation will be loaded after all other modules have been initialized.');

            Dispatcher::addListener(Events::SUITE_BEFORE, function (): void {
                $this->loadWordPress(true);
            });

            return;
        }

        $this->ensureDbModuleCompat();

        // If the database does not already exist, then create it now.
        $db->create();

        WPTestCase::beStrictAboutWpdbConnectionId($config['beStrictAboutWpdbConnectionId']);

        $this->loadWordPress();
    }

    /**
     * Returns the absolute path to the WordPress root folder or a path within it..
     *
     * @param string|null $path The path to append to the WordPress root folder.
     *
     * @return string The absolute path to the WordPress root folder or a path within it.
     */
    public function getWpRootFolder(string $path = null): string
    {
        return $this->installation->getWpRootDir($path);
    }

    /**
     * @throws ModuleConfigException
     */
    private function ensureDbModuleCompat(): void
    {
        if ($this->config['loadOnly']) {
            return;
        }

        foreach (['MysqlDatabase', 'WPDb', WPDb::class] as $moduleName) {
            if (!$this->moduleContainer->hasModule($moduleName)) {
                continue;
            }

            $message = sprintf(
                'The WPLoader module is not being used to only load WordPress, but to also install it.'
                . PHP_EOL .
                'The %1$s module is enabled in the suite, and will try to manage the database state interfering with ' .
                'the WPLoader module.' . PHP_EOL .
                'Either:' . PHP_EOL .
                ' - remove or disable the %1$s module from the suite configuration;' . PHP_EOL .
                ' - or, configure the WPLoader module to only load WordPress, by setting the `loadOnly` ' .
                'configuration key to `true`;' . PHP_EOL .
                'If you are using the %1$s module to load a SQL dump file, you can use the `dump` configuration key ' .
                'of the WPLoader module to load one or more SQL dump files.',
                $moduleName
            );

            throw new ModuleConfigException($this, $message);
        }
    }

    /**
     * Loads WordPress calling the bootstrap file.
     *
     *
     * @throws Throwable
     */
    private function loadWordPress(bool $loadOnly = false): void
    {
        $this->loadConfigFiles();

        if ($loadOnly) {
            Dispatcher::dispatch(self::EVENT_BEFORE_LOADONLY, $this);
            $loadSandbox = new LoadSandbox($this->installation->getWpRootDir(), $this->config['domain']);
            $loadSandbox->load();
            Dispatcher::dispatch(self::EVENT_AFTER_LOADONLY, $this);
        } else {
            $this->installAndBootstrapInstallation();
        }

        wp_cache_flush();

        $this->factoryStore = new FactoryStore();

        if (Debug::isEnabled()) {
            codecept_debug(
                'WordPress status: ' . json_encode(
                    $this->installation->report(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
            );
        }
    }

    /**
     * Returns the absolute path to the plugins directory.
     *
     * The value will first look at the `WP_PLUGIN_DIR` constant, then the `pluginsFolder` configuration parameter
     * and will, finally, look in the default path from the WordPress root directory.
     *
     * @example
     * ```php
     * $plugins = $this->getPluginsFolder();
     * $hello = $this->getPluginsFolder('hello.php');
     * ```
     *
     * @param string $path A relative path to append to te plugins directory absolute path.
     *
     * @return string The absolute path to the `pluginsFolder` path or the same with a relative path appended if `$path`
     *                is provided.
     */
    public function getPluginsFolder(string $path = ''): string
    {
        return $this->installation->getPluginsDir($path);
    }

    /**
     * Returns the absolute path to the themes' directory.
     *
     * @example
     * ```php
     * $themes = $this->getThemesFolder();
     * $twentytwenty = $this->getThemesFolder('/twentytwenty');
     * ```
     *
     * @param string $path A relative path to append to te themes directory absolute path.
     *
     * @return string The absolute path to the `themesFolder` path or the same with a relative path appended if `$path`
     *                is provided.
     */
    public function getThemesFolder(string $path = ''): string
    {
        return $this->installation->getThemesDir($path);
    }

    /**
     * Installs and bootstraps the WordPress installation.
     *
     * @throws ModuleException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     */
    private function installAndBootstrapInstallation(): void
    {
        $GLOBALS['wpLoaderConfig'] = $this->config;

        $skipInstall = ($this->config['skipInstall'] ?? false)
            && !Debug::isEnabled()
            && $this->isWordPressInstalled();
        $isMultisite = $this->config['multisite'];
        $plugins = (array)$this->config['plugins'];

        Dispatcher::dispatch(self::EVENT_BEFORE_INSTALL, $this);

        if (!$skipInstall) {
            putenv('WP_TESTS_SKIP_INSTALL=0');

            /*
             * The bootstrap file will load the `wp-settings.php` one that will load plugins and the theme.
             * Hook on the option to get the active plugins to run the plugins' and theme activation
             * in a separate process.
             */
            if ($isMultisite) {
                // Activate plugins and enable theme network-wide.
                $activate = function () use (&$activate, $plugins): array {
                    remove_filter('pre_site_option_active_sitewide_plugins', $activate);
                    return $this->muActivatePluginsTheme($plugins);
                };
                PreloadFilters::addFilter('pre_site_option_active_sitewide_plugins', $activate);
            } else {
                // Activate plugins and theme.
                $activate = function () use (&$activate, $plugins): array {
                    remove_filter('pre_option_active_plugins', $activate);
                    return $this->activatePluginsTheme($plugins);
                };
                PreloadFilters::addFilter('pre_option_active_plugins', $activate);
            }
        } else {
            putenv('WP_TESTS_SKIP_INSTALL=1');
        }

        $silentPlugins = $this->config['silentlyActivatePlugins'];
        $this->includeAllPlugins(array_merge($plugins, $silentPlugins), $isMultisite);
        if (!empty($this->config['theme'])) {
            /** @var string|array{string,string} $theme */
            $theme = $this->config['theme'];
            $this->switchThemeFromFile($theme);
        }
        $this->includeCorePHPUniteSuiteBootstrapFile();

        Dispatcher::dispatch(self::EVENT_AFTER_INSTALL, $this);

        $this->disableUpdates();

        if (!$skipInstall) {
            $this->importDumps();
        }

        Dispatcher::dispatch(self::EVENT_AFTER_BOOTSTRAP, $this);

        $this->runBootstrapActions();
    }

    /**
     * @throws Throwable
     * @throws WorkerException
     * @throws ModuleException
     * @throws ProcessException
     */
    private function activatePluginsSwitchThemeInSeparateProcess(): void
    {
        /** @var array<string> $plugins */
        $plugins = (array)($this->config['plugins'] ?: []);
        $silentlyActivatePlugins = (array)($this->config['silentlyActivatePlugins'] ?: []);
        $allPlugins = array_merge($plugins, $silentlyActivatePlugins);
        $multisite = (bool)($this->config['multisite'] ?? false);
        $closuresFactory = $this->getCodeExecutionFactory();
        $silentFlags = array_merge(
            array_fill(0, count($plugins), false),
            array_fill(0, count($silentlyActivatePlugins), true)
        );

        $jobs = array_combine(
            array_map(static function (string $plugin) : string {
                return 'plugin::' . $plugin;
            }, $allPlugins),
            array_map(
                static function (string $plugin, bool $silent) use ($closuresFactory, $multisite): Closure {
                    return $closuresFactory->toActivatePlugin($plugin, $multisite, $silent);
                },
                $allPlugins,
                $silentFlags
            )
        );

        $themes = (array)$this->config['theme'];
        foreach ($themes as $theme) {
            $jobs['theme::' . basename($theme)] = $closuresFactory->toSwitchTheme($theme, $multisite);
        }

        $pluginsList = implode(', ', $plugins);
        if ($themes) {
            codecept_debug('Activating plugins: ' . $pluginsList
                . ' and switching theme(s): ' . implode(', ', array_map('basename', $themes)));
        } else {
            codecept_debug('Activating plugins: ' . $pluginsList);
        }

        foreach ((new Loop($jobs, 1, true))->run()->getResults() as $key => $result) {
            [$type, $name] = explode('::', $key, 2);
            $returnValue = $result->getReturnValue();

            if ($returnValue instanceof Throwable && !($returnValue instanceof InstallationException)) {
                // Not gift-wrapped in a ModuleException to make it easier to debug the issue.
                throw $returnValue;
            }

            $error = $result->getExitCode() !== 0 || $returnValue instanceof InstallationException;

            if ($error) {
                $reason = $returnValue instanceof InstallationException ?
                    $returnValue->getMessage()
                    : $result->getStdoutBuffer();
                $message = $type === 'plugin' ?
                    "Failed to activate plugin $name. $reason"
                    : "Failed to switch to theme $name. $reason";
                throw new ModuleException(__CLASS__, $message);
            }
        }
    }

    /**
     * Calls a list of user-defined actions needed in tests.
     */
    private function runBootstrapActions(): void
    {
        /**
         * Coming from the validation.
         *
         * @var array{
         *     bootstrapActions: array<callable|string>,
         * } $config
         */
        $config = $this->config;
        foreach ($config['bootstrapActions'] as $action) {
            if (!is_callable($action)) {
                do_action($action);
            } else {
                $action();
            }
        }
    }

    /**
     * Accessor method to get the object storing the factories for things.
     * This method gives access to the same factories provided by the
     * [Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/).
     *
     * @example
     * ```php
     * $postId = $I->factory()->post->create();
     * $userId = $I->factory()->user->create(['role' => 'administrator']);
     * ```
     *
     * @return FactoryStore A factory store, proxy to get hold of the Core suite object
     *                                                     factories.
     *
     * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/
     */
    public function factory(): FactoryStore
    {
        return $this->factoryStore;
    }


    /**
     * Returns an array of the configuration files
     * specified with the `configFile` parameter of the module configuration.
     *
     * @throws ModuleConfigException If a specified configuration file does not exist.
     */
    private function loadConfigFiles(): void
    {
        $candidates = $this->config['configFile'];
        $configFiles = [];
        $candidates = array_filter((array)$candidates);

        foreach ($candidates as $candidate) {
            $configFile = FS::realpath($candidate);

            if ($configFile === false || !is_file($configFile)) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "\nConfig file `{$candidate}` could not be found in WordPress root folder or above."
                );
            }

            require_once $configFile;
        }
    }

    /**
     * Returns the absolute path to the WordPress content directory.
     *
     * @example
     * ```php
     * $content = $this->getContentFolder();
     * $themes = $this->getContentFolder('themes');
     * $twentytwenty = $this->getContentFolder('themes/twentytwenty');
     * ```
     *
     * @param string $path An optional path to append to the content directory absolute path.
     *
     * @return string The content directory absolute path, or a path in it.
     */
    public function getContentFolder(string $path = ''): string
    {
        return $this->installation->getContentDir($path);
    }

    private function getCodeExecutionFactory(): CodeExecutionFactory
    {
        if ($this->codeExecutionFactory !== null) {
            return $this->codeExecutionFactory;
        }

        $installationState = $this->installation->getState();
        $wpConfigFilePath = $installationState instanceof Scaffolded ?
            $installationState->getWpRootDir('/wp-config.php')
            : $installationState->getWpConfigPath();

        $this->codeExecutionFactory =  new CodeExecutionFactory(
            $this->getWpRootFolder(),
            $this->config['domain'] ?: 'localhost',
            [$wpConfigFilePath => CorePHPUnit::path('/wp-tests-config.php')],
            [
                'wpLoaderIncludeWpSettings' => true,
                'wpLoaderConfig' => $this->config
            ]
        );

        return $this->codeExecutionFactory;
    }

    public function getInstallation(): Installation
    {
        return $this->installation;
    }

    /**
     * @throws ModuleException
     */
    private function checkInstallationToLoadOnly(): void
    {
        // The installation must be at least configured: it might be installed by a dump.
        if (!$this->installation->isConfigured()) {
            $dir = $this->installation->getWpRootDir();
            throw new ModuleException(
                __CLASS__,
                'The WPLoader module is configured to load WordPress only,' .
                " but the WordPress installation at {$dir} is not configured."
            );
        }
    }

    private function disableUpdates(): void
    {
        // Set Core, plugins and themes updates to right now to avoid external requests during tests.
        $updateCheckTransient = (object)[
            'last_checked' => time(),
            'version_checked' => ($nullsafeVariable1 = $this->installation->getVersion()) ? $nullsafeVariable1->getWpVersion() : null,
        ];
        set_site_transient('update_core', $updateCheckTransient);
        set_site_transient('update_plugins', $updateCheckTransient);
        set_site_transient('update_themes', $updateCheckTransient);
        remove_action('admin_init', '_maybe_update_core');
        remove_action('admin_init', '_maybe_update_plugins');
        remove_action('admin_init', '_maybe_update_themes');
        remove_action('admin_init', 'default_password_nag_handler');
    }

    /**
     * @throws ModuleException
     */
    private function importDumps(): void
    {
        $db = $this->db;

        if (!$db instanceof DatabaseInterface) {
            throw new ModuleException(
                __CLASS__,
                'The WPLoader module is configured to import dumps, but the database is not configured.'
            );
        }

        try {
            /**
             * Coming from the validation.
             *
             * @var array{
             *     dump: array<string>,
             * } $config
             */
            $config = $this->config;
            foreach ($config['dump'] as $dumpFilePath) {
                $modified = $db->import($dumpFilePath);
                $this->debug("Imported dump file `$dumpFilePath`: $modified rows modified.");
            }
        } catch (Exception $e) {
            $message = "Could not import dump file `$dumpFilePath`: " . lcfirst($e->getMessage());
            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * @throws ModuleConfigException
     */
    private function parseDbCredentials(): void
    {
        if (isset($this->config['dbUrl'])) {
            if (!is_string($this->config['dbUrl'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    'The `dbUrl` configuration parameter must be a string.'
                );
            }
            $parsedUrl = DbUtils::parseDbUrl($this->config['dbUrl']);
            $this->config['dbHost'] = $parsedUrl['host'] . ($parsedUrl['port'] ? ':' . $parsedUrl['port'] : '');
            $this->config['dbUser'] = $parsedUrl['user'];
            $this->config['dbPassword'] = $parsedUrl['password'];
            $this->config['dbName'] = $parsedUrl['name'];

            return;
        }

        $dbPassword = $this->config['DB_PASSWORD'] ?? $this->config['dbPassword'] ?? null;
        $dbHost = $this->config['DB_HOST'] ?? $this->config['dbHost'] ?? null;
        $dbName = $this->config['DB_NAME'] ?? $this->config['dbName'] ?? null;
        $dbUser = $this->config['DB_USER'] ?? $this->config['dbUser'] ?? null;

        if (count(array_filter([$dbPassword, $dbHost, $dbName, $dbUser], 'is_null')) !== 0) {
            throw new ModuleConfigException(
                __CLASS__,
                "The `dbUrl` configuration parameter must be set or the `dbPassword`, `dbHost`, `dbName`" .
                " and `dbUser` parameters must be set."
            );
        }

        $this->config['dbHost'] = $dbHost;
        $this->config['dbUser'] = $dbUser;
        $this->config['dbPassword'] = $dbPassword;
        $this->config['dbName'] = $dbName;
    }

    /**
     * While loading, WordPress might `die` or `exit` if it encounters an error.
     * Output buffering final handler will always run and provides an opportunity to orderly handle
     * the exit and provide a meaningful message about the failure to the user.
     *
     * @throws ModuleException
     * @throws Throwable
     */
    private function includeCorePHPUniteSuiteBootstrapFile(): void
    {
        ob_start(function (string $buffer, int $phase): string {
            $this->bootstrapOutput .= $buffer;

            if ($phase === PHP_OUTPUT_HANDLER_FINAL) {
                if (!$this->earlyExit) {
                    return $buffer;
                }

                // The inclusion of the test bootstrap file, or a WordPress file included by it, called `exit` or `die`.
                // Jump in on the flow to provide a meaningful message to the user.
                throw new ModuleException(__CLASS__, 'WordPress bootstrap failed.' . PHP_EOL . $buffer);
            }

            $buffer = trim($buffer);
            if ($buffer === '' || strncmp($buffer, 'Not running', strlen('Not running')) === 0) {
                // Do not print empty lines or the lines about skipped test groups.
                return '';
            }
            $this->debug('[Core bootstrap] ' . trim($buffer));
            return '';
        }, 1);

        try {
            require $this->wpBootstrapFile;
            WPTestCase::setWpdbConnectionId((string)$GLOBALS['wpdb']->get_var('SELECT CONNECTION_ID()'));
        } catch (Throwable $t) {
            // Not an early exit: Codeception will handle the Exception and print it.
            $this->earlyExit = false;
            throw $t;
        }

        $this->earlyExit = false;
        // Output has been already printed: no need to flush it.
        ob_end_clean();
    }

    /**
     * @param array<string> $plugins
     * @return array<string>
     * @throws Throwable
     */
    private function activatePluginsTheme(array $plugins): array
    {
        $this->activatePluginsSwitchThemeInSeparateProcess();

        $database = $this->db;

        if ($database === null) {
            throw new ModuleException(
                __CLASS__,
                'Could not get database instance from installation.'
            );
        }

        if ($this->config['theme']) {
            $database->updateOption('template', $database->getOption('template'));
            $database->updateOption('stylesheet', $database->getOption('stylesheet'));
        }

        // Flush the cache to force the refetch of the options' value.
        wp_cache_delete('alloptions', 'options');

        // Do not include external plugins, it would create issues at this stage.
        $pluginsDir = $this->installation->getPluginsDir();

        return array_values(
            array_filter(
                $plugins,
                static function (string $plugin) use ($pluginsDir) {
                    return is_file($pluginsDir . "/$plugin");
                }
            )
        );
    }

    /**
     * @param array<string> $plugins
     * @return array<string, int>
     *
     * @throws Throwable
     */
    private function muActivatePluginsTheme(array $plugins): array
    {
        $this->activatePluginsSwitchThemeInSeparateProcess();

        /** @var DatabaseInterface $database */
        $database = $this->db;

        if ($this->config['theme']) {
            $themes = (array)$this->config['theme'];
            // Refresh the theme related options.
            update_site_option('allowedthemes', array_combine($themes, array_fill(0, count($themes), true)));
            if ($database === null) {
                throw new ModuleException(
                    __CLASS__,
                    'Could not get database instance from installation.'
                );
            }

            update_option('template', $database->getOption('template'));
            update_option('stylesheet', $database->getOption('stylesheet'));
        }

        // Flush the cache to force the refetch of the options' value.
        wp_cache_delete("1::active_sitewide_plugins", 'site-options');

        // Do not include external plugins, it would create issues at this stage.
        $pluginsDir = $this->installation->getPluginsDir();
        $validPlugins = array_values(
            array_filter(
                $plugins,
                static function (string $plugin) use ($pluginsDir) {
                    return is_file($pluginsDir . "/$plugin");
                }
            )
        );

        // Format for site-wide active plugins is `[ 'plugin-slug/plugin.php' => timestamp ]`.
        $validActiveSitewidePlugins = array_combine(
            $validPlugins,
            array_fill(0, count($validPlugins), time())
        );

        return $validActiveSitewidePlugins;
    }

    private function isWordPressInstalled(): bool
    {
        if (!$this->db instanceof DatabaseInterface) {
            return false;
        }

        try {
            return !empty($this->db->getOption('siteurl'));
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * @param string[] $plugins
     * @throws ModuleConfigException
     */
    private function includeAllPlugins(array $plugins, bool $isMultisite): void
    {
        PreloadFilters::addFilter('plugins_loaded', function () use ($plugins, $isMultisite) {
            $activePlugins = $isMultisite ? get_site_option('active_sitewide_plugins') : get_option('active_plugins');

            if (!is_array($activePlugins)) {
                $activePlugins = [];
            }

            $pluginsDir = $this->installation->getPluginsDir();

            foreach ($plugins as $plugin) {
                if (!is_file($pluginsDir . "/$plugin")) {
                    $pluginRealPath = realpath($plugin);

                    if (!$pluginRealPath) {
                        throw new ModuleConfigException(
                            __CLASS__,
                            "Plugin file $plugin does not exist."
                        );
                    }

                    include_once $pluginRealPath;

                    // Create a name for the external plugin in the format <directory>/<file.php>.
                    $plugin = basename(dirname($pluginRealPath)) . '/' . basename($pluginRealPath);
                }

                if ($isMultisite) {
                    // Network-activated plugins are stored in the format <plugins_name> => <timestamp>.
                    $activePlugins[$plugin] = time();
                } else {
                    $activePlugins[] = $plugin;
                }
            }


            // Update the active plugins to include all plugins, external or not.
            if ($isMultisite) {
                update_site_option('active_sitewide_plugins', $activePlugins);
            } else {
                update_option('active_plugins', array_values(array_unique($activePlugins)));
            }
        }, -100000);
    }

    /**
     * @param string|array{string,string} $theme
     */
    private function switchThemeFromFile($theme):void
    {
        [$template, $stylesheet] = is_array($theme) ? $theme : [$theme, $theme];
        $templateRealpath = realpath($template);
        $stylesheetRealpath = realpath($stylesheet);
        $include = 0;

        if ($templateRealpath) {
            $include |= 1;
        }

        if ($stylesheetRealpath) {
            $include |= 2;
        }

        if ($include === 0) {
            return;
        }

        /** @var string $templateRealpath */
        /** @var string $stylesheetRealpath */

        PreloadFilters::addFilter('after_setup_theme', static function () use (
            $include,
            $templateRealpath,
            $stylesheetRealpath
        ) {
            global $wp_stylesheet_path, $wp_template_path, $wp_theme_directories;
            ($include & 1) && $wp_template_path = $templateRealpath;
            ($include & 2) && $wp_stylesheet_path = $stylesheetRealpath;
            ($include & 1) && ($wp_theme_directories[] = dirname($templateRealpath));
            ($include & 2) && ($wp_theme_directories[] = dirname($stylesheetRealpath));
            $wp_theme_directories = array_values(array_unique($wp_theme_directories));
            // Stylesheet first, template second.
            (($include & 2) && ($stylesheetRealpath !== $templateRealpath))
                && include $stylesheetRealpath . '/functions.php';
            ($include & 1) && include $templateRealpath . '/functions.php';
        }, -100000);

        $templateName = basename($templateRealpath);
        $templateRoot = dirname($templateRealpath);
        $stylesheetName = basename($stylesheetRealpath);
        $stylesheetRoot = dirname($stylesheetRealpath);

        PreloadFilters::addFilter('pre_option_template', static function () use ($templateName) {
            return $templateName;
        });
        PreloadFilters::addFilter('pre_option_template_root', static function () use ($templateRoot) {
            return $templateRoot;
        });
        PreloadFilters::addFilter('pre_option_stylesheet', static function () use ($stylesheetName) {
            return $stylesheetName;
        });
        PreloadFilters::addFilter('pre_option_stylesheet_root', static function () use ($stylesheetRoot) {
            return $stylesheetRoot;
        });
    }
}
