<?php
/*
 * CLI-Server router.
 * Extracted from the `wp-cli` project: https://wp-cli.org/
 */

$root = $_SERVER['DOCUMENT_ROOT'];
$path = '/'. ltrim( parse_url( urldecode( $_SERVER['REQUEST_URI'] ),PHP_URL_PATH ), '/' );

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
