<?php

	namespace Codeception\Module;

	trait WPBrowserMethods {

		/**
		 * Goes to the login page and logs in as the site admin.
		 *
		 * @return void
		 */
		public function loginAsAdmin() {
			$this->loginAs( $this->config['adminUsername'], $this->config['adminPassword'] );
		}

		/**
		 * Goes to the login page and logs in using the given credentials.
		 *
		 * @param string $username
		 * @param string $password
		 *
		 * @return void
		 */
		public function loginAs( $username, $password ) {
			$this->amOnPage( $this->loginUrl );
			$this->fillField( '#user_login', $username );
			$this->fillField( '#user_pass', $password );
			$this->click( '#wp-submit' );
		}

		/**
		 * In the plugin administration screen activates a plugin clicking the "Activate" link.
		 *
		 * The method will presume the browser is in the plugin screen already.
		 *
		 * @param  string $pluginSlug The plugin slug, like "hello-dolly".
		 *
		 * @return void
		 */
		public function activatePlugin( $pluginSlug ) {
			$this->click( "table.plugins tr[data-slug='{$pluginSlug}'] span.activate > a:first-of-type" );
		}

		/**
		 * In the plugin administration screen deactivates a plugin clicking the "Deactivate" link.
		 *
		 * The method will presume the browser is in the plugin screen already.
		 *
		 * @param  string $pluginSlug The plugin slug, like "hello-dolly".
		 *
		 * @return void
		 */
		public function deactivatePlugin( $pluginSlug ) {
			$this->click( "table.plugins tr[data-slug='{$pluginSlug}'] span.deactivate > a:first-of-type" );
		}

		/**
		 * Navigates the browser to the plugins administration screen.
		 *
		 * Makes no check about the user being logged in and authorized to do so.
		 *
		 * @return void
		 */
		public function amOnPluginsPage() {
			$this->amOnPage( $this->pluginsUrl );
		}

		/**
		 * Navigates the browser to the Pages administration screen.
		 *
		 * Makes no check about the user being logged in and authorized to do so.
		 *
		 * @return void
		 */
		public function amOnPagesPage() {
			$this->amOnPage( $this->adminUrl . '/edit.php?post_type=page' );
		}

		/**
		 * Looks for a deactivated plugin in the plugin administration screen.
		 *
		 * Will not navigate to the plugin administration screen.
		 *
		 * @param string $pluginSlug The plugin slug, like "hello-dolly".
		 *
		 * @return void
		 */
		public function seePluginDeactivated( $pluginSlug ) {
			$this->seePluginInstalled( $pluginSlug );
			$this->seeElement( "table.plugins tr[data-slug='$pluginSlug'].inactive" );
		}

		/**
		 * Looks for a plugin in the plugin administration screen.
		 *
		 * Will not navigate to the plugin administration screen.
		 *
		 * @param  string $pluginSlug The plugin slug, like "hello-dolly".
		 *
		 * @return void
		 */
		public function seePluginInstalled( $pluginSlug ) {
			$this->seeElement( "table.plugins tr[data-slug='$pluginSlug']" );
		}

		/**
		 * Looks for an activated plugin in the plugin administration screen.
		 *
		 * Will not navigate to the plugin administration screen.
		 *
		 * @param string $pluginSlug The plugin slug, like "hello-dolly".
		 *
		 * @return void
		 */
		public function seePluginActivated( $pluginSlug ) {
			$this->seePluginInstalled( $pluginSlug );
			$this->seeElement( "table.plugins tr[data-slug='$pluginSlug'].active" );
		}

		/**
		 * Looks for a missing plugin in the plugin administration screen.
		 *
		 * Will not navigate to the plugin administration screen.
		 *
		 * @param  string $pluginSlug The plugin slug, like "hello-dolly".
		 *
		 * @return void
		 */
		public function dontSeePluginInstalled( $pluginSlug ) {
			$this->dontSeeElement( "table.plugins tr[data-slug='$pluginSlug']" );
		}

		/**
		 * In an administration screen will look for an error message.
		 *
		 * Allows for class-based error checking to decouple from internationalization.
		 *
		 * @param array $classes A list of classes the error notice should have.
		 *
		 * @return void
		 */
		public function seeErrorMessage( $classes = '' ) {
			if ( is_array( $classes ) ) {
				$classes = implode( '.', $classes );
			}
			if ( $classes ) {
				$classes = '.' . $classes;
			}
			$this->seeElement( '#message.error' . $classes );
		}

		/**
		 * Checks that the current page is a wp_die generated one.
		 *
		 * @return void
		 */
		public function seeWpDiePage() {
			$this->seeElement( 'body#error-page' );
		}

		/**
		 * In an administration screen will look for a message.
		 *
		 * Allows for class-based error checking to decouple from internationalization.
		 *
		 * @param array $classes A list of classes the message should have.
		 *
		 * @return void
		 */
		public function seeMessage( $classes = '' ) {
			if ( is_array( $classes ) ) {
				$classes = implode( '.', $classes );
			}
			if ( $classes ) {
				$classes = '.' . $classes;
			}
			$this->seeElement( '#message.updated' . $classes );
		}

		/**
		 * Returns WordPress default test cookie if present.
		 *
		 * @param null $pattern Optional, overrides the default cookie name.
		 *
		 * @return mixed Either a cookie or null.
		 */
		public function grabWordPressTestCookie( $pattern = null ) {
			$pattern = $pattern ? $pattern : 'wordpress_test_cookie';

			return $this->grabCookie( $pattern );
		}

		/**
		 * Returns WordPress default login cookie if present.
		 *
		 * @param null $pattern Optional, overrides the default cookie name.
		 *
		 * @return mixed Either a cookie or null.
		 */
		public function grabWordPressLoginCookie( $pattern = null ) {
			$pattern = $pattern ? $pattern : '/^wordpress_logged_in_[a-z0-9]{32}$/';
			$cookies = $this->grabCookiesWithPattern( $pattern );

			return empty( $cookies ) ? null : array_pop( $cookies );
		}

		/**
		 * Returns WordPress default auth cookie if present.
		 *
		 * @param null $pattern Optional, overrides the default cookie name.
		 *
		 * @return mixed Either a cookie or null.
		 */
		public function grabWordPressAuthCookie( $pattern = null ) {
			$pattern = $pattern ? $pattern : '/^wordpress_[a-z0-9]{32}$/';
			$cookies = $this->grabCookiesWithPattern( $pattern );

			return empty( $cookies ) ? null : array_pop( $cookies );
		}

		public function amOnAdminPage($path)
		{
			$this->amOnPage($this->adminUrl . '/' . ltrim($path, '/'));
		}
	}