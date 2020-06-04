<?php
/**
 * Installs WordPress for running the tests and loads WordPress and the test libraries
 *
 * @var tad\WPBrowser\Utils\Configuration $installationConfiguration The current installation configuration.
 * @var bool $skipWordPressInstall Whether WordPress shoudl be installed by this script or not.
 */

use Codeception\Module\WPLoader;
use function tad\WPBrowser\vendorDir;

if (!function_exists('tad_functions')) {
	require_once __DIR__ . '/tad-functions.php';
}

// phpcs:ignore
extract( WPLoader::_maybeInit() );

/*
 * Globalize some WordPress variables, because PHPUnit loads this file inside a function.
 * See: https://github.com/sebastianbergmann/phpunit/issues/325
 */
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer;

define('DIR_TESTDATA', __DIR__ . '/../data');

if(!defined('WP_LANG_DIR')){
		define('WP_LANG_DIR', DIR_TESTDATA . '/languages');
}

if (!defined('WP_TESTS_FORCE_KNOWN_BUGS')) {
	define('WP_TESTS_FORCE_KNOWN_BUGS', false);
}

// Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only.
if(!defined('DISABLE_WP_CRON')){
		define('DISABLE_WP_CRON', true);
}

define('REST_TESTS_IMPOSSIBLY_HIGH_NUMBER', 99999999);

$memory_settings = [
	'WP_MEMORY_LIMIT' => -1,
	'WP_MAX_MEMORY_LIMIT' => -1,
];
foreach ($memory_settings as $memory_setting => $value) {
	if (!defined($memory_setting)) {
		define($memory_setting, $value);
	}
}

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$_SERVER['SERVER_NAME'] = WP_TESTS_DOMAIN;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

if ("1" == getenv('WP_MULTISITE') ||
	(defined('WP_TESTS_MULTISITE') && WP_TESTS_MULTISITE)
) {
	$multisite = true;
} else {
	$multisite = false;
}

// Override the PHPMailer
require_once dirname(__FILE__) . '/mock-mailer.php';
$phpmailer = new MockPHPMailer();

if (!defined('WP_DEFAULT_THEME')) {
	define('WP_DEFAULT_THEME', 'default');
}

$wp_theme_directories = array(DIR_TESTDATA . '/themedir1');

$table_prefix = WP_TESTS_TABLE_PREFIX;

if(empty($skipWordPressInstall)){
	// In place of executing the installation script require the (modified) installation file
	if (!defined('WPCEPT_ISOLATED_INSTALL') || false === WPCEPT_ISOLATED_INSTALL) {
		codecept_debug('Installing WordPress in same process...');
		require 'same-scope-install.php';
	} else {
		global $wp_tests_options;
		$wploaderInstallationFilters = empty($wp_tests_options['installation_filters'])
			? []
			: $wp_tests_options['installation_filters'];

		$environment = [
			'root' => codecept_root_dir(),
			'autoload' => vendorDir('autoload.php'),
			'installationFilters' => $wploaderInstallationFilters,
			'constants' => [
				'ABSPATH' => ABSPATH,
				'WP_DEBUG' => true,
				'WP_TESTS_TABLE_PREFIX' => WP_TESTS_TABLE_PREFIX,
				'DB_NAME' => DB_NAME,
				'DB_USER' => DB_USER,
				'DB_PASSWORD' => DB_PASSWORD,
				'DB_HOST' => DB_HOST,
				'DB_CHARSET' => DB_CHARSET,
				'DB_COLLATE' => DB_COLLATE,
				'WP_TESTS_DOMAIN' => WP_TESTS_DOMAIN,
				'WP_TESTS_EMAIL' => WP_TESTS_EMAIL,
				'WP_TESTS_TITLE' => WP_TESTS_TITLE,
				'WP_PHP_BINARY' => WP_PHP_BINARY,
				'WPLANG' => WPLANG,
			],
			'tablesHandling' => $installationConfiguration->get('tablesHandling','empty'),
		];

		$dirConstants = [
			'WP_PLUGIN_DIR',
			'WP_CONTENT_DIR',
			'WP_TEMP_DIR',
			'WPMU_PLUGIN_DIR',
			'WP_LANG_DIR',
		];
		foreach ($dirConstants as $const) {
			if (defined($const)) {
				$environment['constants'][$const] = constant($const);
			}
		}

		if (!empty($GLOBALS['wp_tests_options']['active_plugins'])) {
			$uniqueActivePlugins = array_unique($GLOBALS['wp_tests_options']['active_plugins']);
			$environment['activePlugins'] = $uniqueActivePlugins;
			codecept_debug("Active plugins:\n\t- " . implode("\n\t- ", $uniqueActivePlugins));
		}

		codecept_debug('Installing WordPress in isolated process...');
		ob_start();
		$isolatedInstallationScript = __DIR__ . '/isolated-install.php';
		system(implode(' ', [
			WP_PHP_BINARY,
			escapeshellarg($isolatedInstallationScript),
			escapeshellarg(base64_encode(serialize($environment))),
			$multisite,
		]));
		codecept_debug("Isolated installation script output: \n\n" . ob_get_clean());
	}
}

if ($multisite) {
	if (!defined('MULTISITE')) {
		define('MULTISITE', true);
	}
	if (!defined('SUBDOMAIN_INSTALL')) {
		define('SUBDOMAIN_INSTALL', false);
	}
	$GLOBALS['base'] = '/';
}
unset($multisite);

require_once __DIR__ . '/functions.php';

$GLOBALS['_wp_die_disabled'] = false;
// Allow tests to override wp_die
tests_add_filter('wp_die_handler', '_wp_die_handler_filter');

// Preset WordPress options defined in bootstrap file.
// Used to activate themes, plugins, as well as  other settings.
if (isset($GLOBALS['wp_tests_options'])) {
	function wp_tests_options($value) {
		$key = substr(current_filter(), strlen('pre_option_'));

		return $GLOBALS['wp_tests_options'][$key];
	}

	foreach (array_keys($GLOBALS['wp_tests_options']) as $key) {
		tests_add_filter('pre_option_' . $key, 'wp_tests_options');
	}
}

/*
 * Load WordPress: "untrailingslash" ABSPATH first of all to avoid double slashes in filepath,
 * while still working if ABSPATH did not include a trailing slash.
 */
require_once rtrim(ABSPATH, '/\\') . '/wp-settings.php';

// Delete any default posts & related data.
_without_filters('_delete_all_posts');

include_once __DIR__ . '/exceptions.php';
include_once __DIR__ . '/factory.php';
include_once __DIR__ . '/trac.php';
include_once(ABSPATH . 'wp-admin/includes/admin.php');
include_once(ABSPATH . WPINC . '/class-IXR.php');
include_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');
include_once __DIR__ . '/utils.php';

// let's make sure we are using a version of WordPress that integrates the WP_REST_Server class
if (class_exists('WP_REST_Server')) {
	require __DIR__ . '/spy-rest-server.php';
}
ob_start();
codecept_debug(ob_get_clean());
