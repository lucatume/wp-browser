<?php
/*
 * CLI-Server router.
 * Extracted from the `wp-cli` project: https://wp-cli.org/
 */

$root = $_SERVER['DOCUMENT_ROOT'];
$path = '/'. ltrim( parse_url( urldecode( $_SERVER['REQUEST_URI'] ),PHP_URL_PATH ), '/' );

define('DB_ENGINE', getenv('DB_ENGINE') ?: 'mysql');

// Add a unique request ID to the headers.
$requestId             = md5( microtime() );
$_SERVER['REQUEST_ID'] = $requestId;
header( 'X-Request-ID: ' . $requestId );

// Disable the MU upgrade routine.
global $wp_filter;
$wp_filter['do_mu_upgrade'][10][] = [
    'accepted_args' => 0,
    'function'      => '__return_false'
];

if ( file_exists( $root.$path ) ) {

	// Enforces trailing slash, keeping links tidy in the admin
	if ( is_dir( $root.$path ) && ! str_ends_with( $path, '/' ) ) {
		header( "Location: $path/" );
		exit;
	}

	// Runs PHP file if it exists
	if ( str_contains( $path, '.php' ) ) {
		chdir( dirname( $root.$path ) );
		require_once $root.$path;
	} else {
		return false;
	}
} else {

	// Otherwise, run `index.php`
	chdir( $root );
	require_once 'index.php';
}
