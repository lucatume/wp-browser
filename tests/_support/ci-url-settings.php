<?php
/**
 * Sets the WordPress home and site URL constants explicitly to allow
 * the site to respond to requests from the Chrome Driver.
 */

if (filter_has_var(INPUT_SERVER, 'HTTP_HOST')) {
	if (!defined('WP_HOME')) {
		define('WP_HOME', 'http://' . \$_SERVER['HTTP_HOST']);
	}
	if (!defined('WP_SITEURL')) {
		define('WP_SITEURL', 'http://' . \$_SERVER['HTTP_HOST']);
	}
}
