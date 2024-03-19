<?php
/**
 * Plugin Name: Airplane Mode
 * Plugin URI: https://github.com/norcross/airplane-mode
 * Description: Control loading of external files when developing locally
 * Author: Andrew Norcross
 * Author URI: http://andrewnorcross.com/
 * Version: 0.2.5
 * Text Domain: airplane-mode
 * Requires WP: 4.4
 * Domain Path: languages
 * GitHub Plugin URI: https://github.com/norcross/airplane-mode
 * @package airplane-mode
 */

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Andrew Norcross
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Set our base if not already defined.
if ( ! defined( 'AIRMDE_BASE' ) ) {
	define( 'AIRMDE_BASE', plugin_basename( __FILE__ ) );
}

// Set our directory if not already defined.
if ( ! defined( 'AIRMDE_DIR' ) ) {
	define( 'AIRMDE_DIR', plugin_dir_path( __FILE__ ) );
}

// Set our version if not already defined.
if ( ! defined( 'AIRMDE_VER' ) ) {
	define( 'AIRMDE_VER', '0.2.5' );
}

// Load our WP-CLI helper if that is defined and available.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/inc/wp-cli.php';
}

// Ensure the class has not already been loaded.
if ( ! class_exists( 'Airplane_Mode_Core' ) ) {

	/**
	 * Call our class.
	 */
	class Airplane_Mode_Core {

		/**
		 * Static property to hold our singleton instance.
		 *
		 * @var $instance
		 */
		static $instance = false;

		/**
		 * Set a var for the number of HTTP requests.
		 *
		 * @var $http_count
		 */
		private $http_count = 0;

		/**
		 * This is our constructor. There are many like it, but this one is mine.
		 */
		private function __construct() {
			add_action( 'plugins_loaded',                        array( $this, 'textdomain'              )           );
			add_action( 'style_loader_src',                      array( $this, 'block_style_load'        ),  100     );
			add_action( 'script_loader_src',                     array( $this, 'block_script_load'       ),  100     );
			add_action( 'admin_init',                            array( $this, 'remove_update_crons'     )           );
			add_action( 'admin_init',                            array( $this, 'remove_schedule_hook'    )           );

			add_filter( 'embed_oembed_html',                     array( $this, 'block_oembed_html'       ),  1,  4   );
			add_filter( 'get_avatar',                            array( $this, 'replace_gravatar'        ),  1,  5   );
			add_filter( 'map_meta_cap',                          array( $this, 'prevent_auto_updates'    ),  10, 2   );
			add_filter( 'default_avatar_select',                 array( $this, 'default_avatar'          )           );

			// Kill all the http requests.
			add_filter( 'pre_http_request',                      array( $this, 'disable_http_reqs'       ),  10, 3   );

			// Check for our query string and handle accordingly.
			add_action( 'init',                                  array( $this, 'toggle_check'            )           );

			// Check for status change and purge transients as needed.
			add_action( 'airplane_mode_status_change',           array( $this, 'purge_transients'        )           );

			// Add our counter action.
			add_action( 'airplane_mode_http_args',               array( $this, 'count_http_requests'     ),  0, 0    );

			// CSS loader and top toggle.
			add_action( 'admin_bar_menu',                        array( $this, 'admin_bar_toggle'        ),  9999    );
			add_action( 'wp_enqueue_scripts',                    array( $this, 'toggle_css'              ),  9999    );
			add_action( 'admin_enqueue_scripts',                 array( $this, 'toggle_css'              ),  9999    );

			// Body class on each location for the display.
			add_filter( 'body_class',                            array( $this, 'body_class'              )           );
			add_filter( 'login_body_class',                      array( $this, 'body_class'              )           );
			add_filter( 'admin_body_class',                      array( $this, 'admin_body_class'        )           );

			// Remove bulk action for updating themes/plugins.
			add_filter( 'bulk_actions-plugins',                  array( $this, 'remove_bulk_actions'     )           );
			add_filter( 'bulk_actions-themes',                   array( $this, 'remove_bulk_actions'     )           );
			add_filter( 'bulk_actions-plugins-network',          array( $this, 'remove_bulk_actions'     )           );
			add_filter( 'bulk_actions-themes-network',           array( $this, 'remove_bulk_actions'     )           );

			// Admin UI items.
			add_action( 'admin_menu',                            array( $this, 'admin_menu_items'        ),  9999    );
			add_action( 'network_admin_menu',                    array( $this, 'ms_admin_menu_items'     ),  9999    );
			add_filter( 'install_plugins_tabs',                  array( $this, 'plugin_add_tabs'         )           );

			// Theme update API for different calls.
			add_filter( 'themes_api',                            array( $this, 'bypass_theme_api_call'   ),  10, 3   );
			add_filter( 'themes_api_result',                     array( $this, 'bypass_theme_api_result' ),  10, 3   );

			// Time based transient checks.
			add_filter( 'pre_site_transient_update_themes',      array( $this, 'last_checked_themes'     )           );
			add_filter( 'pre_site_transient_update_plugins',     array( $this, 'last_checked_plugins'    )           );
			add_filter( 'pre_site_transient_update_core',        array( $this, 'last_checked_core'       )           );
			add_filter( 'site_transient_update_themes',          array( $this, 'remove_update_array'     )           );
			add_filter( 'site_transient_update_plugins',         array( $this, 'remove_update_array'     )           );

			// Disable fetching languages from online
			add_filter( 'site_transient_available_translations', array( $this, 'available_translations' ), 9999, 1  );

			// Use our own filters for scripts and stylesheets to allow local.
			add_filter( 'airplane_mode_parse_style',             array( $this, 'bypass_asset_block'     ),  10, 2   );
			add_filter( 'airplane_mode_parse_script',            array( $this, 'bypass_asset_block'     ),  10, 2   );

			// Our activation / deactivation triggers.
			register_activation_hook( __FILE__,                  array( $this, 'create_setting'          )           );
			register_deactivation_hook( __FILE__,                array( $this, 'remove_setting'          )           );

			// All our various filter checks.
			if ( $this->enabled() ) {

				// Allows locally defined JETPACK_DEV_DEBUG constant to override filter.
				if ( ! defined( 'JETPACK_DEV_DEBUG' ) ) {
					// Keep jetpack from attempting external requests.
					add_filter( 'jetpack_development_mode',         '__return_true', 9999 );
				}

				// Prevent BuddyPress from falling back to Gravatar avatars.
				add_filter( 'bp_core_fetch_avatar_no_grav',         '__return_true' );

				// Disable automatic updater updates.
				add_filter( 'automatic_updater_disabled',           '__return_true' );

				// Tell WordPress we are on a version control system to add additional blocks.
				add_filter( 'automatic_updates_is_vcs_checkout',    '__return_true' );

				// Disable translation updates.
				add_filter( 'auto_update_translation',              '__return_false' );

				// Disable minor core updates.
				add_filter( 'allow_minor_auto_core_updates',        '__return_false' );

				// Disable major core updates.
				add_filter( 'allow_major_auto_core_updates',        '__return_false' );

				// Disable dev core updates.
				add_filter( 'allow_dev_auto_core_updates',          '__return_false' );

				// Disable overall core updates.
				add_filter( 'auto_update_core',                     '__return_false' );
				add_filter( 'wp_auto_update_core',                  '__return_false' );

				// Disable automatic plugin and theme updates (used by WP to force push security fixes).
				add_filter( 'auto_update_plugin',                   '__return_false' );
				add_filter( 'auto_update_theme',                    '__return_false' );

				// Disable debug emails (used by core for rollback alerts in automatic update deployment).
				add_filter( 'automatic_updates_send_debug_email',   '__return_false' );

				// Disable update emails (for when we push the new WordPress versions manually) as well
				// as the notification there is a new version emails.
				add_filter( 'auto_core_update_send_email',          '__return_false' );
				add_filter( 'send_core_update_notification_email',  '__return_false' );
				add_filter( 'automatic_updates_send_debug_email ',  '__return_false', 1 );

				// Get rid of the version number in the footer.
				add_filter( 'update_footer',                        '__return_empty_string', 11 );

				// Filter out the pre core option.
				add_filter( 'pre_option_update_core',               '__return_null' );

				// Remove some actions.
				remove_action( 'admin_init',            'wp_plugin_update_rows' );
				remove_action( 'admin_init',            'wp_theme_update_rows' );
				remove_action( 'admin_notices',         'maintenance_nag' );
				remove_action( 'init',                  'wp_schedule_update_checks' );

				// Add back the upload tab.
				add_action( 'install_themes_upload',    'install_themes_upload', 10, 0 );

				// Define core contants for more protection.
				if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) {
					define( 'AUTOMATIC_UPDATER_DISABLED', true );
				}
				if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) ) {
					define( 'WP_AUTO_UPDATE_CORE', false );
				}
			}
		}

		/**
		 * If an instance exists, this returns it.  If not, it creates one and
		 * returns it.
		 *
		 * @return $instance
		 */
		public static function getInstance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Load our textdomain for localization.
		 *
		 * @return void
		 */
		public function textdomain() {
			load_plugin_textdomain( 'airplane-mode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Set our initial airplane mode setting to 'on' on activation.
		 */
		public function create_setting() {
			add_site_option( 'airplane-mode', 'on' );
			set_transient( 'wporg_theme_feature_list', array(), 999999999999 );
		}

		/**
		 * Remove our setting on plugin deactivation.
		 */
		public function remove_setting() {
			delete_option( 'airplane-mode' );
			delete_site_option( 'airplane-mode' );
			delete_transient( 'wporg_theme_feature_list' );
		}

		/**
		 * Helper function to check the current status.
		 *
		 * @return bool True if status is 'on'; false if not.
		 */
		public function enabled() {

			// Bail if CLI.
			if ( defined( 'WP_CLI' ) and WP_CLI ) {
				return false;
			}

			// Pull our status from the options table.
			$option = get_site_option( 'airplane-mode' );

			// Backup check for regular options table.
			if ( false === $option ) {
				$option = get_option( 'airplane-mode' );
			}

			// Return the option flag.
			return 'on' === $option;
		}

		/**
		 * Check the URL of a stylesheet and remove any that are not on the local URL.
		 *
		 * @param  string $source  The source URL of the CSS sheet.
		 *
		 * @return string $source  The same URL, or null.
		 */
		public function block_style_load( $source ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $source;
			}

			// Parse the URL being passed to pull out the host.
			$parsed = parse_url( $source, PHP_URL_HOST );

			// First run the filter to allow a source host to get through.
			if ( false === apply_filters( 'airplane_mode_parse_style', true, $parsed ) ) {
				return $source;
			}

			// If we don't share the same URL as the site itself, return null. Otherwise return the URL.
			return isset( $parsed ) && false === strpos( home_url(), $parsed )
				? new Airplane_Mode_WP_Error( 'airplane_mode_enabled', __( 'Airplane Mode blocked style', 'airplane-mode' ), array(
					'return' => '',
					'src'    => $source,
				) )
				: $source;
		}

		/**
		 * Check the URL of a JS file and remove any that are not on the local URL.
		 *
		 * @param  string $source  The source URL of the JS file.
		 *
		 * @return string $source  The same URL, or null.
		 */
		public function block_script_load( $source ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $source;
			}

			// Parse the URL being passed to pull out the host.
			$parsed = parse_url( $source, PHP_URL_HOST );

			// First run the filter to allow a source host to get through.
			if ( false === apply_filters( 'airplane_mode_parse_script', true, $parsed ) ) {
				return $source;
			}

			// If we don't share the same URL as the site itself, return null. Otherwise return the URL.
			return isset( $parsed ) && false === strpos( home_url(), $parsed )
				? new Airplane_Mode_WP_Error( 'airplane_mode_enabled', __( 'Airplane Mode blocked script', 'airplane-mode' ), array(
					'return' => '',
					'src'    => $source,
				) )
				: $source;
		}

		/**
		 * Use our existing filter to check for local assets.
		 *
		 * @param  boolean $block   Whether to block the specific asset. Defaults to 'true'.
		 * @param  array   $parsed  The URL of the asset, parsed.
		 *
		 * @return boolean
		 */
		public function bypass_asset_block( $block, $parsed ) {

			// Create an array of the approved local domains.
			$local  = apply_filters( 'airplane_mode_local_hosts', array( 'localhost', '127.0.0.1' ) );

			// If our parsed URL host is in that array, return false. Otherwise, return our blocking choice.
			return ! empty( $local ) && in_array( $parsed, $local ) ? false : $block;
		}

		/**
		 * Block oEmbeds from displaying.
		 *
		 * @param string $html The embed HTML.
		 * @param string $url The attempted embed URL.
		 * @param array  $attr An array of shortcode attributes.
		 * @param int    $post_ID Post ID.
		 *
		 * @return string
		 */
		public function block_oembed_html( $html, $url, $attr, $post_ID ) {
			return $this->enabled() ? sprintf( '<div class="loading-placeholder airplane-mode-placeholder"><p>%s</p></div>', sprintf( __( 'Airplane Mode is enabled. oEmbed blocked for %1$s.', 'airplane-mode' ), esc_url( $url ) ) ) : $html;
		}

		/**
		 * Add body class to front-end pages and login based on plugin status.
		 *
		 * @param  array $classes  The existing array of body classes.
		 *
		 * @return array $classes  The updated array of body classes.
		 */
		public function body_class( $classes ) {

			// Add the class based on the current status.
			$classes[]  = $this->enabled() ? 'airplane-mode-enabled' : 'airplane-mode-disabled';

			// Also add in the margin setup for Query Monitor because I'm a perfectionist.
			if ( ! class_exists( 'QueryMonitor' ) || defined( 'QM_DISABLED' ) && QM_DISABLED ) {
				$classes[]  = 'airplane-mode-no-qm';
			}

			// Return our array of classes.
			return $classes;
		}

		/**
		 * Add body class to admin pages based on plugin status.
		 *
		 * @param  string $classes  The existing space-separated list of CSS classes.
		 *
		 * @return string $classes  The updated space-separated list of CSS classes.
		 */
		public function admin_body_class( $classes ) {

			// First add the standard set of classes based on status.
			$classes .= $this->enabled() ? ' airplane-mode-enabled' : ' airplane-mode-disabled';

			// Also add in the margin setup for Query Monitor because I'm a perfectionist.
			if ( ! class_exists( 'QueryMonitor' ) || defined( 'QM_DISABLED' ) && QM_DISABLED ) {
				$classes .= ' airplane-mode-no-qm';
			}

			// Return our string of classes.
			return $classes;
		}

		/**
		 * Remove menu items for updates from a standard WP install.
		 *
		 * @return null
		 */
		public function admin_menu_items() {

			// Bail if disabled, or on a multisite.
			if ( ! $this->enabled() || is_multisite() ) {
				return;
			}

			// Remove our items.
			remove_submenu_page( 'index.php', 'update-core.php' );
		}

		/**
		 * Remove menu items for updates from a multisite instance.
		 *
		 * @return null
		 */
		public function ms_admin_menu_items() {

			// Bail if disabled or not on our network admin.
			if ( ! $this->enabled() || ! is_network_admin() ) {
				return;
			}

			// Remove the items.
			remove_submenu_page( 'index.php', 'upgrade.php' );
		}

		/**
		 * Replace all instances of gravatar with a local image file
		 * to remove the call to remote service.
		 *
		 * @param string            $avatar       Image tag for the user's avatar.
		 * @param int|object|string $id_or_email  A user ID, email address, or comment object.
		 * @param int               $size         Square avatar width and height in pixels to retrieve.
		 * @param string            $default      URL to a default image to use if no avatar is available.
		 * @param string            $alt          Alternative text to use in the avatar image tag.
		 *
		 * @return string `<img>` tag for the user's avatar.
		 */
		public function replace_gravatar( $avatar, $id_or_email, $size, $default, $alt ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $avatar;
			}

			// Swap out the file for a base64 encoded image.
			$image  = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
			$avatar = "<img alt='{$alt}' src='{$image}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' style='background:#eee;' />";

			// Return the avatar.
			return $avatar;
		}

		/**
		 * Remove avatar images from the default avatar list.
		 *
		 * @param  string $avatar_list  List of default avatars.
		 *
		 * @return string               Updated list with images removed.
		 */
		public function default_avatar( $avatar_list ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $avatar_list;
			}

			// Remove images.
			$avatar_list = preg_replace( '|<img([^>]+)> |i', '', $avatar_list );

			// Send back the list.
			return $avatar_list;
		}

		/**
		 * Disable all the HTTP requests being made with the action
		 * happening before the status check so others can allow certain
		 * items as desired.
		 *
		 * @param  bool|array|WP_Error $status  Whether to preempt an HTTP request return. Default false.
		 * @param  array               $args    HTTP request arguments.
		 * @param  string              $url     The request URL.
		 *
		 * @return bool|array|WP_Error          A WP_Error object if Airplane Mode is enabled. Original $status if not.
		 */
		public function disable_http_reqs( $status = false, $args = array(), $url = '' ) {

			// Pass our data to the action to allow a bypass.
			do_action( 'airplane_mode_http_args', $status, $args, $url );

			if ( ! $this->enabled() ) {
				return $status;
			}

			$url_host = parse_url( $url, PHP_URL_HOST );

			// Allow the request to pass through if the URL host matches the site's host.
			if ( $url_host && parse_url( home_url(), PHP_URL_HOST ) === $url_host ) {

				// But allow this to be disabled via a filter.
				if ( apply_filters( 'airplane_mode_allow_local_bypass', true, $url, $args ) ) {
					return $status;
				}
			}

			// Allow certain HTTP API requests to pass through via a filter.
			if ( apply_filters( 'airplane_mode_allow_http_api_request', false, $url, $args, $url_host ) ) {
				return $status;
			}

			// Disable the http requests if enabled.
			return new WP_Error( 'airplane_mode_enabled', __( 'Airplane Mode is enabled', 'airplane-mode' ) );
		}

		/**
		 * Load our small CSS file for the toggle switch.
		 */
		public function toggle_css() {

			// Don't display CSS on the front-end if the admin bar is not loading.
			if ( ! is_admin() && ! is_admin_bar_showing() ) {
				return;
			}

			// Set a suffix for loading the minified or normal.
			$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'airplane-mode.css' : 'airplane-mode.min.css';

			// Set a version for browser caching.
			$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : AIRMDE_VER;

			// Load the CSS file itself.
			wp_enqueue_style( 'airplane-mode', plugins_url( '/lib/css/' . $file, __FILE__ ), array(), $vers, 'all' );
		}

		/**
		 * Sets the mode.
		 *
		 * @param  string $mode Desired mode ('on' or 'off').
		 *
		 * @return bool Whether the setting changed.
		 */
		public function set_mode( $mode = 'on' ) {

			// Check what mode we're currently in, with "on" as a fallback.
			if ( ! in_array( $mode, array( 'on', 'off' ) ) ) {
				$mode = 'on';
			}

			// Update the setting.
			$return = update_site_option( 'airplane-mode', $mode );

			// Fire action to allow for functions to run on status change.
			do_action( 'airplane_mode_status_change', $mode );

			// And return the status we just set.
			return $return;
		}

		/**
		 * Enables airplane mode.
		 *
		 * @return bool Whether the setting changed.
		 */
		public function enable() {
			return self::set_mode( 'on' );
		}

		/**
		 * Disables airplane mode.
		 *
		 * @return bool Whether the setting changed.
		 */
		public function disable() {
			return self::set_mode( 'off' );
		}

		/**
		 * Check the user action from the toggle switch to set the option
		 * to 'on' or 'off'.
		 *
		 * @return void If any of the sanity checks fail and we bail early.
		 */
		public function toggle_check() {

			// Bail if current user doesn't have cap.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Set a sanitized variable of our potential nonce and request.
			$nonce  = isset( $_GET['airmde_nonce'] ) ? sanitize_key( $_GET['airmde_nonce'] ) : '';
			$switch = isset( $_REQUEST['airplane-mode'] ) ? sanitize_key( $_REQUEST['airplane-mode'] ) : '';

			// Check for our nonce.
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'airmde_nonce' ) ) {
				return;
			}

			// Now check for our query string.
			if ( empty( $switch ) || ! in_array( $switch, array( 'on', 'off' ) ) ) {
				return;
			}

			// Delete old per-site option.
			delete_option( 'airplane-mode' );

			// Set our mode based on the toggle action.
			self::set_mode( $switch );

			// And go about our business.
			wp_redirect( self::get_redirect() );
			exit;
		}

		/**
		 * Fetch the URL to redirect to after toggling Airplane Mode.
		 *
		 * @return string The URL to redirect to.
		 */
		protected static function get_redirect() {

			// Return the args for the actual redirect.
			$redirect = remove_query_arg( array(
				'airplane-mode',
				'airmde_nonce',
				'user_switched',
				'switched_off',
				'switched_back',
				'message',
				'update',
				'updated',
				'settings-updated',
				'saved',
				'activated',
				'activate',
				'deactivate',
				'enabled',
				'disabled',
				'locked',
				'skipped',
				'deleted',
				'trashed',
				'untrashed',
				'force-check',
			) );

			// Redirect away from the update core page.
			$redirect = str_replace( 'update-core.php', '', $redirect );

			// And return the redirect.
			return apply_filters( 'airplane_mode_redirect_url', $redirect );
		}

		/**
		 * Add our quick toggle to the admin bar to enable / disable.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
		 *
		 * @return void if current user can't manage options and we bail early.
		 */
		public function admin_bar_toggle( WP_Admin_Bar $wp_admin_bar ) {

			// Bail if current user doesn't have cap.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Get the current status.
			$status = $this->enabled();

			// Set a title message (translatable).
			$title  = $status ? __( 'Airplane Mode is enabled.', 'airplane-mode' ) : __( 'Airplane Mode is disabled.', 'airplane-mode' );

			// Set our toggle variable parameter (in reverse since we want the opposite action).
			$toggle = $status ? 'off' : 'on';

			// Set my HTTP request count label to a blank string for now.
			$label = '';

			// Get and display the HTTP count when Query Monitor isn't active.
			if ( ! class_exists( 'QueryMonitor' ) || defined( 'QM_DISABLED' ) && QM_DISABLED ) {

				// Pull my HTTP count.
				$count  = ! empty( $this->http_count ) ? number_format_i18n( $this->http_count ) : 0;

				$count_label = sprintf( _n( 'There was %s HTTP request.', 'There were %s HTTP requests.', $count, 'airplane-mode' ), $count );

				// Build the markup for my label.
				$label = '<span class="ab-label" aria-hidden="true">' . absint( $count ) . '</span>';
				$label .= '<span class="screen-reader-text">' . esc_html( $count_label ) . '</span>';

				// Amend the tooltip title with the count.
				$title .= '&nbsp;' . $count_label;
			}

			// Get our link with the status parameter.
			$link = wp_nonce_url( add_query_arg( 'airplane-mode', $toggle ), 'airmde_nonce', 'airmde_nonce' );

			// Now add the admin bar link.
			$wp_admin_bar->add_node(
				array(
					'id'        => 'airplane-mode-toggle',
					'title'     => '<span class="ab-icon"></span>' . $label,
					'href'      => esc_url( $link ),
					'position'  => 0,
					'meta'      => array(
						'title' => $title,
					),
				)
			);
		}

		/**
		 * Filter a user's meta capabilities to prevent auto-updates from being attempted.
		 *
		 * @param array  $caps    Returns the user's actual capabilities.
		 * @param string $cap     Capability name.
		 *
		 * @return array The user's filtered capabilities.
		 */
		public function prevent_auto_updates( $caps, $cap ) {

			// Check for being enabled and look for specific cap requirements.
			if ( $this->enabled() && in_array( $cap, array( 'update_plugins', 'update_themes', 'update_core' ) ) ) {
				$caps[] = 'do_not_allow';
			}

			// Send back the data array.
			return $caps;
		}

		/**
		 * Check the new status after airplane mode has been enabled or
		 * disabled and purge related transients.
		 *
		 * @param boolean $force  Whether to force the purge.
		 *
		 * @return void
		 */
		public function purge_transients( $force = false ) {

			// First check for the filter to avoid this action overall.
			if ( empty( $force ) && false === $clear = apply_filters( 'airplane_mode_purge_transients', true ) ) {
				return;
			}

			// Purge the transients related to updates when disabled or the force is called.
			if ( ! $this->enabled() || ! empty( $force ) ) {
				delete_site_transient( 'update_core' );
				delete_site_transient( 'update_plugins' );
				delete_site_transient( 'update_themes' );
				delete_site_transient( 'wporg_theme_feature_list' );
			}
		}

		/**
		 * Remove all the various places WP does the update checks. As you can see there are a lot of them.
		 *
		 * @return null
		 */
		public function remove_update_crons() {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return;
			}

			// Do a quick check to make sure we can remove things.
			if ( ! function_exists( 'remove_action' ) ) {
				return;
			}

			// Disable Theme Updates.
			remove_action( 'load-update-core.php', 'wp_update_themes' );
			remove_action( 'load-themes.php', 'wp_update_themes' );
			remove_action( 'load-update.php', 'wp_update_themes' );
			remove_action( 'wp_update_themes', 'wp_update_themes' );
			remove_action( 'admin_init', '_maybe_update_themes' );

			// Disable Plugin Updates.
			remove_action( 'load-update-core.php', 'wp_update_plugins' );
			remove_action( 'load-plugins.php', 'wp_update_plugins' );
			remove_action( 'load-update.php', 'wp_update_plugins' );
			remove_action( 'wp_update_plugins', 'wp_update_plugins' );
			remove_action( 'admin_init', '_maybe_update_plugins' );

			// Disable Core updates

			// Don't look for WordPress updates. Seriously!
			remove_action( 'wp_version_check', 'wp_version_check' );
			remove_action( 'admin_init', '_maybe_update_core' );

			// Not even maybe.
			remove_action( 'wp_maybe_auto_update', 'wp_maybe_auto_update' );
			remove_action( 'admin_init', 'wp_maybe_auto_update' );
			remove_action( 'admin_init', 'wp_auto_update_core' );

			// Don't forget when the language packs do it.
			remove_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
			remove_action( 'upgrader_process_complete', 'wp_version_check' );
			remove_action( 'upgrader_process_complete', 'wp_update_plugins' );
			remove_action( 'upgrader_process_complete', 'wp_update_themes' );
		}

		/**
		 * Remove all the various schedule hooks for themes, plugins, etc.
		 *
		 * @return null
		 */
		public function remove_schedule_hook() {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return;
			}

			// Clear all my hooks.
			wp_clear_scheduled_hook( 'wp_update_themes' );
			wp_clear_scheduled_hook( 'wp_update_plugins' );
			wp_clear_scheduled_hook( 'wp_version_check' );
			wp_clear_scheduled_hook( 'wp_maybe_auto_update' );
		}

		/**
		 * Override the API call made for pulling themes from the .org repo.
		 *
		 * @param false|object|array $override  Whether to override the WordPress.org Themes API. Default false.
		 * @param string             $action    Requested action. Likely values are 'theme_information',
		 *                                      'feature_list', or 'query_themes'.
		 * @param object             $args      Arguments used to query for installer pages from the Themes API.
		 *
		 * @return bool                         True if enabled, otherwise the existng value.
		 */
		public function bypass_theme_api_call( $override, $action, $args ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $override;
			}

			// Return false on feature list to avoid the API call.
			return ! empty( $action ) && 'feature_list' === $action ? true : $override;
		}

		/**
		 * Hijack the expected themes API result.
		 *
		 * @param array|object|WP_Error $res     WordPress.org Themes API response.
		 * @param string                $action  Requested action. Likely values are 'theme_information',
		 *                                       'feature_list', or 'query_themes'.
		 * @param object                $args    Arguments used to query for installer pages from the WordPress.org Themes API.
		 *
		 * @return bool                          An empty array if enabled, otherwise the existng result.
		 */
		public function bypass_theme_api_result( $res, $action, $args ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $res;
			}

			// Return false on feature list to avoid the API call.
			return ! empty( $action ) && in_array( $action, array( 'feature_list', 'query_themes' ) ) ? array() : $res;
		}

		/**
		 * Always send back that the latest version of WordPress is the one we're running
		 *
		 * @return object     the modified output with our information
		 */
		public function last_checked_core() {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return false;
			}

			// Call the global WP version.
			global $wp_version;

			// Return our object.
			return (object) array(
				'last_checked'      => time(),
				'updates'           => array(),
				'version_checked'   => $wp_version,
			);
		}

		/**
		 * Always send back that the latest version of our theme is the one we're running
		 *
		 * @return object     the modified output with our information
		 */
		public function last_checked_themes() {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return false;
			}

			// Call the global WP version.
			global $wp_version;

			// Set a blank data array.
			$data = array();

			// Build my theme data array.
			foreach ( wp_get_themes() as $theme ) {
				$data[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
			}

			// Return our object.
			return (object) array(
				'last_checked'      => time(),
				'updates'           => array(),
				'version_checked'   => $wp_version,
				'checked'           => $data,
			);
		}

		/**
		 * Always send back that the latest version of our plugins are the one we're running
		 *
		 * @return object     the modified output with our information
		 */
		public function last_checked_plugins() {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return false;
			}

			// Call the global WP version.
			global $wp_version;

			// Set a blank data array.
			$data = array();

			// Add our plugin file if we don't have it.
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Build my plugin data array.
			foreach ( get_plugins() as $file => $pl ) {
				$data[ $file ] = $pl['Version'];
			}

			// Return our object.
			return (object) array(
				'last_checked'      => time(),
				'updates'           => array(),
				'version_checked'   => $wp_version,
				'checked'           => $data,
			);
		}

		/**
		 * Filter for languages list transient. Returns locally available translations
		 * to avoid request into wp.org translation API.
		 *
		 * @param mixed $translations  Translation data returned from transient API.
		 *
		 * @return array                List of available languages.
		 */
		public function available_translations( $translations ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $translations;
			}

			/**
			 * If transient still contains list of languages just use those.
			 * Otherwise fallback to mimicked data which we create here.
			 */
			if ( $translations && ! empty( $translations ) ) {
				return $translations;
			} else {
				$installed_languages = get_available_languages();
				return $this->get_offline_languages( $installed_languages );
			}
		}

		/**
		 * Returns list of languages installed locally with full mimicked meta data.
		 *
		 * @param array $installed_languages  List of locally available languages.
		 *
		 * @return array                List of available languages in offline mode.
		 */
		private function get_offline_languages( $installed_languages ) {

			// This is list of languages which are available from translations API.
			$offline_languages = $this->get_offline_translation_information();

			// Call the global WP version.
			global $wp_version;

			// Tell WordPress that all translations are recent even though they can be old.
			$date = date_i18n( 'Y-m-d H:is' , time() );

			// Set an empty array of the available languages.
			$available_languages = array();

			// Loop through our installed languages.
			foreach ( $installed_languages as $lang ) {

				// Try to mimick the data that WordPress puts into 'available_translations' transient.
				$settings = array(
					'language'  => $lang,
					'iso'       => array( $lang ),
					'version'   => $wp_version,
					'updated'   => $date,
					'strings'   => array(
						'continue' => __( 'Continue' ),
					),
					'package'   => "https://downloads.wordpress.org/translation/core/{$wp_version}/{$lang}.zip",
				);

				// Combine the mimicked data with data we have stored in $offline_languages to give realistic output.
				if ( isset( $offline_languages[ $lang ] ) ) {
					$available_languages[ $lang ] = array_merge( $settings, $offline_languages[ $lang ] );
				}
			}

			// And return our language sets.
			return $available_languages;
		}

		/**
		 * We can't use wp_get_available_translations() offine.
		 * This function tries to return something similiar.
		 *
		 * @return array     List of wordpress language meta data.
		 */
		private function get_offline_translation_information() {

			// Build out the list of languages to use.
			$languages  = array(
				'af'                => array( 'english_name' => 'Afrikaans', 'native_name' => 'Afrikaans' ),
				'ar'                => array( 'english_name' => 'Arabic', 'native_name' => 'العربية' ),
				'ary'               => array( 'english_name' => 'Moroccan Arabic', 'native_name' => 'العربية المغربية' ),
				'as'                => array( 'english_name' => 'Assamese', 'native_name' => 'অসমীয়া' ),
				'az'                => array( 'english_name' => 'Azerbaijani', 'native_name' => 'Azərbaycan dili' ),
				'azb'               => array( 'english_name' => 'South Azerbaijani', 'native_name' => 'گؤنئی آذربایجان' ),
				'bel'               => array( 'english_name' => 'Belarusian', 'native_name' => 'Беларуская мова' ),
				'bg_BG'             => array( 'english_name' => 'Bulgarian', 'native_name' => 'Български' ),
				'bn_BD'             => array( 'english_name' => 'Bengali', 'native_name' => 'বাংলা' ),
				'bo'                => array( 'english_name' => 'Tibetan', 'native_name' => 'བོད་ཡིག' ),
				'bs_BA'             => array( 'english_name' => 'Bosnian', 'native_name' => 'Bosanski' ),
				'ca'                => array( 'english_name' => 'Catalan', 'native_name' => 'Català' ),
				'ceb'               => array( 'english_name' => 'Cebuano', 'native_name' => 'Cebuano' ),
				'ckb'               => array( 'english_name' => 'Kurdish (Sorani)', 'native_name' => 'كوردی‎' ),
				'cs_CZ'             => array( 'english_name' => 'Czech', 'native_name' => 'Čeština‎' ),
				'cy'                => array( 'english_name' => 'Welsh', 'native_name' => 'Cymraeg' ),
				'da_DK'             => array( 'english_name' => 'Danish', 'native_name' => 'Dansk' ),
				'de_DE_formal'      => array( 'english_name' => 'German (Formal)', 'native_name' => 'Deutsch (Sie)' ),
				'de_DE'             => array( 'english_name' => 'German', 'native_name' => 'Deutsch' ),
				'de_CH_informal'    => array( 'english_name' => 'German (Switzerland, Informal)', 'native_name' => 'Deutsch (Schweiz, Du)' ),
				'de_CH'             => array( 'english_name' => 'German (Switzerland)', 'native_name' => 'Deutsch (Schweiz)' ),
				'dzo'               => array( 'english_name' => 'Dzongkha', 'native_name' => 'རྫོང་ཁ' ),
				'el'                => array( 'english_name' => 'Greek', 'native_name' => 'Ελληνικά' ),
				'en_CA'             => array( 'english_name' => 'English (Canada)', 'native_name' => 'English (Canada)' ),
				'en_ZA'             => array( 'english_name' => 'English (South Africa)', 'native_name' => 'English (South Africa)' ),
				'en_AU'             => array( 'english_name' => 'English (Australia)', 'native_name' => 'English (Australia)' ),
				'en_NZ'             => array( 'english_name' => 'English (New Zealand)', 'native_name' => 'English (New Zealand)' ),
				'en_GB'             => array( 'english_name' => 'English (UK)', 'native_name' => 'English (UK)' ),
				'eo'                => array( 'english_name' => 'Esperanto', 'native_name' => 'Esperanto' ),
				'es_CL'             => array( 'english_name' => 'Spanish (Chile)', 'native_name' => 'Español de Chile' ),
				'es_AR'             => array( 'english_name' => 'Spanish (Argentina)', 'native_name' => 'Español de Argentina' ),
				'es_PE'             => array( 'english_name' => 'Spanish (Peru)', 'native_name' => 'Español de Perú' ),
				'es_MX'             => array( 'english_name' => 'Spanish (Mexico)', 'native_name' => 'Español de México' ),
				'es_CO'             => array( 'english_name' => 'Spanish (Colombia)', 'native_name' => 'Español de Colombia' ),
				'es_ES'             => array( 'english_name' => 'Spanish (Spain)', 'native_name' => 'Español' ),
				'es_VE'             => array( 'english_name' => 'Spanish (Venezuela)', 'native_name' => 'Español de Venezuela' ),
				'es_GT'             => array( 'english_name' => 'Spanish (Guatemala)', 'native_name' => 'Español de Guatemala' ),
				'et'                => array( 'english_name' => 'Estonian', 'native_name' => 'Eesti' ),
				'eu'                => array( 'english_name' => 'Basque', 'native_name' => 'Euskara' ),
				'fa_IR'             => array( 'english_name' => 'Persian', 'native_name' => 'فارسی' ),
				'fi'                => array( 'english_name' => 'Finnish', 'native_name' => 'Suomi' ),
				'fr_BE'             => array( 'english_name' => 'French (Belgium)', 'native_name' => 'Français de Belgique' ),
				'fr_FR'             => array( 'english_name' => 'French (France)', 'native_name' => 'Français' ),
				'fr_CA'             => array( 'english_name' => 'French (Canada)', 'native_name' => 'Français du Canada' ),
				'gd'                => array( 'english_name' => 'Scottish Gaelic', 'native_name' => 'Gàidhlig' ),
				'gl_ES'             => array( 'english_name' => 'Galician', 'native_name' => 'Galego' ),
				'gu'                => array( 'english_name' => 'Gujarati', 'native_name' => 'ગુજરાતી' ),
				'haz'               => array( 'english_name' => 'Hazaragi', 'native_name' => 'هزاره گی' ),
				'he_IL'             => array( 'english_name' => 'Hebrew', 'native_name' => 'עִבְרִית' ),
				'hi_IN'             => array( 'english_name' => 'Hindi', 'native_name' => 'हिन्दी' ),
				'hr'                => array( 'english_name' => 'Croatian', 'native_name' => 'Hrvatski' ),
				'hu_HU'             => array( 'english_name' => 'Hungarian', 'native_name' => 'Magyar' ),
				'hy'                => array( 'english_name' => 'Armenian', 'native_name' => 'Հայերեն' ),
				'id_ID'             => array( 'english_name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia' ),
				'is_IS'             => array( 'english_name' => 'Icelandic', 'native_name' => 'Íslenska' ),
				'it_IT'             => array( 'english_name' => 'Italian', 'native_name' => 'Italiano' ),
				'ja'                => array( 'english_name' => 'Japanese', 'native_name' => '日本語' ),
				'ka_GE'             => array( 'english_name' => 'Georgian', 'native_name' => 'ქართული' ),
				'kab'               => array( 'english_name' => 'Kabyle', 'native_name' => 'Taqbaylit' ),
				'km'                => array( 'english_name' => 'Khmer', 'native_name' => 'ភាសាខ្មែរ' ),
				'ko_KR'             => array( 'english_name' => 'Korean', 'native_name' => '한국어' ),
				'lo'                => array( 'english_name' => 'Lao', 'native_name' => 'ພາສາລາວ' ),
				'lt_LT'             => array( 'english_name' => 'Lithuanian', 'native_name' => 'Lietuvių kalba' ),
				'lv'                => array( 'english_name' => 'Latvian', 'native_name' => 'Latviešu valoda' ),
				'mk_MK'             => array( 'english_name' => 'Macedonian', 'native_name' => 'Македонски јазик' ),
				'ml_IN'             => array( 'english_name' => 'Malayalam', 'native_name' => 'മലയാളം' ),
				'mn'                => array( 'english_name' => 'Mongolian', 'native_name' => 'Монгол' ),
				'mr'                => array( 'english_name' => 'Marathi', 'native_name' => 'मराठी' ),
				'ms_MY'             => array( 'english_name' => 'Malay', 'native_name' => 'Bahasa Melayu' ),
				'my_MM'             => array( 'english_name' => 'Myanmar (Burmese)', 'native_name' => 'ဗမာစာ' ),
				'nb_NO'             => array( 'english_name' => 'Norwegian (Bokmål)', 'native_name' => 'Norsk bokmål' ),
				'ne_NP'             => array( 'english_name' => 'Nepali', 'native_name' => 'नेपाली' ),
				'nl_BE'             => array( 'english_name' => 'Dutch (Belgium)', 'native_name' => 'Nederlands (België)' ),
				'nl_NL'             => array( 'english_name' => 'Dutch', 'native_name' => 'Nederlands' ),
				'nl_NL_formal'      => array( 'english_name' => 'Dutch (Formal)', 'native_name' => 'Nederlands (Formeel)' ),
				'nn_NO'             => array( 'english_name' => 'Norwegian (Nynorsk)', 'native_name' => 'Norsk nynorsk' ),
				'oci'               => array( 'english_name' => 'Occitan', 'native_name' => 'Occitan' ),
				'pa_IN'             => array( 'english_name' => 'Punjabi', 'native_name' => 'ਪੰਜਾਬੀ' ),
				'pl_PL'             => array( 'english_name' => 'Polish', 'native_name' => 'Polski' ),
				'ps'                => array( 'english_name' => 'Pashto', 'native_name' => 'پښتو' ),
				'pt_BR'             => array( 'english_name' => 'Portuguese (Brazil)', 'native_name' => 'Português do Brasil' ),
				'pt_PT'             => array( 'english_name' => 'Portuguese (Portugal)', 'native_name' => 'Português' ),
				'rhg'               => array( 'english_name' => 'Rohingya', 'native_name' => 'Ruáinga' ),
				'ro_RO'             => array( 'english_name' => 'Romanian', 'native_name' => 'Română' ),
				'ru_RU'             => array( 'english_name' => 'Russian', 'native_name' => 'Русский' ),
				'sah'               => array( 'english_name' => 'Sakha', 'native_name' => 'Сахалыы' ),
				'si_LK'             => array( 'english_name' => 'Sinhala', 'native_name' => 'සිංහල' ),
				'sk_SK'             => array( 'english_name' => 'Slovak', 'native_name' => 'Slovenčina' ),
				'sl_SI'             => array( 'english_name' => 'Slovenian', 'native_name' => 'Slovenščina' ),
				'sq'                => array( 'english_name' => 'Albanian', 'native_name' => 'Shqip' ),
				'sr_RS'             => array( 'english_name' => 'Serbian', 'native_name' => 'Српски језик' ),
				'sv_SE'             => array( 'english_name' => 'Swedish', 'native_name' => 'Svenska' ),
				'szl'               => array( 'english_name' => 'Silesian', 'native_name' => 'Ślōnskŏ gŏdka' ),
				'ta_IN'             => array( 'english_name' => 'Tamil', 'native_name' => 'தமிழ்' ),
				'tah'               => array( 'english_name' => 'Tahitian', 'native_name' => 'Reo Tahiti' ),
				'te'                => array( 'english_name' => 'Telugu', 'native_name' => 'తెలుగు' ),
				'th'                => array( 'english_name' => 'Thai', 'native_name' => 'ไทย' ),
				'tl'                => array( 'english_name' => 'Tagalog', 'native_name' => 'Tagalog' ),
				'tr_TR'             => array( 'english_name' => 'Turkish', 'native_name' => 'Türkçe' ),
				'tt_RU'             => array( 'english_name' => 'Tatar', 'native_name' => 'Татар теле' ),
				'ug_CN'             => array( 'english_name' => 'Uighur', 'native_name' => 'Uyƣurqə' ),
				'uk'                => array( 'english_name' => 'Ukrainian', 'native_name' => 'Українська' ),
				'ur'                => array( 'english_name' => 'Urdu', 'native_name' => 'اردو' ),
				'uz_UZ'             => array( 'english_name' => 'Uzbek', 'native_name' => 'O‘zbekcha' ),
				'vi'                => array( 'english_name' => 'Vietnamese', 'native_name' => 'Tiếng Việt' ),
				'zh_CN'             => array( 'english_name' => 'Chinese (China)', 'native_name' => '简体中文' ),
				'zh_HK'             => array( 'english_name' => 'Chinese (Hong Kong)', 'native_name' => '香港中文版' ),
				'zh_TW'             => array( 'english_name' => 'Chinese (Taiwan)', 'native_name' => '繁體中文' ),
			);

			// Allow adding or removing languages.
			return apply_filters( 'airplane_mode_offline_languages', $languages );
		}

		/**
		 * Return an empty array of items requiring update for both themes and plugins
		 *
		 * @param  array $items  All the items being passed for update.
		 *
		 * @return array         An empty array, or the original items if not enabled.
		 */
		public function remove_update_array( $items ) {
			return ! $this->enabled() ? $items : array();
		}

		/**
		 * Remove the ability to update plugins/themes from single
		 * site and multisite bulk actions
		 *
		 * @param  array $actions  All the bulk actions.
		 *
		 * @return array $actions  The remaining actions
		 */
		public function remove_bulk_actions( $actions ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $actions;
			}

			// Set an array of items to be removed with optional filter.
			if ( false === $remove = apply_filters( 'airplane_mode_bulk_items', array( 'update-selected', 'update', 'upgrade' ) ) ) {
				return $actions;
			}

			// Loop the item array and unset each.
			foreach ( $remove as $key ) {
				unset( $actions[ $key ] );
			}

			// Return the remaining.
			return $actions;
		}

		/**
		 * Remove the tabs on the plugin page to add new items
		 * since they require the WP connection and will fail.
		 *
		 * @param  array $nonmenu_tabs  All the tabs displayed.
		 * @return array $nonmenu_tabs  The remaining tabs.
		 */
		public function plugin_add_tabs( $nonmenu_tabs ) {

			// Bail if disabled.
			if ( ! $this->enabled() ) {
				return $nonmenu_tabs;
			}

			// Set an array of tabs to be removed with optional filter.
			if ( false === $remove = apply_filters( 'airplane_mode_bulk_items', array( 'featured', 'popular', 'recommended', 'favorites', 'beta' ) ) ) {
				return $nonmenu_tabs;
			}

			// Loop the item array and unset each.
			foreach ( $remove as $key ) {
				unset( $nonmenu_tabs[ $key ] );
			}

			// Return the tabs.
			return $nonmenu_tabs;
		}

		/**
		 * Increase HTTP request counter by one.
		 *
		 * @return void
		 */
		public function count_http_requests() {
			$this->http_count++;
		}

		// End class.
	}

} // End class_exists.

if ( ! class_exists( 'Airplane_Mode_WP_Error' ) ) {

	/**
	 * Extend the WP_Error class to include our own.
	 */
	class Airplane_Mode_WP_Error extends WP_Error {

		/**
		 * Get our error data and return it.
		 *
		 * @return string
		 */
		public function __tostring() {
			$data = $this->get_error_data();
			return $data['return'];
		}

		// End class.
	}

}

// Instantiate our class.
$Airplane_Mode_Core = Airplane_Mode_Core::getInstance();
