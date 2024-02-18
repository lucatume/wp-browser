<?php
/*
 * CLI-Server router.
 * Extracted from the `wp-cli` project: https://wp-cli.org/
 */

$root = $_SERVER['DOCUMENT_ROOT'];
$path = '/'. ltrim( parse_url( urldecode( $_SERVER['REQUEST_URI'] ),PHP_URL_PATH ), '/' );

define('DB_ENGINE', getenv('DB_ENGINE') ?: 'mysql');

if ( file_exists( $root.$path ) ) {

	// Enforces trailing slash, keeping links tidy in the admin
	if ( is_dir( $root.$path ) && substr_compare($path, '/', -strlen('/')) !== 0 ) {
		header( "Location: $path/" );
		exit;
	}

	// Runs PHP file if it exists
	if ( strpos($path, '.php') !== false ) {
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
