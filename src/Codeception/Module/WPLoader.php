<?php
/**
 * A module to load WordPress.
 *
 * @package Codeception\Module;
 */

namespace Codeception\Module;

use Codeception\Command\Shared\Config;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleConflictException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Util\Debug;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use tad\WPBrowser\Adapters\WP;
use tad\WPBrowser\Module\Support\WPHealthcheck;
use tad\WPBrowser\Module\WPLoader\FactoryStore;
use tad\WPBrowser\Traits\SerializableModule;
use tad\WPBrowser\Traits\WithCodeceptionModuleConfig;
use tad\WPBrowser\Traits\WithEvents;
use tad\WPBrowser\Traits\WithWordPressFilters;
use tad\WPBrowser\Utils\Configuration;
use function tad\WPBrowser\findHereOrInParent;
use function tad\WPBrowser\resolvePath;
use function tad\WPBrowser\unleadslashit;
use function tad\WPBrowser\untrailslashit;

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

    use WithEvents;
    use WithWordPressFilters;
    use Config;
    use WithCodeceptionModuleConfig;

    /**
     * Whether to include inherited actions or not.
     *
     * @var bool
     */
    public static $includeInheritedActions = true;

    /**
     * Allows to explicitly set what methods have this class.
     *
     * @var array<string>
     */
    public static $onlyActions = [];

    /**
     * Allows to explicitly exclude actions from module.
     *
     * @var array<string>
     */
    public static $excludeActions = [];

    /**
     * A flag to indicate whether the module should late init or not.
     *
     * @var bool
     */
    public static $didInit = false;

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
     * All of the fields have a correspondant in the standard
     * `wp-tests-config.php` file found in [WordPress automated testing
     * suite.](http://make.wordpress.org/core/handbook/automated-testing/)
     *
     * loadOnly - bool, def. `false`, whether the module should load WordPress
     * and install it (`false`) or just only load it
     * (`true`) isolatedInstall - bool, def. `true`, whether the WP
     * installation should happen in an isolated process(core like) or not
     * (previous wp-browser method). wpDebug - bool, def. `true`, the WP_DEBUG
     * global value. multisite - bool, def.
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
     *
     *
     * @var array<string,mixed>|Configuration
     */
    protected $config
        = [
            'loadOnly'                  => false,
            'isolatedInstall'           => true,
            'installationTableHandling' => 'empty',
            'wpDebug'                   => true,
            'multisite'                 => false,
            'dbCharset'                 => 'utf8',
            'dbCollate'                 => '',
            'tablePrefix'               => 'wptests_',
            'domain'                    => 'example.org',
            'adminEmail'                => 'admin@example.org',
            'title'                     => 'Test Blog',
            'phpBinary'                 => 'php',
            'language'                  => '',
            'configFile'                => '',
            'contentFolder'             => '',
            'pluginsFolder'             => '',
            'plugins'                   => '',
            'activatePlugins'           => '',
            'bootstrapActions'          => '',
            'theme'                     => '',
        ];

    /**
     * The path to the modified tests bootstrap file.
     *
     * @var string
     */
    protected $wpBootstrapFile;

    /**
     * @var string The absolute path to WP root folder (`ABSPATH`).
     */
    protected $wpRootFolder;

    /**
     * The absolute path to the plugins directory.
     *
     * @var string
     */
    protected $pluginDir;

    /**
     * The absolute path to the content directory.
     *
     * @var string
     */
    protected $contentDir;

    /**
     * @var FactoryStore
     */
    protected $factoryStore;

    /**
     * Whether WordPress did load correctly or not.
     *
     * @var bool
     */
    protected $wpDidLoadCorrectly = false;

    /**
     * An array of the database populating modules found in the module container.
     *
     * @var array<Module>
     */
    protected $dbModules;

    /**
     * A list of redirections triggered by WordPress during load in the shape location to status codes.
     *
     * @var array<string,int>
     */
    protected $loadRedirections = [];

    /**
     * An instance of the WordPress healthcheck provider object.
     *
     * @var WPHealthcheck|null
     */
    protected $healthcheck;

    /**
     * An instance of the WordPress adapter.
     *
     * @var WP
     */
    protected $wp;

    /**
     * WPLoader constructor.
     *
     * @param ModuleContainer    $moduleContainer The current module container.
     * @param array<string,mixed>                   $config The current module configuration.
     * @param WP|null            $wp An instance of the WordPress adapter.
     * @param WPHealthcheck|null $healthcheck An instance of the WPHealthcheck object.
     */
    public function __construct(
        ModuleContainer $moduleContainer,
        $config,
        WP $wp = null,
        WPHealthcheck $healthcheck = null
    ) {
        parent::__construct($moduleContainer, $config);
        $this->wp = $wp ? $wp : new WP();
        $this->healthcheck = $healthcheck ? $healthcheck : new WPHealthcheck();
    }

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
     *
     * @internal This method is very much tailored to the use in `WPTestCase` to support tests running in isolation.
     */
    public static function _maybeInit()
    {
        if (defined('ABSPATH') || self::$didInit) {
            // Already initialized.
            return [];
        }

        self::$didInit = true;

        $instance    = static::_newInstanceWithoutConstructor();

        $instance->defineConstants($instance->_getConstants());
        $instance->setActivePlugins();
        $instance->_setActiveTheme();

        return [
            'skipWordPressInstall' => true,
            'installationConfiguration' => $instance->getInstallationConfiguration()
        ];
    }

    /**
     * Builds a new instance of the module without calling its contructor.
     *
     *
     * @return static A new instance of the module, built without calling its constructor method.
     *
     * @throws ModuleException|ModuleConfigException If an instance of the module cannot be built.
     */
    protected static function _newInstanceWithoutConstructor()
    {
        $instance              = (new \ReflectionClass(self::class))->newInstanceWithoutConstructor();

        if (! $instance instanceof static) {
            throw new ModuleException($instance, 'Could not build instance.');
        }

        $instance->wp          = new WP();
        $instance->healthcheck = new WPHealthcheck();
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
     * @return void
     */
    public function _initialize()
    {
        $this->initialize();
    }

    /**
     * Initializes the module making some initial checks and setting up the paths.
     *
     * @return void
     *
     * @throws ModuleConfigException If the WordPress root directory specified in the configuration is not valid.
     * @throws ModuleConflictException If a *Db module is loaded alongside this one and the settings of each are not
     *                                 compatible with each other.
     */
    protected function initialize()
    {
        self::$didInit = true;
        $configArray = $this->config instanceof Configuration ? $this->config->toArray() : $this->config;
        $this->config = new Configuration($configArray, [
            'wpRootDir' => 'wpRootFolder',
            'pluginsDir' => 'pluginsFolder',
            'contentDir' => 'contentFolder'
        ]);

        $this->ensureWPRoot($this->getWpRootFolder());

        // WordPress  will deal with database connection errors.
        $this->wpBootstrapFile = dirname(dirname(__DIR__)) . '/includes/bootstrap.php';

        $loadOnly = ! empty($this->config['loadOnly']);

        if ($loadOnly) {
            $this->debug('WPLoader module will load WordPress when all other modules initialized.');
            $this->addAction(WPDb::EVENT_BEFORE_SUITE, [$this, '_loadWordpress']);

            return;
        }

        // Any *Db Module should either not be running or properly configured if this has to run alongside it.
        $this->ensureDbModuleCompat();

        $this->_loadWordpress();
    }

    /**
     * Checks the root directory.
     *
     * @param string $wpRootFolder The current WordPress root directory.
     * @param bool $throw Whether to throw an exception on invalid path or return a value.
     *
     * @return bool Whether the current root directory is valid or not.
     *
     * @throws ModuleConfigException If the specified WordPress root folder is not found or not valid.
     */
    protected function ensureWPRoot($wpRootFolder, $throw = true)
    {
        if (! file_exists($wpRootFolder . DIRECTORY_SEPARATOR . 'wp-settings.php')) {
            if (!$throw) {
                return false;
            }

            throw new ModuleConfigException(
                __CLASS__,
                "\nThe path `{$wpRootFolder}` is not pointing to a valid WordPress installation folder."
            );
        }

        return true;
    }

    /**
     * Parses and validates the WordPress root directory path from the configuration.
     *
     * @param string $path An optional path to append to the WordPress root folder path.
     *
     * @return string The absolute path to the WordPress root directory.
     */
    protected function getWpRootFolder($path = '')
    {
        if (empty($this->wpRootFolder)) {
            $wpRootFolder = $this->config['wpRootFolder'];
            // Maybe the user is using the `~` symbol for home?
            $wpRootFolder = (string)resolvePath($wpRootFolder);
            // Remove `\ ` spaces in folder paths.
            $wpRootFolder = str_replace('\ ', ' ', $wpRootFolder);
            // Resolve to real path if relative or symlinked.
            if ($realPath = realpath($wpRootFolder)) {
                $wpRootFolder = $realPath;
            }
            // Allow me not to bother with trailing slashes.
            $this->wpRootFolder = untrailslashit($wpRootFolder) . '/';
        }

        return empty($path) ? $this->wpRootFolder : $this->wpRootFolder . unleadslashit($path);
    }

    /**
     * Checks the *Db modules loaded in the suite to ensure their configuration is compatible with this module current
     * one.
     *
     * @return void
     *
     * @throws ModuleConflictException If the configuration of one *Db module is not compatible with this module
     *                                 configuration.
     */
    protected function ensureDbModuleCompat()
    {
        $interference_candidates = ['Db', 'WPDb'];
        $allModules = $this->moduleContainer->all();
        foreach ($interference_candidates as $moduleName) {
            if (! $this->moduleContainer->hasModule($moduleName)) {
                continue;
            }
            /** @var Module $module */
            $module = $allModules[$moduleName];
            $cleanup_config = $module->_getConfig('cleanup');
            if (! empty($cleanup_config)) {
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
     * @return void
     */
    public function _loadWordpress()
    {
        $this->loadConfigFile();
        $this->defineConstants($this->_getConstants());

        if ($this->config['multisite']) {
            $this->debug('Running as multisite');
        } else {
            $this->debug('Running as single site');
        }

        require_once dirname(dirname(__DIR__)) . '/includes/functions.php';

        if (! empty($this->config['loadOnly'])) {
            $this->bootstrapWP();
        } else {
            $this->installAndBootstrapInstallation();
        }

        // Make the `factory` property available on the `$tester` property.
        $this->setupFactoryStore();

        if ($this->healthcheck instanceof WPHealthcheck && Debug::isEnabled()) {
            codecept_debug('WordPress status: ' . json_encode(
                $this->healthcheck->run(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ));
        }
    }

    /**
     * Defines the globals needed by WordPress to run to user set values.
     *
     * The method replaces the "wp-tests-config.php" file the original
     * testing workflow included to allow run-time customization of the
     * globals in a Codeception friendly way.
     *
     * @return array<string,mixed> The map of the defined constants.
     *
     * @throws ModuleConfigException If a `configFile` parameter is defined in the configuration, but cannot be found.
     */
    public function _getConstants()
    {
        $wpRootFolder = $this->getWpRootFolder();

        $constants = [
            // By default install WordPress in an isolated process.
            'WPCEPT_ISOLATED_INSTALL' => $this->requiresIsolatedInstallation(),
            'ABSPATH'                 => $wpRootFolder,
            'DB_NAME'                 => $this->config['dbName'],
            'DB_USER'                 => $this->config['dbUser'],
            'DB_PASSWORD'             => $this->config['dbPassword'],
            'DB_HOST'                 => $this->config['dbHost'],
            'DB_CHARSET'              => $this->config['dbCharset'],
            'DB_COLLATE'              => $this->config['dbCollate'],
            'WP_TESTS_TABLE_PREFIX'   => $this->config['tablePrefix'],
            'WP_TESTS_DOMAIN'         => $this->config['domain'],
            'WP_TESTS_EMAIL'          => $this->config['adminEmail'],
            'WP_TESTS_TITLE'          => $this->config['title'],
            'WP_PHP_BINARY'           => $this->config['phpBinary'],
            'WPLANG'                  => $this->config['language'],
            'WP_DEBUG'                => $this->config['wpDebug'],
            'WP_TESTS_MULTISITE'      => $this->config['multisite'],
        ];

        if (! defined('WP_PLUGIN_DIR') && ! empty($this->config['pluginsFolder'])) {
            $constants['WP_PLUGIN_DIR'] =  $this->getPluginsFolder();
        }

        if (! defined('WP_CONTENT_DIR') && ! empty($this->config['contentFolder'])) {
            $constants['WP_CONTENT_DIR'] =  $this->getContentFolder();
        }

        return $constants;
    }

    /**
     * Loads an extra configuration file, if specified in the user configuration.
     *
     * @param null|string $folder The directory to load configuration files from.
     *
     * @return void
     *
     * @throws ModuleConfigException If the specified configuration file cannot be found.
     */
    protected function loadConfigFile($folder = null)
    {
        foreach ($this->_getConfigFiles($folder) as $configFile) {
            require_once $configFile;
        }
    }

    /**
     * Returns whether the configuration requires an isolated installation or not.
     *
     * @return bool Whether the configuration requires an isolated installation or not.
     */
    protected function requiresIsolatedInstallation()
    {
        return isset($this->config['isolatedInstall']) ? $this->config['isolatedInstall'] : true;
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
    public function getPluginsFolder($path = '')
    {
        if (! empty($this->pluginDir)) {
            return empty($path) ? $this->pluginDir : $this->pluginDir . '/' . ltrim($path, '\\/');
        }

        if (defined('WP_PLUGIN_DIR')) {
            $candidate = WP_PLUGIN_DIR;
        } elseif (! empty($this->config['pluginsFolder'])) {
            $candidate = $this->config['pluginsFolder'];
        } else {
            $candidate = $this->getContentFolder('plugins');
        }

        try {
            $resolved = resolvePath($candidate, $this->getWpRootFolder());
            if ($resolved === false) {
                throw new \RuntimeException('Could not resolve path.');
            }
        } catch (\Exception $e) {
            throw new ModuleConfigException(
                __CLASS__,
                "The path to the plugins directory ('{$candidate}') doesn't exist."
            );
        }

        $this->pluginDir = (string)untrailslashit($resolved);

        return empty($path) ? $this->pluginDir : $this->pluginDir . '/' . ltrim($path, '\\/');
    }

    /**
     * Bootstraps the WordPress installation using the same steps taken by the Core PHPUnit test suite.
     *
     * @return void
     */
    protected function bootstrapWP()
    {
        $this->ensureServerVars();

        $this->setupLoadWatchers();
        include_once untrailslashit($this->wpRootFolder) . '/wp-load.php';
        $this->removeLoadWatchers();

        $this->setupCurrentSite();
    }

    /**
     * Sets up the required `$_SERVER` variables to ensure the WordPress installation will work correctly.
     *
     * @return void
     */
    protected function ensureServerVars()
    {
        $serverDefaults = [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_HOST'       => getenv('WP_DOMAIN') ? getenv('WP_DOMAIN') : $this->config['domain'],
        ];

        foreach ($serverDefaults as $key => $value) {
            if (empty($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Sets up the `current_site` global handling multisite and single site
     * installation cases.
     *
     * @return void
     */
    protected function setupCurrentSite()
    {
        /** @var wpdb $wpdb */
        global $current_site, $wpdb;

        $current_site = new \stdClass;

        if (! empty($wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}blogs'"))) {
            $query = "SELECT domain, path  FROM {$wpdb->prefix}blogs WHERE blog_id = 1 AND site_id = 1";
            $data = $wpdb->get_row($query);
            $current_site->domain = $data->domain;
            $current_site->path = $data->path;
            $current_site->site_name = ucfirst($data->domain);
        } else {
            $site_url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'siteurl'");
            if (! empty($site_url)) {
                $current_site->domain = parse_url($site_url, PHP_URL_HOST);
                if ($port = parse_url($site_url, PHP_URL_PORT)) {
                    $current_site->domain .= ":{$port}";
                }
            } else {
                $current_site->domain = $this->config['domain'];
            }
            $current_site->path = '/';
        }
        $current_site->site_name = ucfirst($current_site->domain);
        $current_site->id = 1;
    }

    /**
     * Installs and bootstraps the WordPress installation.
     *
     * @return void
     */
    protected function installAndBootstrapInstallation()
    {
        $this->setActivePlugins();
        $this->_setActiveTheme();

        if (! $this->requiresIsolatedInstallation()) {
            tests_add_filter('muplugins_loaded', [$this, '_loadPlugins']);
            tests_add_filter('wp_install', [$this, '_activatePlugins'], 100);
            tests_add_filter(
                'wp_install',
                [$this, '_bootstrapActions'],
                101
            );
            tests_add_filter('plugins_loaded', [$this, '_switchTheme']);
        }

        $installationConfiguration = $this->getInstallationConfiguration();

        require_once $this->wpBootstrapFile;

        if ($this->requiresIsolatedInstallation()) {
            $this->_bootstrapActions();
            $this->_switchTheme();
        }
    }

    /**
     * Sets the currently active plugins.
     *
     * @return void
     */
    protected function setActivePlugins()
    {
        if (empty($this->config['plugins'])) {
            return;
        }

        if (! empty($GLOBALS['wp_tests_options']['active_plugins'])) {
            $GLOBALS['wp_tests_options']['active_plugins'] = array_merge(
                $GLOBALS['wp_tests_options']['active_plugins'],
                $this->config['plugins']
            );
        } else {
            $GLOBALS['wp_tests_options']['active_plugins'] = $this->config['plugins'];
        }
    }

    /**
     * Sets the active template and stylesheet according to the `theme`
     * configuration parameter.
     *
     * @return void
     */
    public function _setActiveTheme()
    {
        if (empty($this->config['theme'])) {
            return;
        }

        if (! is_array($this->config['theme'])) {
            $template = $this->config['theme'];
            $stylesheet = $this->config['theme'];
        } else {
            $template = reset($this->config['theme']);
            $stylesheet = end($this->config['theme']);
        }

        $GLOBALS['wp_tests_options']['template'] = $template;
        $GLOBALS['wp_tests_options']['stylesheet'] = $stylesheet;

        codecept_debug(sprintf('Filtering option "pre_option_template" to return value "%s"', $template));
        codecept_debug(sprintf('Filtering option "pre_option_stylesheet" to return value "%s"', $stylesheet));
    }

    /**
     * Calls a list of user-defined actions needed in tests.
     *
     * @return void
     */
    public function _bootstrapActions()
    {
        if (empty($this->config['bootstrapActions'])) {
            return;
        }

        foreach ($this->config['bootstrapActions'] as $action) {
            if (! is_callable($action)) {
                do_action($action);
            } else {
                call_user_func($action);
            }
        }
    }

    /**
     * Switches the current theme.
     *
     * @return void
     */
    public function _switchTheme()
    {
        if (empty($this->config['theme'])) {
            return;
        }

        $stylesheet = is_array($this->config['theme']) ?
            end($this->config['theme'])
            : $this->config['theme'];
        $functionsFile = $this->wp->getWpContentDir() . '/themes/' . $stylesheet . '/functions.php';
        if (file_exists($functionsFile)) {
            require_once($functionsFile);
        }
        $this->wp->switch_theme($stylesheet);
        $this->wp->do_action('after_switch_theme', $stylesheet);
    }

    /**
     * Activates the set of specified plugins.
     *
     * @return void
     */
    public function _activatePlugins()
    {
        $currentUserIdBackup = get_current_user_id();

        wp_set_current_user(1);

        if (empty($this->config['activatePlugins'])) {
            return;
        }

        foreach ($this->config['activatePlugins'] as $plugin) {
            activate_plugin($plugin);
            update_option(
                'active_plugins',
                array_merge(get_option('active_plugins', []), [$plugin])
            );
        }

        wp_set_current_user($currentUserIdBackup);
    }

    /**
     * Loads the plugins required by the test.
     *
     * @return void
     */
    public function _loadPlugins()
    {
        if (empty($this->config['plugins']) || ! defined('WP_PLUGIN_DIR')) {
            return;
        }
        $pluginsPath = $this->getPluginsFolder() . DIRECTORY_SEPARATOR;
        $plugins = $this->config['plugins'];
        foreach ($plugins as $plugin) {
            $path = $pluginsPath . $plugin;
            if (! file_exists($path)) {
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
    public function factory()
    {
        return $this->factoryStore;
    }

    /**
     * Returns a closure to handle the exit of WordPress during the bootstrap process.
     *
     * @param  OutputInterface|null  $output  An output stream.
     *
     * @return void
     */
    public function _wordPressExitHandler(OutputInterface $output = null)
    {
        if ($this->wpDidLoadCorrectly || !$this->healthcheck instanceof WPHealthcheck) {
            return;
        }

        $output = $output ? $output : new ConsoleOutput();

        if (Debug::isEnabled()) {
            codecept_debug(
                'WordPress status: ' . json_encode(
                    $this->healthcheck->run(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                )
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
     * Returns the current redirect handler callback.
     *
     * @param string $location The redirect location.
     * @param int $status The redirection status code.
     *
     * @return string The redirect location.
     */
    public function _wordPressRedirectHandler($location, $status)
    {
        $this->loadRedirections [$location] = $status;

        codecept_debug(sprintf(
            'WordPress redirected to [%s] with status [%s] before exiting.',
            $location,
            $status
        ));

        return $location;
    }

    /**
     * Sets up the load watchers.
     *
     * @return void
     */
    protected function setupLoadWatchers()
    {
        register_shutdown_function([$this, '_wordPressExitHandler']);
        tests_add_filter('wp_redirect', [$this, '_wordPressRedirectHandler'], 0, 2);
        $this->loadRedirections = [];
    }

    /**
     * Removes the set load watchers.
     *
     * @return void
     */
    protected function removeLoadWatchers()
    {
        $this->wpDidLoadCorrectly = true;
        remove_filter('wp_redirect', [$this, '_wordPressRedirectHandler'], 0);
        $this->loadRedirections = [];
    }

    /**
     * Instantiates and sets up the factory store that will be available on the suite tester.
     *
     * @return void
     */
    protected function setupFactoryStore()
    {
        $this->factoryStore = new FactoryStore();
    }

    /**
     * Returns an array of the configuration files specified with the `configFile` parameter of the module configuarion.
     *
     * @param string|null $root   The start directory to search for configuration files. If not found in the starting
     *                            directory, then files will be searched in the directory parents.
     *
     * @return array<int,string|false> An array of configuration files absolute paths.
     *
     * @throws ModuleConfigException If a specified configuration file does not exist.
     */
    public function _getConfigFiles($root = null)
    {
        $root = $root ?: codecept_root_dir();

        $candidates  = $this->config['configFile'];
        $configFiles = [];

        foreach ((array)$candidates as $candidate) {
            if (! empty($candidate)) {
                $configFile = (string)findHereOrInParent($candidate, $root);
                if (! is_file($configFile)) {
                    throw new ModuleConfigException(
                        __CLASS__,
                        "\nConfig file `{$candidate}` could not be found in WordPress root folder or above."
                    );
                }
                $configFiles[] = $configFile;
            }
        }

        return array_unique($configFiles);
    }

    /**
     * Defines the constants required to set up the WordPress installation.
     *
     * @param array<string,int|string> $constants The map of the constants to define.
     *
     * @return void
     */
    protected function defineConstants(array $constants = [])
    {
        foreach ($constants as $key => $value) {
            if (! defined($key)) {
                define($key, $value);
            }
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
    public function getContentFolder($path = '')
    {
        if (! empty($this->contentDir)) {
            return empty($path) ? $this->contentDir : $this->contentDir . '/' . ltrim($path, '\\/');
        }

        if (defined('WP_CONTENT_DIR')) {
            $candidate = WP_CONTENT_DIR ;
        } elseif (!empty($this->config['contentFolder'])) {
            $candidate = $this->config['contentFolder'];
        } else {
            $candidate = $this->getWpRootFolder('wp-content');
        }

        try {
            $resolved = resolvePath($candidate, $this->getWpRootFolder());
            if ($resolved === false) {
                throw new \RuntimeException('Could not resolve path.');
            }
        } catch (\Exception $e) {
            throw new ModuleConfigException(
                __CLASS__,
                "The path to the content directory ('{$candidate}') doesn't exist or is not accessible."
            );
        }

        $this->contentDir = (string)untrailslashit($resolved);

        return empty($path) ? $this->contentDir : $this->contentDir . '/' . ltrim($path, '\\/');
    }

    /**
     * Returns the WordPress installation configuration as created from the currrent module configuration.
     *
     * @return Configuration The WordPress installation configuration, as created from the module configuration.
     */
    protected function getInstallationConfiguration()
    {
        return new Configuration([
            'tablesHandling' => isset($this->config['installationTableHandling']) ?
                $this->config['installationTableHandling']
                : 'empty'
        ]);
    }
}
