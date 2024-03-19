<?php

class Airplane_Mode_Command extends WP_CLI_Command {

	/**
	 * Enables airplane mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp airplane-mode enable
	 *
	 * @when after_wp_load
	 * @subcommand on
	 * @alias enable
	 */
	function enable() {
		Airplane_Mode_Core::getInstance()->enable();
		WP_CLI::success( __( 'Airplane mode was enabled', 'airplane-mode' ) );
	}

	/**
	 * Disables airplane mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp airplane-mode disable
	 *
	 * @when after_wp_load
	 * @subcommand off
	 * @alias disable
	 */
	function disable() {
		Airplane_Mode_Core::getInstance()->disable();
		WP_CLI::success( __( 'Airplane mode was disabled', 'airplane-mode' ) );
	}

	/**
	 * Provides the status of airplane mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp airplane-mode status
	 *
	 * @when after_wp_load
	 */
	function status() {
		$on = 'on' === get_site_option( 'airplane-mode' );
		WP_CLI::success( $on ? __( 'Airplane mode is enabled', 'airplane-mode' ) : __( 'Airplane mode is disabled', 'airplane-mode' ) );
	}

	/**
	 * Purge the transients set from airplane mode.
	 *
	 * ## EXAMPLES
	 *
	 *     wp airplane-mode clean
	 *
	 * @when after_wp_load
	 */
	function clean() {
		Airplane_Mode_Core::getInstance()->purge_transients( true );
		WP_CLI::success( __( 'Transients have been cleared', 'airplane-mode' ) );
	}
}

WP_CLI::add_command( 'airplane-mode', 'Airplane_Mode_Command' );
