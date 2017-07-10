<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleConflictException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Adapters\WP;
use tad\WPBrowser\Filesystem\Utils;
use tad\WPBrowser\Module\WPLoader\FactoryStore;

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
class WPLoader extends Module {

	public static $includeInheritedActions = true;

	public static $onlyActions = [];

	public static $excludeActions = [];

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
	 * @var array
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
	 * @var array
	 */
	protected $config
		= [
			'loadOnly'         => false,
			'isolatedInstall'  => true,
			'wpDebug'          => true,
			'multisite'        => false,
			'dbCharset'        => 'utf8',
			'dbCollate'        => '',
			'tablePrefix'      => 'wptests_',
			'domain'           => 'example.org',
			'adminEmail'       => 'admin@example.org',
			'title'            => 'Test Blog',
			'phpBinary'        => 'php',
			'language'         => '',
			'configFile'       => '',
			'pluginsFolder'    => '',
			'plugins'          => '',
			'activatePlugins'  => '',
			'bootstrapActions' => '',
			'theme'            => '',
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
	 * @var string The absolute path to the plugins folder
	 */
	protected $pluginsFolder;

	/**
	 * @var \tad\WPBrowser\Module\WPLoader\FactoryStore
	 */
	protected $factoryStore;

	/**
	 * @var WP
	 */
	private $wp;

	public function __construct(
		ModuleContainer $moduleContainer,
		$config,
		WP $wp = null
	) {
		parent::__construct($moduleContainer, $config);
		$this->wp = $wp ? $wp : new WP();
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
	public function _initialize() {
		$this->initialize();
	}

	protected function initialize() {
		if (empty($this->config['loadOnly'])) {
			// let's make sure *Db Module is either not running or properly configured if we have to run alongside it
			$this->ensureDbModuleCompat();
		}

		$this->ensureWPRoot($this->getWpRootFolder());

		// WordPress  will deal with database connection errors
		$this->wpBootstrapFile = dirname(dirname(__DIR__)) . '/includes/bootstrap.php';
		$this->loadWordPress();
	}

	protected function ensureDbModuleCompat() {
		$interference_candidates = ['Db', 'WPDb'];
		$allModules              = $this->moduleContainer->all();
		foreach ($interference_candidates as $moduleName) {
			if (!$this->moduleContainer->hasModule($moduleName)) {
				continue;
			}
			/** @var \Codeception\Module $module */
			$module         = $allModules[$moduleName];
			$cleanup_config = $module->_getConfig('cleanup');
			if (!empty($cleanup_config)) {
				throw new ModuleConflictException(__CLASS__,
					"{$moduleName}\nThe WP Loader module is being used together with the {$moduleName} module: the {$moduleName} module should have the 'cleanup' parameter set to 'false' not to interfere with the WP Loader module.");
			}
		}
	}

	/**
	 * @param string $wpRootFolder
	 *
	 * @throws \Codeception\Exception\ModuleConfigException If the specified
	 *                                                      WordPress root
	 *                                                      folder is not found
	 *                                                      or not valid.
	 */
	protected function ensureWPRoot($wpRootFolder) {
		if (!file_exists($wpRootFolder . DIRECTORY_SEPARATOR . 'wp-settings.php')) {
			throw new ModuleConfigException(__CLASS__,
				"\nThe path `{$wpRootFolder}` is not pointing to a valid WordPress installation folder.");
		}
	}

	/**
	 * @return string
	 */
	protected function getWpRootFolder() {
		if (empty($this->wpRootFolder)) {
			// allow me not to bother with traling slashes
			$wpRootFolder = Utils::untrailslashit($this->config['wpRootFolder']) . DIRECTORY_SEPARATOR;

			// maybe the user is using the `~` symbol for home?
			$this->wpRootFolder = Utils::homeify($wpRootFolder);
		}

		return $this->wpRootFolder;
	}

	/**
	 * Loads WordPress calling the bootstrap file
	 *
	 * This method does little but wrapping preparing the global space for the
	 * original automated testing bootstrap file and taking charge of replacing
	 * the original "wp-tests-config.php" file in setting up the globals.
	 *
	 * @return void
	 */
	protected function loadWordPress() {
		$this->defineGlobals();

		if ($this->config['multisite']) {
			$this->debug('Running as multisite');
		}
		else {
			$this->debug('Running as single site');
		}

		require_once dirname(dirname(__DIR__)) . '/includes/functions.php';

		if (!empty($this->config['loadOnly'])) {
			$this->bootstrapWP();
		}
		else {
			$this->installAndBootstrapInstallation();
		}
	}

	/**
	 * Defines the globals needed by WordPress to run to user set values.
	 *
	 * The method replaces the "wp-tests-config.php" file the original
	 * testing workflow included to allow run-time customization of the
	 * globals in a Codeception friendly way.
	 *
	 * @return void
	 */
	protected function defineGlobals() {
		$wpRootFolder = $this->getWpRootFolder();

		// load an extra config file if any
		$this->loadConfigFile();

		$constants = [
			// by default install WordPress in an isolated process
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

		foreach ($constants as $key => $value) {
			if (!defined($key)) {
				define($key, $value);
			}
		}

		if (!defined('WP_PLUGIN_DIR') && !empty($this->config['pluginsFolder'])) {
			define('WP_PLUGIN_DIR', $this->getPluginsFolder());
		}
	}

	/**
	 * @param string $folder = null The absolute path to the WordPress root
	 *                       installation folder.
	 *
	 * @throws ModuleConfigException
	 */
	protected function loadConfigFile($folder = null) {
		$folder = $folder ?: codecept_root_dir();
		$frags  = $this->config['configFile'];
		$frags  = is_array($frags) ?: [$frags];
		foreach ($frags as $frag) {
			if (!empty($frag)) {
				$configFile = Utils::findHereOrInParent($frag, $folder);
				if (!file_exists($configFile)) {
					throw new ModuleConfigException(__CLASS__,
						"\nConfig file `{$frag}` could not be found in WordPress root folder or above.");
				}
				require_once $configFile;
			}
		}
	}

	/**
	 * @return bool|mixed
	 */
	protected function requiresIsolatedInstallation() {
		return isset($this->config['isolatedInstall']) ? $this->config['isolatedInstall'] : true;
	}

	/**
	 * @return string
	 * @throws ModuleConfigException
	 */
	protected function getPluginsFolder() {
		if (empty($this->pluginsFolder)) {
			$path = empty($this->config['pluginsFolder']) ? WP_PLUGIN_DIR
				: realpath($this->getWpRootFolder() . Utils::unleadslashit($this->config['pluginsFolder']));

			if (!file_exists($path)) {
				throw new ModuleConfigException(__CLASS__,
					"The path to the plugins folder ('{$path}') doesn't exist.");
			}

			$this->pluginsFolder = Utils::untrailslashit($path);
		}

		return $this->pluginsFolder;
	}

	protected function bootstrapWP() {
		$this->ensureServerVars();

		include_once $this->wpRootFolder . '/wp-load.php';

		$this->setupCurrentSite();
		$this->factoryStore = new FactoryStore();
		$this->factoryStore->setupFactories();
	}

	/**
	 * Sets up the `current_site` global handling multisite and single site
	 * installation cases.
	 */
	protected function setupCurrentSite() {
		/** @var wpdb $wpdb */
		global $current_site, $wpdb;

		$current_site = new \stdClass;

		if (!empty($wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}blogs'"))) {
			$data                    = $wpdb->get_row("SELECT domain, path  FROM {$wpdb->prefix}blogs WHERE blog_id = 1 AND site_id = 1");
			$current_site->domain    = $data->domain;
			$current_site->path      = $data->path;
			$current_site->site_name = ucfirst($data->domain);
		}
		else {
			$site_url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'siteurl'");
			if (!empty($site_url)) {
				$current_site->domain = parse_url($site_url, PHP_URL_HOST);
				if ($port = parse_url($site_url, PHP_URL_PORT)) {
					$current_site->domain .= ":{$port}";
				}
			}
			else {
				$current_site->domain = $this->config['domain'];
			}
			$current_site->path = '/';
		}
		$current_site->site_name = ucfirst($current_site->domain);
		$current_site->id        = 1;
	}

	protected function installAndBootstrapInstallation() {
		$this->setActivePlugins();
		$this->_setActiveTheme();

		if (!$this->requiresIsolatedInstallation()) {
			tests_add_filter('muplugins_loaded', [$this, 'loadPlugins']);
			tests_add_filter('wp_install', [$this, 'activatePlugins'], 100);
			tests_add_filter('wp_install', [$this, 'bootstrapActions'],
				101);
			tests_add_filter('plugins_loaded', [$this, '_switch_theme']);
		}

		require_once $this->wpBootstrapFile;

		if ($this->requiresIsolatedInstallation()) {
			$this->bootstrapActions();
			$this->_switch_theme();
		}
	}

	protected function setActivePlugins() {
		if (empty($this->config['plugins'])) {
			return;
		}

		if (!empty($GLOBALS['wp_tests_options']['active_plugins'])) {
			$GLOBALS['wp_tests_options']['active_plugins'] = array_merge($GLOBALS['wp_tests_options']['active_plugins'],
				$this->config['plugins']);
		}
		else {
			$GLOBALS['wp_tests_options']['active_plugins'] = $this->config['plugins'];
		}
	}

	/**
	 * Sets the active template and stylesheet according to the `theme`
	 * configuration parameter.
	 */
	public function _setActiveTheme() {
		if (empty($this->config['theme'])) {
			return;
		}

		if (!is_array($this->config['theme'])) {
			$template   = $this->config['theme'];
			$stylesheet = $this->config['theme'];
		}
		else {
			$template   = reset($this->config['theme']);
			$stylesheet = end($this->config['theme']);
		}

		$GLOBALS['wp_tests_options']['template']   = $template;
		$GLOBALS['wp_tests_options']['stylesheet'] = $stylesheet;

		codecept_debug('Set template to [' . $template . '] and stylesheet to [' . $stylesheet . ']');
	}

	/**
	 * Calls a list of user-defined actions needed in tests.
	 */
	public function bootstrapActions() {
		if (empty($this->config['bootstrapActions'])) {
			return;
		}

		foreach ($this->config['bootstrapActions'] as $action) {
			if (!is_callable($action)) {
				do_action($action);
			}
			else {
				call_user_func($action);
			}
		}
	}

	public function _switch_theme() {
		if (!empty($this->config['theme'])) {
			$stylesheet    = is_array($this->config['theme']) ? end($this->config['theme']) : $this->config['theme'];
			$functionsFile = $this->wp->WP_CONTENT_DIR() . '/themes/' . $stylesheet . '/functions.php';
			if (file_exists($functionsFile)) {
				require_once($functionsFile);
			}
			call_user_func([$this->wp, 'switch_theme'], $stylesheet);
			$this->wp->do_action('after_switch_theme', $stylesheet);
		}
	}

	public function activatePlugins() {
		$currentUserIdBackup = get_current_user_id();

		wp_set_current_user(1);

		if (empty($this->config['activatePlugins'])) {
			return;
		}

		foreach ($this->config['activatePlugins'] as $plugin) {
			activate_plugin($plugin);
			update_option('active_plugins',
				array_merge(get_option('active_plugins', []), [$plugin]));
		}

		wp_set_current_user($currentUserIdBackup);
	}

	/**
	 * Loads the plugins required by the test.
	 */
	public function loadPlugins() {
		if (empty($this->config['plugins']) || !defined('WP_PLUGIN_DIR')) {
			return;
		}
		$pluginsPath = $this->getPluginsFolder() . DIRECTORY_SEPARATOR;
		$plugins     = $this->config['plugins'];
		foreach ($plugins as $plugin) {
			$path = $pluginsPath . $plugin;
			if (!file_exists($path)) {
				throw new ModuleConfigException(__CLASS__,
					"The '{$plugin}' plugin file was not found in the {$pluginsPath} directory; this might be due to a wrong configuration of the `wpRootFolder` setting or a missing inclusion of one ore more additional config files using the `configFile` setting.");
				continue;
			}
			require_once $path;
		}
	}

	/**
	 * Accessor method to get the object storing the factories for things.
	 *
	 * Example usage:
	 *
	 *        $postId = $I->factory()->post->create();
	 *
	 * @return \tad\WPBrowser\Module\WPLoader\FactoryStore
	 */
	public function factory() {
		return $this->factoryStore;
	}

	protected function ensureServerVars() {
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
}
