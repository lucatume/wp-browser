<?php

/**
 * Plugin Name: Multisite Plugin 1
 * Plugin URI: http://theAverageDev.com
 * Description: Multisite Plugin 1
 * Version: 1.0
 * Author: theAverageDev
 * Author URI: http://theAverageDev.com
 * License: GPL 2.0
 */
class MUPlugin1
{
	public static function activate()
	{
		if (is_multisite()) {
			update_network_option(null, 'muplugin1', 'mu-activated');
		} else {
			update_option('muplugin1', 'not-mu-activated');
		}
	}
}

register_activation_hook(__FILE__, [MUPlugin1::class, 'activate']);
