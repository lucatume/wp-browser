<?php
/**
 * Functions related to the manipulation and interaction with the filesystem.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Recursively removes a directory and all its content.
 *
 * @param string $src The absolute path to the directory to remove.
 */
function rrmdir( $src ) {
	if ( ! file_exists( $src ) ) {
		return;
	}

	$dir = opendir( $src );
	while ( false !== ( $file = readdir( $dir ) ) ) {
		if ( ( $file !== '.' ) && ( $file !== '..' ) ) {
			$full = $src . '/' . $file;
			if ( is_dir( $full ) ) {
				rrmdir( $full );
			} else {
				unlink( $full );
			}
		}
	}
	closedir( $dir );
	rmdir( $src );
}

