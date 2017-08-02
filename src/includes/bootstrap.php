<?php
include_once dirname(dirname(dirname(__FILE__))) . '/shims.php';

/**
 * Installs WordPress for running the tests and loads WordPress and the test libraries
 */

if ( ! function_exists( 'tad_functions' ) ) {
	require_once dirname( __FILE__ ) . '/tad-functions.php';
}

/*
 * Globalize some WordPress variables, because PHPUnit loads this file inside a function
 * See: https://github.com/sebastianbergmann/phpunit/issues/325
 */
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp, $phpmailer;

define( 'DIR_TESTDATA', dirname( __FILE__ ) . '/../data' );
define( 'WP_LANG_DIR', DIR_TESTDATA . '/languages' );

if ( ! defined( 'WP_TESTS_FORCE_KNOWN_BUGS' ) ) {
	define( 'WP_TESTS_FORCE_KNOWN_BUGS', false );
}

// Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only
define( 'DISABLE_WP_CRON', true );

define( 'REST_TESTS_IMPOSSIBLY_HIGH_NUMBER', 99999999 );

$memory_settings = [
	'WP_MEMORY_LIMIT'     => - 1,
	'WP_MAX_MEMORY_LIMIT' => - 1
];
foreach ( $memory_settings as $memory_setting => $value ) {
	if ( ! defined( $memory_setting ) ) {
		define( $memory_setting, $value );
	}
}

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST']       = WP_TESTS_DOMAIN;
$_SERVER['SERVER_NAME']     = WP_TESTS_DOMAIN;
$_SERVER['REQUEST_METHOD']  = 'GET';
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';

$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

if ( "1" == getenv( 'WP_MULTISITE' ) ||
     ( defined( 'WP_TESTS_MULTISITE' ) && WP_TESTS_MULTISITE )
) {
	$multisite = true;
} else {
	$multisite = false;
}

// Override the PHPMailer
require_once( dirname( __FILE__ ) . '/mock-mailer.php' );
$phpmailer = new MockPHPMailer();

if ( ! defined( 'WP_DEFAULT_THEME' ) ) {
	define( 'WP_DEFAULT_THEME', 'default' );
}

$wp_theme_directories = array( DIR_TESTDATA . '/themedir1' );

$table_prefix = WP_TESTS_TABLE_PREFIX;

// in place of executing the installation script require the (modified) installation file
if ( ! defined( 'WPCEPT_ISOLATED_INSTALL' ) || false === WPCEPT_ISOLATED_INSTALL ) {
	codecept_debug( 'Installing WordPress in same process...' );
	require 'same-scope-install.php';
} else {
	$environment = [
		'root' => codecept_root_dir(),
		'autoload'  => wpbrowser_vendor_path( 'autoload.php' ),
		'constants' => [
			'ABSPATH'               => ABSPATH,
			'WP_DEBUG'              => true,
			'WP_TESTS_TABLE_PREFIX' => WP_TESTS_TABLE_PREFIX,
			'DB_NAME'               => DB_NAME,
			'DB_USER'               => DB_USER,
			'DB_PASSWORD'           => DB_PASSWORD,
			'DB_HOST'               => DB_HOST,
			'DB_CHARSET'            => DB_CHARSET,
			'DB_COLLATE'            => DB_COLLATE,
			'WP_TESTS_DOMAIN'       => WP_TESTS_DOMAIN,
			'WP_TESTS_EMAIL'        => WP_TESTS_EMAIL,
			'WP_TESTS_TITLE'        => WP_TESTS_TITLE,
			'WP_PHP_BINARY'         => WP_PHP_BINARY,
			'WPLANG'                => WPLANG
		]
	];

	$dirConstants = [
		'WP_PLUGIN_DIR',
		'WP_CONTENT_DIR',
		'WP_TEMP_DIR',
		'WPMU_PLUGIN_DIR',
		'WP_LANG_DIR',
	];
	foreach ( $dirConstants as $const ) {
		if ( defined( $const ) ) {
			$environment['constants'][ $const ] = constant( $const );
		}
	}

	if ( ! empty( $GLOBALS['wp_tests_options']['active_plugins'] ) ) {
		$uniqueActivePlugins          = array_unique( $GLOBALS['wp_tests_options']['active_plugins'] );
		$environment['activePlugins'] = $uniqueActivePlugins;
		codecept_debug( "Active plugins:\n\t- " . implode( "\n\t- ", $uniqueActivePlugins ) );
	}

	codecept_debug( 'Installing WordPress in isolated process...' );
	ob_start();
	$isolatedInstallationScript = dirname( __FILE__ ) . '/isolated-install.php';
	system( implode( ' ', [
		WP_PHP_BINARY,
		escapeshellarg( $isolatedInstallationScript ),
		escapeshellarg( serialize( $environment ) ),
		$multisite
	] ) );
	codecept_debug( "Isolated installation script output: \n\n" . ob_get_clean() );
}

if ( $multisite ) {
	if ( ! defined( 'MULTISITE' ) ) {
		define( 'MULTISITE', true );
	}
	if ( ! defined( 'SUBDOMAIN_INSTALL' ) ) {
		define( 'SUBDOMAIN_INSTALL', false );
	}
	$GLOBALS['base'] = '/';
}
unset( $multisite );

require_once dirname( __FILE__ ) . '/functions.php';

$GLOBALS['_wp_die_disabled'] = false;
// Allow tests to override wp_die
tests_add_filter( 'wp_die_handler', '_wp_die_handler_filter' );

// Preset WordPress options defined in bootstrap file.
// Used to activate themes, plugins, as well as  other settings.
if ( isset( $GLOBALS['wp_tests_options'] ) ) {
	function wp_tests_options( $value ) {
		$key = substr( current_filter(), strlen( 'pre_option_' ) );

		return $GLOBALS['wp_tests_options'][ $key ];
	}

	foreach ( array_keys( $GLOBALS['wp_tests_options'] ) as $key ) {
		tests_add_filter( 'pre_option_' . $key, 'wp_tests_options' );
	}
}

// Load WordPress: "untrailingslash" ABSPATH first of all to avoid double slashes in filepath,
// while still working if ABSPATH did not include a trailing slash
require_once rtrim( ABSPATH, '/\\' ) . '/wp-settings.php';

// Delete any default posts & related data
_without_filters( '_delete_all_posts' );

require dirname( __FILE__ ) . '/testcase.php';
require dirname( __FILE__ ) . '/testcase-rest-api.php';
require dirname( __FILE__ ) . '/testcase-xmlrpc.php';
require dirname( __FILE__ ) . '/testcase-ajax.php';
require dirname( __FILE__ ) . '/testcase-canonical.php';
require dirname( __FILE__ ) . '/exceptions.php';
require dirname( __FILE__ ) . '/utils.php';
// let's make sure we are using a version of WordPress that integrates the WP_REST_Server class
if ( class_exists( 'WP_REST_Server' ) ) {
	require dirname( __FILE__ ) . '/spy-rest-server.php';
}

/**
 * A child class of the PHP test runner.
 *
 * Used to access the protected longOptions property, to parse the arguments
 * passed to the script.
 *
 * If it is determined that phpunit was called with a --group that corresponds
 * to an @ticket annotation (such as `phpunit --group 12345` for bugs marked
 * as #WP12345), then it is assumed that known bugs should not be skipped.
 *
 * If WP_TESTS_FORCE_KNOWN_BUGS is already set in wp-tests-config.php, then
 * how you call phpunit has no effect.
 */
class WP_PHPUnit_Util_Getopt extends PHPUnit_Util_Getopt {
	protected $longOptions = array(
		'exclude-group=',
		'group=',
	);

	function __construct( $argv ) {
		array_shift( $argv );
		$options = array();
		while ( list( $i, $arg ) = each( $argv ) ) {
			try {
				if ( strlen( $arg ) > 1 && $arg[0] === '-' && $arg[1] === '-' ) {
					PHPUnit_Util_Getopt::parseLongOption( substr( $arg, 2 ), $this->longOptions, $options, $argv );
				}
			} catch ( PHPUnit_Framework_Exception $e ) {
				// Enforcing recognized arguments or correctly formed arguments is
				// not really the concern here.
				continue;
			}
		}

		$skipped_groups = array(
			'ajax'          => true,
			'ms-files'      => true,
			'external-http' => true,
		);

		foreach ( $options as $option ) {
			switch ( $option[0] ) {
				case '--exclude-group' :
					foreach ( $skipped_groups as $group_name => $skipped ) {
						$skipped_groups[ $group_name ] = false;
					}
					continue 2;
				case '--group' :
					$groups = explode( ',', $option[1] );
					foreach ( $groups as $group ) {
						if ( is_numeric( $group ) || preg_match( '/^(UT|Plugin)\d+$/', $group ) ) {
							WP_UnitTestCase::forceTicket( $group );
						}
					}

					foreach ( $skipped_groups as $group_name => $skipped ) {
						if ( in_array( $group_name, $groups ) ) {
							$skipped_groups[ $group_name ] = false;
						}
					}
					continue 2;
			}
		}

		$skipped_groups = array_filter( $skipped_groups );
		foreach ( $skipped_groups as $group_name => $skipped ) {
			echo sprintf( 'Not running %1$s tests. To execute these, use --group %1$s.', $group_name ) . PHP_EOL;
		}

		if ( ! isset( $skipped_groups['external-http'] ) ) {
			echo PHP_EOL;
			echo 'External HTTP skipped tests can be caused by timeouts.' . PHP_EOL;
			echo 'If this changeset includes changes to HTTP, make sure there are no timeouts.' . PHP_EOL;
			echo PHP_EOL;
		}
	}
}

ob_start();
new WP_PHPUnit_Util_Getopt( $_SERVER['argv'] );
codecept_debug( ob_get_clean() );
