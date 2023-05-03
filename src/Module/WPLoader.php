<?php
/**
 * A module to load WordPress.
 *
 * @package Codeception\Module;
 */

namespace lucatume\WPBrowser\Module;

use Codeception\Command\Shared\ConfigTrait;
use Codeception\Events;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Util\Debug;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\Traits\DebugWrapping;
use lucatume\WPBrowser\Module\WPLoader\FactoryStore;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Traits\WithCodeceptionModuleConfig;
use lucatume\WPBrowser\Traits\WithWordPressFilters;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\Db as DbUtils;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\Db;
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
    use WithWordPressFilters;
    use ConfigTrait;
    use WithCodeceptionModuleConfig;

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
     * loadOnly - just load WordPress, skip the installation.
     * wpDebug - bool, def. `true`, the WP_DEBUG
     * global value.
     * multisite - bool, def.
     * `false`, if set to `true` will create a multisite installation, the
     * WP_TESTS_MULTISITE global value. dbCharset - string, def. `utf8`, the
     * DB_CHARSET global value. dbCollate - string, def. ``, the DB_COLLATE
     * global value. tablePrefix - string, def. `wptests_`, the
     * WP_TESTS_TABLE_PREFIX value. domain - string, def. `example.org`, the
     * root URL of the site, the WP_TESTS_DOMAIN global value. adminEmail -
     * string, def. `admin@example.org`, the admin email, the WP_TEST_EMAIL
     * global value. title - string, def. `Test Blog`, the blog title, the
     * WP_TESTS_TITLE global value. phpBinary - string, def. `php`, the php bin
     * command, the WP_PHP_BINARY global value. language - string, def. ``, the
     * installation language, the WPLANG global value. configFile - string or
     * array, def. ``, the path, or an array of paths, to custom config file(s)
     * relative to the `wpRootFolder` folder, no leading slash needed; this is
     * the place where custom `wp_tests_options` could be set. pluginsFolder -
     * string, def. ``, the relative path to the plugins folder in respect to
     * the WP root folder plugins - array, def. `[]`, a list of plugins that
     * should be loaded before any test case runs and after mu-plugins have
     * been loaded; these should be defined in the
     * `folder/plugin-file.php` format.
     * activatePlugins - array, def. `[]`, a list of plugins that should be
     * activated calling the `activate_{$plugin}` before any test case runs and
     * after mu-plugins have been loaded; these should be defined in the
     * `folder/plugin-file.php` format.
     * bootstrapActions - array, def. `[]`, a list of actions that should be
     * called before any test case runs.
     * skipPluggables - bool, def. `false`, if set to `true` will skip the
     * definition of pluggable functions.
     *
     * @var array<string,mixed>
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
        'contentFolder' => '',
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
        'dump' => '',
    ];

    /**
     * The path to the modified tests bootstrap file.
     *
     * @var string
     */
    protected string $wpBootstrapFile;

    /**
     * The absolute path to the plugins directory.
     *
     * @var string
     */
    protected string $pluginDir;

    /**
     * The absolute path to the content directory.
     *
     * @var string
     */
    protected string $contentDir;

    /**
     * @var FactoryStore
     */
    protected FactoryStore $factoryStore;

    /**
     * A list of redirections triggered by WordPress during load in the shape location to status codes.
     *
     * @var array<string,int>
     */
    private array $loadRedirections = [];
    private Installation $installation;
    private string $bootstrapOutput = '';
    private string $installationOutput = '';

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
        $this->config['wpRootFolder'] = $this->config['ABSPATH'] ?? $this->config['wpRootFolder'] ?? '';

        $this->parseDbCredentials();

        $this->config['dbCharset'] = $this->config['DB_CHARSET'] ?? $this->config['dbCharset'] ?? '';
        $this->config['dbCollate'] = $this->config['DB_COLLATE'] ?? $this->config['dbCollate'] ?? '';
        $this->config['multisite'] = $this->config['WP_TESTS_MULTISITE'] ?? $this->config['multisite'] ?? '';
        $this->config['theme'] = $this->config['WP_TESTS_MULTISITE'] ?? $this->config['theme'] ?? '';
        if (is_array($this->config['theme'])) {
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
        $this->config['adminEmail'] = $this->config['WP_TESTS_EMAIL'] ?? $this->config['adminEmail'] ?? 'admin@example.org';
        $this->config['title'] = $this->config['WP_TESTS_TITLE'] ?? $this->config['title'] ?? 'Test Blog';
        $this->config['bootstrapActions'] = array_values(array_filter((array)($this->config['bootstrapActions'] ?? [])));
        $this->config['configFile'] = array_values(array_filter((array)($this->config['configFile'] ?? [])));

        $this->config['dump'] = array_filter((array)$this->config['dump']);
        foreach ($this->config['dump'] as $dumpFile) {
            if (!file_exists($dumpFile)) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "The dump file `$dumpFile` does not exist."
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
        try {
            $db = new Db(
                $this->config['dbName'],
                $this->config['dbUser'],
                $this->config['dbPassword'],
                $this->config['dbHost'],
                $this->config['tablePrefix']
            );
            // Try and initialize the database connection now.
            $db->create();

            $this->installation = new Installation($this->config['wpRootFolder'], $db);
            $installationState = $this->installation->getState();

            // The WordPress root directory should be at least scaffolded, it cannot be empty.
            if ($installationState instanceof EmptyDir) {
                $wpRootDir = $this->installation->getWpRootDir();
                Installation::scaffold($wpRootDir);
                $this->installation = new Installation($wpRootDir, $db);
            }

            $this->config['wpRootFolder'] = $this->installation->getWpRootDir();

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
                     ] as $salt) {
                if (empty($this->config[$salt])) {
                    $this->config[$salt] = $configurationSalts[$salt] ?? Random::salt();
                }
            }
        } catch (DbException|InstallationException $e) {
            throw new ModuleConfigException($this, $e->getMessage(), $e);
        }

        $this->wpBootstrapFile = CorePHPUnit::bootstrapFile();

        // The `bootstrap.php` file will seek this tests configuration file before loading the test suite.
        defined('WP_TESTS_CONFIG_FILE_PATH')
        || define('WP_TESTS_CONFIG_FILE_PATH', CorePHPUnit::path('/wp-tests-config.php'));

        if (!empty($this->config['loadOnly'])) {
            $this->checkInstallationToLoadOnly();
            $this->debug('The WordPress installation will be loaded after all other modules have been initialized.');

            Dispatcher::addListener(Events::SUITE_BEFORE, function () {
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

        foreach (['Db', 'WPDb', WPDb::class] as $moduleName) {
            if (!$this->moduleContainer->hasModule($moduleName)) {
                continue;
            }

            $message = sprintf(
                'The WPLoader module is not being used to only load WordPress, but to also install it.' . PHP_EOL .
                'The %1$s module is enabled in the suite, and will try to manage the database state interfering with ' .
                'the WPLoader module.' . PHP_EOL .
                'Either:' . PHP_EOL .
                ' - remove or disable the %1$s module from the suite configuration;' . PHP_EOL .
                ' - or, configure the WPLoader module to only load WordPress, by setting the `loadOnly` configuration ' .
                'key to `true`;' . PHP_EOL .
                'If you are using the %1$s module to load a SQL dump file, you can use the `dump` configuration key of ' .
                'the WPLoader module to load one or more SQL dump files.',
                $moduleName
            );

            throw new ModuleConfigException($this, $message);
        }
    }

    /**
     * Loads WordPress calling the bootstrap file.
     *
     * @param bool $loadOnly
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
            codecept_debug('WordPress status: ' . json_encode($this->installation->report(),
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
     * Returns the absolute path to the themes directory.
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
            $activatePlugins = function () use (&$activatePlugins, $plugins): array {
                remove_filter('pre_site_option_active_sitewide_plugins', $activatePlugins);
                $this->activatePluginsSwitchThemeInSeparateProcess();

                if ($this->config['theme']) {
                    // Refresh the theme related options.
                    update_site_option('allowedthemes', [$this->config['theme'] => true]);
                    $db = $this->installation->getDb();
                    update_option('template', $db->getOption('template'));
                    update_option('stylesheet', $db->getOption('stylesheet'));
                }

                // Format for site-wide active plugins is `[ 'plugin-slug/plugin.php' => timestamp ]`.
                return array_combine($plugins, array_fill(0, count($plugins), time()));
            };
            PreloadFilters::addFilter('pre_site_option_active_sitewide_plugins', $activatePlugins);
        } else {
            // Activate plugins and theme.
            $activatePlugins = function () use (&$activatePlugins, $plugins): array {
                remove_filter('pre_option_active_plugins', $activatePlugins);
                $this->activatePluginsSwitchThemeInSeparateProcess();

                if ($this->config['theme']) {
                    // Refresh the theme related options.
                    $db = $this->installation->getDb();
                    update_option('template', $db->getOption('template'));
                    update_option('stylesheet', $db->getOption('stylesheet'));
                }

                return $plugins;
            };
            PreloadFilters::addFilter('pre_option_active_plugins', $activatePlugins);
        }

        $bootstrapSuccessful = false;
        ob_start(function ($buffer) use (&$bootstrapSuccessful) {
            $this->bootstrapOutput = $buffer;
            if ($bootstrapSuccessful === true) {
                return;
            }

            // The bootstrap failed and called exit 1.
            throw new ModuleException(__CLASS__, 'WordPress bootstrap failed.' . PHP_EOL . $buffer);
        });
        require $this->wpBootstrapFile;
        $bootstrapSuccessful = true;
        ob_end_clean();

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
        $plugins = (array)($this->config['plugins'] ?: []);
        $multisite = $this->config['multisite'] ?? false;
        $closuresFactory = $this->getClosuresFactory();

        $jobs = array_combine(
            array_map(static fn(string $plugin) => 'plugin::' . $plugin, $plugins),
            array_map(static fn(string $plugin) => $closuresFactory->toActivatePlugin($plugin, $multisite), $plugins)
        );

        $stylesheet = $this->config['theme'];
        if ($stylesheet) {
            $jobs['stylesheet::' . $stylesheet] = $closuresFactory->toSwitchTheme($stylesheet, $multisite);
        }

        $results = (new Loop($jobs, 1, true))->run()->getResults();

        foreach ($results as $key => $result) {
            [$type, $name] = explode('::', $key, 2);
            $returnValue = $result->getReturnValue();

            if ($returnValue instanceof Throwable) {
                $this->debug($returnValue->getMessage());
                $this->debug($returnValue->getTraceAsString());
                // Not gift-wrapped in a ModuleException to make it easier to debug the issue.
                throw $returnValue;
            }

            if ($result->getExitCode() !== 0) {
                $message = $type === 'plugin' ?
                    "Failed to activate plugin $name: {$result->getStdoutBuffer()}"
                    : "Failed to switch theme $name: {$result->getStdoutBuffer()}";
                throw new ModuleException(__CLASS__, $message);
            }
        }
    }

    /**
     * Calls a list of user-defined actions needed in tests.
     */
    private function runBootstrapActions(): void
    {
        foreach ($this->config['bootstrapActions'] as $action) {
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
     * Returns an array of the configuration files specified with the `configFile` parameter of the module configuarion.
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
        $db = $this->installation->getDb();

        if (!$db instanceof Db) {
            throw new ModuleException(
                __CLASS__,
                'The WPLoader module is configured to import dumps, but the database is not configured.'
            );
        }

        try {
            foreach ($this->config['dump'] as $dumpFilePath) {
                $modified = $db->import($dumpFilePath);
                $this->debug("Imported dump file `$dumpFilePath`: $modified rows modified.");
            }
        } catch (\Exception $e) {
            $message = "Could not import dump file `$dumpFilePath`: " . lcfirst($e->getMessage());
            throw new ModuleException(__CLASS__, $message);
        }
    }

    private function parseDbCredentials(): void
    {
        if (isset($this->config['dbUrl'])) {
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
}
