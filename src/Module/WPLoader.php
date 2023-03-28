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
use Codeception\Exception\ModuleConflictException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\Util\Debug;
use ErrorException;
use Exception;
use JsonException;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Module\Traits\DebugWrapping;
use lucatume\WPBrowser\Module\WPLoader\FactoryStore;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\Worker\Exited;
use lucatume\WPBrowser\Traits\WithCodeceptionModuleConfig;
use lucatume\WPBrowser\Traits\WithWordPressFilters;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestClosureFactory;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\LoadSandbox;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use stdClass;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
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
    use DebugWrapping;

    /**
     * Whether to include inherited actions or not.
     *
     * @var bool
     */
    public static bool $includeInheritedActions = true;

    /**
     * Allows to explicitly set what methods have this class.
     *
     * @var array<string>
     */
    public static array $onlyActions = [];

    /**
     * Allows to explicitly exclude actions from module.
     *
     * @var array<string>
     */
    public static array $excludeActions = [];

    /**
     * A flag to indicate whether the module should late init or not.
     *
     * @var bool
     */
    public static bool $didInit = false;

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
     * called after before any test case runs.
     * skipPluggables - bool, def. `false`, if set to `true` will skip the
     * definition of pluggable functions.
     *
     *
     * @var array<string,mixed>
     */
    // @todo review for unused/deprecated
    protected array $config = [
        'loadOnly' => false,
        'installationTableHandling' => 'empty',
        'wpDebug' => true,
        'multisite' => false,
        'skipPluggables' => false,
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
        'plugins' => '',
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

    /**
     * Initializes the module if not already initialized.
     *
     * When this method runs, the `ABSPATH` constant is not set then the module will init itself and
     * load WordPress.
     * It should really not be used elsewhere.
     *
     * @return array<string,mixed>An export-able array that will define objects and variables expected to be global when
     *                            this is called.
     *
     * @throws ModuleConfigException|ModuleException If there's any configuration error.
     * @throws ReflectionException If there's an issue building an instance of the module using reflection.
     *
     * @internal This method is very much tailored to the use in `WPTestCase` to support tests running in isolation.
     */
    public static function _maybeInit(): array
    {
        if (defined('ABSPATH') || self::$didInit) {
            // Already initialized.
            return [];
        }

        self::$didInit = true;

        $instance = static::_newInstanceWithoutConstructor();

        $instance->filterActivePlugins();
        $instance->filterTemplateStylesheet();

        return [
            'skipWordPressInstall' => true,
        ];
    }

    /**
     * Builds a new instance of the module without calling its constructor.
     *
     *
     * @return static A new instance of the module, built without calling its constructor method.
     *
     * @throws ModuleException|ModuleConfigException If an instance of the module cannot be built.
     * @throws ReflectionException if there's an issue building an instance of the module using reflection.
     */
    protected static function _newInstanceWithoutConstructor(): static
    {
        $instance = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();

        if (!$instance instanceof static) {
            throw new ModuleException($instance, 'Could not build instance.');
        }

        $instance->_setConfig(static::_getModuleConfig($instance));

        return $instance;
    }

    /**
     * The function that will initialize the module.
     *
     * The function will set up the WordPress testing configuration and will
     * take care of installing and loading WordPress. The simple inclusion of
     * the module in an test helper class will hence trigger WordPress loading,
     * no explicit method calling on the user side is needed.
     *
     * @throws DbException
     * @throws JsonException
     * @throws ModuleConfigException
     * @throws ModuleConflictException
     * @throws ModuleException
     * @throws ErrorException
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

        $this->wpBootstrapFile = CorePHPUnit::path('/includes/bootstrap.php');

        // The `bootstrap.php` file will seek this tests configuration file before loading the test suite.
        defined('WP_TESTS_CONFIG_FILE_PATH')
        || define('WP_TESTS_CONFIG_FILE_PATH', CorePHPUnit::path('/wp-tests-config.php'));

        if (!empty($this->config['loadOnly'])) {
            $this->checkInstallationToLoadOnly();
            $this->loadWordPressBeforeSuite();

            return;
        }

        // If the database does not already exist, then create it now.
        $db->create();

        // Any *Db Module should either not be running or properly configured if this has to run alongside it.
        $this->ensureDbModuleCompat();

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
     * Checks the *Db modules loaded in the suite to ensure their configuration is compatible with this module current
     * one.
     *
     *
     * @throws ModuleConflictException If the configuration of one *Db module is not compatible with this module
     *                                 configuration.
     */
    protected function ensureDbModuleCompat(): void
    {
        $dbModules = ['Db', 'WPDb'];
        $allModules = $this->moduleContainer->all();
        foreach ($dbModules as $moduleName) {
            if (!$this->moduleContainer->hasModule($moduleName)) {
                continue;
            }

            $module = $allModules[$moduleName];
            $cleanup_config = $module->_getConfig('cleanup');
            if (!empty($cleanup_config)) {
                throw new ModuleConflictException(
                    __CLASS__,
                    "{$moduleName}\nThe WP Loader module is being used together with the {$moduleName} module: "
                    . "the {$moduleName} module should have the 'cleanup' parameter set to 'false' not to interfere "
                    . "with the WP Loader module."
                );
            }
        }
    }

    /**
     * Loads WordPress calling the bootstrap file.
     *
     * @throws ModuleConfigException|JsonException|ErrorException
     */
    private function loadWordPress(bool $loadOnly = false): void
    {
        $this->loadConfigFiles();

        if ($loadOnly) {
            $loadSandbox = new LoadSandbox($this->installation->getWpRootDir(), $this->config['domain']);
            $loadSandbox->load();
        } else {
            $this->installAndBootstrapInstallation();
        }

        // Make the `factory` property available on the `$tester` property.
        $this->setupFactoryStore();

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
     *
     * @throws ModuleConfigException If the path to the plugins folder does not exist.
     */
    public function getPluginsFolder(string $path = ''): string
    {
        if (!empty($this->pluginDir)) {
            return empty($path) ? $this->pluginDir : $this->pluginDir . '/' . ltrim($path, '\\/');
        }

        if (defined('WP_PLUGIN_DIR')) {
            $candidate = WP_PLUGIN_DIR;
        } elseif (!empty($this->config['pluginsFolder'])) {
            $candidate = $this->config['pluginsFolder'];
        } else {
            $candidate = $this->getContentFolder('plugins');
        }

        try {
            $resolved = FS::resolvePath($candidate, $this->getWpRootFolder());
            if ($resolved === false) {
                throw new RuntimeException('Could not resolve path.');
            }
        } catch (Exception) {
            throw new ModuleConfigException(
                __CLASS__,
                "The path to the plugins directory ('{$candidate}') doesn't exist."
            );
        }

        $this->pluginDir = FS::untrailslashit($resolved);

        return empty($path) ? $this->pluginDir : $this->pluginDir . '/' . ltrim($path, '\\/');
    }

    private function filterActivePlugins(): void
    {
        if (empty($this->config['plugins'])) {
            return;
        }

        $GLOBALS['wp_tests_options']['active_plugins'] = array_replace([], $this->config['plugins']);
    }

    private function filterTemplateStylesheet(): void
    {
        [$stylesheet, $template] = $this->getStylesheetTemplateFromConfig();

        if ($template) {
            $GLOBALS['wp_tests_options']['template'] = $template;
            $GLOBALS['wp_tests_options']['stylesheet'] = $stylesheet;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->debugSection('WPLoader', 'Template: ' . ($template ?: 'default'));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->debugSection('WPLoader', 'Stylesheet: ' . ($stylesheet ?: 'default'));
    }

    /**
     * Installs and bootstraps the WordPress installation.
     */
    private function installAndBootstrapInstallation(): void
    {
        $this->filterActivePlugins();
        $this->filterTemplateStylesheet();

        $wpLoaderConfig = $this->config;

        ob_start($this->relayOutputToDebug('WPLoader/install'));
        require $this->wpBootstrapFile;
        ob_end_clean();

        $this->activatePluginsSwitchThemeInSeparateProcess();
        $this->runBootstrapActions();
    }

    private function activatePluginsSwitchThemeInSeparateProcess(): void
    {
        $plugins = $this->config['activatePlugins'] ?? ($this->config['plugins'] ?: []);

        if (empty($plugins)) {
            return;
        }

        $multisite = $this->config['multisite'] ?? false;

        $closure = $this->getRequestClosureFactory();

        $jobs = array_combine(
            array_map(static fn(string $plugin) => 'plugin::' . $plugin, $plugins),
            array_map(static fn(string $plugin) => $closure->toActivatePlugin($plugin, $multisite), $plugins)
        );

        [$stylesheet] = $this->getStylesheetTemplateFromConfig();
        $jobs['stylesheet::' . $stylesheet] = $closure->toSwitchTheme($stylesheet, $multisite);

        $loop = new Loop($jobs, 1, true);

        $loop->subscribeToWorkerExit($this->toDebugActivationResult());
        $loop->run()->getResults();

        if ($loop->failed()) {
            $failMessage = Debug::isEnabled() ?
                'Plugin activation failed; see output above.'
                : 'Plugin activation failed; run again with --debug to know more.';
            $this->fail($failMessage);
        }
    }

    /**
     * Calls a list of user-defined actions needed in tests.
     */
    private function runBootstrapActions(): void
    {
        if (empty($this->config['bootstrapActions'])) {
            return;
        }

        foreach ($this->config['bootstrapActions'] as $action) {
            if (!is_callable($action)) {
                do_action($action);
            } else {
                $action();
            }
        }
    }

    /**
     * Loads the plugins required by the test.
     *
     *
     * @throws ModuleConfigException If there's an issue with the configuration.
     */
    public function _loadPlugins(): void
    {
        if (empty($this->config['plugins']) || !defined('WP_PLUGIN_DIR')) {
            return;
        }
        $pluginsPath = $this->getPluginsFolder() . DIRECTORY_SEPARATOR;
        $plugins = $this->config['plugins'];
        foreach ($plugins as $plugin) {
            $path = $pluginsPath . $plugin;
            if (!is_file($path)) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "The '{$plugin}' plugin file was not found in the {$pluginsPath} directory; "
                    . 'this might be due to a wrong configuration of the `wpRootFolder` setting or a missing inclusion '
                    . 'of one ore more additional config files using the `configFile` setting.'
                );
            }
            require_once $path;
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
     * Returns a closure to handle the exit of WordPress during the bootstrap process.
     *
     * @param OutputInterface|null $output An output stream.
     * @throws JsonException If there's an issue debugging the error.
     */
    private function handleWordPressExit(OutputInterface $output = null): void
    {
        $output = $output ?: new ConsoleOutput();

        if (Debug::isEnabled()) {
            codecept_debug(
                'WordPress status: ' . json_encode($this->installation->report(),
                    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        $lines = [
            'WPLoader could not correctly load WordPress.',
            'If you do not see any other output beside this, probably a call to `die` or `exit` might have been'
            . ' made while loading WordPress files.',
            'There are a number of reasons why this might happen and the most common is an empty, incomplete or'
            . ' incoherent database status.',
            '',
            'E.g. you are trying to bootstrap WordPress as multisite on a database that does not contain '
            . 'multisite tables.',
            'Run the same test command again activating debug (-vvv) to run a WordPress status check.'
        ];

        $moduleContainer = $this->moduleContainer;

        if ($moduleContainer->hasModule('WPDb') || $moduleContainer->hasModule('Db')) {
            $dbModule = $moduleContainer->hasModule('WPDb') ? 'WPDb' : 'Db';
            $lines [] = '';
            $lines[] = "It looks like, alongside the WPLoader module, you are using the {$dbModule} one.";
            if (empty($this->config['loadOnly'])) {
                $lines[] = 'Since the `WPLoader::loadOnly` parameter is not set or set to `false` both the '
                    . "WPLoader module and the {$dbModule} one are trying to populate the database.";
                $lines[] = "If you want to fill the database with a dump then keep using the {$dbModule} "
                    . 'module but set the `WPLoader::loadOnly` parameter to `true` and make sure that, '
                    . "in the suite configuration file, in the `modules` section, the {$dbModule} module comes"
                    . ' before the WPLoader one.';
                $lines[] = '';
                $lines[] = 'If you are, instead, trying to run integration tests you do not probably need the'
                    . " {$dbModule} module or should set the `populate` and `cleanup` arguments to `false`";
            } else {
                $lines[] = 'Since the `WPLoader::loadOnly` parameter is set to `true` the WPLoader module'
                    . ' will not try to populate the database.';
                $lines[] = "The database should be populated from a dump using the {$dbModule} modules.";
                $lines[] = 'Make sure the SQL dump you\'re trying to use is not empty and correct for the kind '
                    . 'of installation you are trying to test.';
                $lines[] = 'Make also sure that, in the suite configuration file, in the `modules` section, ' .
                    "the {$dbModule} modules comes before the WPLoader one." .
                    $lines[] = '';
                $lines[] = 'If you are, instead, trying to run integration tests you do not probably need the'
                    . " {$dbModule} module or should set the `populate` and `cleanup` arguments to `false` and "
                    . 'set the `WPLoader::loadOnly` parameter to `false` to let the WPLoader module populate the'
                    . ' database for you.';
            }
            $lines[] = 'Find out more about this at '
                . 'https://wpbrowser.wptestkit.dev/summary/modules/wploader'
                . '#wploader-to-only-bootstrap-wordpress';
        } else {
            $lines[] = 'Since the `WPLoader::loadOnly` parameter is set to `true` the WPLoader module'
                . ' will not try to populate the database.';
            $lines[] = 'The database should be populated from a dump using the WPDb/Db modules.';
        }

        $output->writeln('<error>' . implode(PHP_EOL, $lines) . '</error>');
    }

    /**
     * Instantiates and sets up the factory store that will be available on the suite tester.
     */
    private function setupFactoryStore(): void
    {
        $this->factoryStore = new FactoryStore();
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
     *
     * @throws ModuleConfigException If the path to the content directory cannot be resolved.
     */
    public function getContentFolder(string $path = ''): string
    {
        if (!empty($this->contentDir)) {
            return empty($path) ? $this->contentDir : $this->contentDir . '/' . ltrim($path, '\\/');
        }

        if (defined('WP_CONTENT_DIR')) {
            $candidate = WP_CONTENT_DIR;
        } elseif (!empty($this->config['contentFolder'])) {
            $candidate = $this->config['contentFolder'];
        } else {
            $candidate = $this->getWpRootFolder('wp-content');
        }

        try {
            $resolved = FS::resolvePath($candidate, $this->getWpRootFolder());
            if ($resolved === false) {
                throw new RuntimeException('Could not resolve path.');
            }
        } catch (Exception) {
            throw new ModuleConfigException(
                __CLASS__,
                "The path to the content directory ('{$candidate}') doesn't exist or is not accessible."
            );
        }

        $this->contentDir = FS::untrailslashit($resolved);

        return empty($path) ? $this->contentDir : $this->contentDir . '/' . ltrim($path, '\\/');
    }

    private function getStylesheetTemplateFromConfig(): array
    {
        [$template, $stylesheet] = array_replace([null, null], (array)$this->config['theme']);
        $template = $template ?: $this->config['theme'];
        $stylesheet = $stylesheet ?: $this->config['theme'];
        return array($stylesheet, $template);
    }

    private function toDebugActivationResult(): Closure
    {
        return function (Exited $exited): void {
            $id = $exited->getId();
            $exitCode = $exited->getExitCode();

            if (str_starts_with($id, 'plugin::')) {
                $format = 'Activating Plugin %s: %s';
                $name = substr($id, 8);
            } else {
                $format = 'Switching to theme %s: %s';
                $name = substr($id, 12);
            }

            if ($exitCode === 0) {
                $result = 'OK';
            } else {
                $stdout = $exited->getStdout();
                $stderr = $exited->getStderr();
                $result = "FAILED\n\tExit code: $exitCode\n\tSTDOUT: $stdout\n\tSTDERR: $stderr";
                $returnValue = $exited->getReturnValue();
                if ($returnValue instanceof Throwable) {
                    $traceAsString = str_replace("\n", "\n\t\t", $returnValue->getTraceAsString());
                    $errorClass = $returnValue instanceof SerializableThrowable ?
                        $returnValue->getWrappedThrowableClass()
                        : get_class($returnValue);
                    $result .= "\n\tThrown $errorClass:\n\t\t{$returnValue->getMessage()}\n\t\t" . $traceAsString;
                }
            }
            $message = sprintf($format, $name, $result);
            $this->debugSection('WPLoader', $message);
        };
    }

    private function getRequestClosureFactory(): FileRequestClosureFactory
    {
        $requestFactory = new FileRequestFactory(
            $this->getWpRootFolder(),
            $this->config['domain'] ?: 'localhost',
            [ABSPATH . 'wp-config.php' => CorePHPUnit::path('/wp-tests-config.php')],
            [
                'wpLoaderIncludeWpSettings' => true,
                'wpLoaderConfig' => $this->config
            ]
        );
        return new FileRequestClosureFactory($requestFactory);
    }

    public function getInstallation(): Installation
    {
        return $this->installation;
    }

    private function loadWordPressBeforeSuite(): void
    {
        $this->debug('WPLoader module will load WordPress when all other modules initialized.');

        Dispatcher::addListener(Events::SUITE_BEFORE, function () {
            $this->loadWordPress(true);
        }, -100);
    }

    /**
     * @throws ModuleException
     * @throws ModuleConfigException
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

        $domain = getenv('WP_DOMAIN') ?: $this->config['domain'];

        if (empty($domain)) {
            throw new ModuleConfigException(
                __CLASS__,
                'When using the WPLoader module to load WordPress,' .
                ' the `domain` configuration parameter must be set.'
            );
        }

    }
}
