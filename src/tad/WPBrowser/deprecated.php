<?php
/**
 * Deprecated functions.
 *
 * @package tad\WPBrowser
 */

if ( ! function_exists( 'rrmdir' ) ) {
	/**
	 * Recursively removes a directory and all its content.
	 *
	 * @see        tad\WPBrowser\rrmdir() for the replacement function.
	 * @deprecated Since 2.3; moved to the `\tad\WPBrowser` namespace.
	 *
	 * @param string $src The absolute path to the directory to remove.
	 *
	 */
	function rrmdir( $src ) {
		tad\WPBrowser\rrmdir( $src );
	}
}
