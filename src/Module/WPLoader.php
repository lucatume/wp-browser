<?php
/**
 * A module to load WordPress.
 *
 * @package Codeception\Module;
 */

namespace lucatume\WPBrowser\Module;

use Closure;
use Codeception\Command\Shared\ConfigTrait;
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
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\Db as DbUtils;
use lucatume\WPBrowser\Utils\Filesystem as FS;
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
    use ConfigTrait;

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
    protected array $requiredFields = [
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
     *     bootstrapActions: string|string[],
     *     theme: string,
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
     *     dbUrl?: string
     * }
     */
    protected array $config = [
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
        'dump' => ''
    ];

    private string $wpBootstrapFile;
    private FactoryStore $factoryStore;
    private Installation $installation;
    private string $bootstrapOutput = '';
    private string $installationOutput = '';
    private bool $earlyExit = true;
    private ?DatabaseInterface $db = null;

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
        $this->config['theme'] = $this->config['WP_TESTS_MULTISITE'] ?? $this->config['theme'] ?? '';

        if (!is_string($this->config['theme'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "The `theme` configuration parameter must be a string.\n" .
                "For child themes, use the child theme slug."
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
         *     bootstrapActions: string|string[],
         *     theme: string,
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
         *     WPMU_PLUGIN_DIR?: string
         * } $config
         */
        $config = $this->config;
        try {
            if (empty($config['dbHost']) && str_starts_with($config['dbName'], codecept_root_dir())) {
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
            }, -100);

            return;
        }

        $this->ensureDbModuleCompat();

        // If the database does not already exist, then create it now.
        $db->create();

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

        $this->factoryStore = new FactoryStore();

        if (Debug::isEnabled()) {
            codecept_debug(
                'WordPress status: ' . json_encode(
                    $this->installation->report(),
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
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
     * @param string $path A relative path to append to te plugins directory absolute path.
     *
     * @return string The absolute path to the `pluginsFolder` path or the same with a relative path appended if `$path`
     *                is provided.
     * @example
     * ```php
     * $plugins = $this->getPluginsFolder();
     * $hello = $this->getPluginsFolder('hello.php');
     * ```
     *
     */
    public function getPluginsFolder(string $path = ''): string
    {
        return $this->installation->getPluginsDir($path);
    }

    /**
     * Returns the absolute path to the themes directory.
     *
     * @param string $path A relative path to append to te themes directory absolute path.
     *
     * @return string The absolute path to the `themesFolder` path or the same with a relative path appended if `$path`
     *                is provided.
     * @example
     * ```php
     * $themes = $this->getThemesFolder();
     * $twentytwenty = $this->getThemesFolder('/twentytwenty');
     * ```
     *
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

        Dispatcher::dispatch(self::EVENT_BEFORE_INSTALL, $this);

        $isMultisite = $this->config['multisite'];
        $plugins = (array)$this->config['plugins'];

        /*
         * The bootstrap file will load the `wp-settings.php` one that will load plugins and the theme.
         * Hook on the option to get the the active plugins to run the plugins' and theme activation
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

        $this->includeCorePHPUniteSuiteBootstrapFile();

        Dispatcher::dispatch(self::EVENT_AFTER_INSTALL, $this);

        $this->disableUpdates();

        $this->importDumps();

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
        $multisite = (bool)($this->config['multisite'] ?? false);
        $closuresFactory = $this->getClosuresFactory();

        $jobs = array_combine(
            array_map(static fn(string $plugin): string => 'plugin::' . $plugin, $plugins),
            array_map(
                static fn(string $plugin): Closure => $closuresFactory->toActivatePlugin($plugin, $multisite),
                $plugins
            )
        );

        /** @var string $stylesheet */
        $stylesheet = $this->config['theme'];
        if ($stylesheet) {
            $jobs['stylesheet::' . $stylesheet] = $closuresFactory->toSwitchTheme($stylesheet, $multisite);
        }

        $pluginsList = implode(', ', $plugins);
        if ($stylesheet) {
            codecept_debug('Activating plugins: ' . $pluginsList . ' and switching theme: ' . $stylesheet);
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
                    : "Failed to switch theme $name. $reason";
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
     * This methods gives access to the same factories provided by the
     * [Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/).
     *
     * @return FactoryStore A factory store, proxy to get hold of the Core suite object
     *                                                     factories.
     *
     * @example
     * ```php
     * $postId = $I->factory()->post->create();
     * $userId = $I->factory()->user->create(['role' => 'administrator']);
     * ```
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
     * @param string $path An optional path to append to the content directory absolute path.
     *
     * @return string The content directory absolute path, or a path in it.
     * @example
     * ```php
     * $content = $this->getContentFolder();
     * $themes = $this->getContentFolder('themes');
     * $twentytwenty = $this->getContentFolder('themes/twentytwenty');
     * ```
     *
     */
    public function getContentFolder(string $path = ''): string
    {
        return $this->installation->getContentDir($path);
    }

    private function getClosuresFactory(): CodeExecutionFactory
    {
        $installationState = $this->installation->getState();
        $wpConfigFilePath = $installationState instanceof Scaffolded ?
            $installationState->getWpRootDir('/wp-config.php')
            : $installationState->getWpConfigPath();

        return new CodeExecutionFactory(
            $this->getWpRootFolder(),
            $this->config['domain'] ?: 'localhost',
            [$wpConfigFilePath => CorePHPUnit::path('/wp-tests-config.php')],
            [
                'wpLoaderIncludeWpSettings' => true,
                'wpLoaderConfig' => $this->config
            ]
        );
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
            'version_checked' => $this->installation->getVersion()?->getWpVersion(),
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
            if ($buffer === '' || str_starts_with($buffer, 'Not running')) {
                // Do not print empty lines or the lines about skipped test groups.
                return '';
            }
            $this->debug('[Core bootstrap] ' . trim($buffer));
            return '';
        }, 1);

        try {
            require $this->wpBootstrapFile;
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

        /** @var DatabaseInterface $database */
        $database = $this->db;

        if ($this->config['theme']) {
            // Refresh the theme related options.
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
        wp_cache_delete('alloptions', 'options');

        return $plugins;
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
            // Refresh the theme related options.
            update_site_option('allowedthemes', [$this->config['theme'] => true]);
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

        // Format for site-wide active plugins is `[ 'plugin-slug/plugin.php' => timestamp ]`.
        return array_combine($plugins, array_fill(0, count($plugins), time()));
    }
}
