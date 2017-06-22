<?php

namespace Codeception\Module;

trait WPBrowserMethods
{

	/**
	 * Goes to the login page and logs in as the site admin.
	 *
	 * @return array An array of login credentials and auth cookies.
	 */
	public function loginAsAdmin()
	{
		return $this->loginAs($this->config['adminUsername'], $this->config['adminPassword']);
	}

	/**
	 * Goes to the login page and logs in using the given credentials.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return array An array of login credentials and auth cookies.
	 */
	public function loginAs($username, $password)
	{
		$this->amOnPage($this->loginUrl);
		$this->submitForm('#loginform', ['log' =>$username,'pwd' => $password ,'testcookie' => '1', 'redirect_to' => ''], '#wp-submit');

		return [
			'username' => $username,
			'password' => $password,
			'authCookie' => $this->grabWordPressAuthCookie(),
			'loginCookie' => $this->grabWordPressLoginCookie()
		];
	}

	/**
	 * Returns WordPress default auth cookie if present.
	 *
	 * @param null $pattern Optional, overrides the default cookie name.
	 *
	 * @return mixed Either a cookie or null.
	 */
	public function grabWordPressAuthCookie($pattern = null)
	{
		$pattern = $pattern ? $pattern : '/^wordpress_[a-z0-9]{32}$/';
		$cookies = $this->grabCookiesWithPattern($pattern);

		return empty($cookies) ? null : array_pop($cookies);
	}

	/**
	 * Returns WordPress default login cookie if present.
	 *
	 * @param null $pattern Optional, overrides the default cookie name.
	 *
	 * @return mixed Either a cookie or null.
	 */
	public function grabWordPressLoginCookie($pattern = null)
	{
		$pattern = $pattern ? $pattern : '/^wordpress_logged_in_[a-z0-9]{32}$/';
		$cookies = $this->grabCookiesWithPattern($pattern);

		return empty($cookies) ? null : array_pop($cookies);
	}

	/**
	 * In the plugin administration screen activates a plugin clicking the "Activate" link.
	 *
	 * The method will presume the browser is in the plugin screen already.
	 *
	 * @param  string|array $pluginSlug The plugin slug, like "hello-dolly" or a list of plugin slugs.
	 *
	 * @return void
	 */
	public function activatePlugin($pluginSlug)
	{
		$plugins = (array) $pluginSlug;
		foreach ($plugins as $plugin) {
			$option = '//*[@data-slug="' . $plugin . '"]/th/input';
			$this->scrollTo($option, 0, -40);
			$this->checkOption($option);
		}
		$this->scrollTo('select[name="action"]', 0, -40);
		$this->selectOption('action', 'activate-selected');
		$this->click("#doaction");
	}

	/**
	 * In the plugin administration screen deactivates a plugin clicking the "Deactivate" link.
	 *
	 * The method will presume the browser is in the plugin screen already.
	 *
	 * @param  string|array $pluginSlug The plugin slug, like "hello-dolly" or a list of plugin slugs.
	 *
	 * @return void
	 */
	public function deactivatePlugin($pluginSlug)
	{
		$plugins = (array) $pluginSlug;
		foreach ($plugins as $plugin) {
			$option = '//*[@data-slug="' . $plugin . '"]/th/input';
			$this->scrollTo($option, 0, -40);
			$this->checkOption($option);
		}
		$this->scrollTo('select[name="action"]', 0, -40);
		$this->selectOption('action', 'deactivate-selected');
		$this->click("#doaction");
	}

	/**
	 * Navigates the browser to the plugins administration screen.
	 *
	 * Makes no check about the user being logged in and authorized to do so.
	 *
	 * @return void
	 */
	public function amOnPluginsPage()
	{
		$this->amOnPage($this->pluginsPath);
	}

	/**
	 * Navigates the browser to the Pages administration screen.
	 *
	 * Makes no check about the user being logged in and authorized to do so.
	 *
	 * @return void
	 */
	public function amOnPagesPage()
	{
		$this->amOnPage($this->adminPath . '/edit.php?post_type=page');
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
	public function seePluginDeactivated($pluginSlug)
	{
		$this->seePluginInstalled($pluginSlug);
		$this->seeElement("table.plugins tr[data-slug='$pluginSlug'].inactive");
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
	public function seePluginInstalled($pluginSlug)
	{
		$this->seeElement("table.plugins tr[data-slug='$pluginSlug']");
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
	public function seePluginActivated($pluginSlug)
	{
		$this->seePluginInstalled($pluginSlug);
		$this->seeElement("table.plugins tr[data-slug='$pluginSlug'].active");
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
	public function dontSeePluginInstalled($pluginSlug)
	{
		$this->dontSeeElement("table.plugins tr[data-slug='$pluginSlug']");
	}

	/**
	 * In an administration screen will look for an error message.
	 *
	 * Allows for class-based error checking to decouple from internationalization.
	 *
	 * @param array|string $classes A list of classes the error notice should have.
	 *
	 * @return void
	 */
	public function seeErrorMessage($classes = '')
	{
		if (is_array($classes)) {
			$classes = implode('.', $classes);
		}
		if ($classes) {
			$classes = '.' . $classes;
		}
		$this->seeElement('#message.error' . $classes);
	}

	/**
	 * Checks that the current page is a wp_die generated one.
	 *
	 * @return void
	 */
	public function seeWpDiePage()
	{
		$this->seeElement('body#error-page');
	}

	/**
	 * In an administration screen will look for a message.
	 *
	 * Allows for class-based error checking to decouple from internationalization.
	 *
	 * @param array|string $classes A list of classes the message should have.
	 *
	 * @return void
	 */
	public function seeMessage($classes = '')
	{
		if (is_array($classes)) {
			$classes = implode('.', $classes);
		}
		if ($classes) {
			$classes = '.' . $classes;
		}
		$this->seeElement('#message.updated' . $classes);
	}

	/**
	 * Returns WordPress default test cookie if present.
	 *
	 * @param null $pattern Optional, overrides the default cookie name.
	 *
	 * @return mixed Either a cookie or null.
	 */
	public function grabWordPressTestCookie($pattern = null)
	{
		$pattern = $pattern ? $pattern : 'wordpress_test_cookie';

		return $this->grabCookie($pattern);
	}

	/**
	 * Goes to a page relative to the admin URL.
	 *
	 * @param string $path
	 */
	public function amOnAdminPage($path)
	{
		$this->amOnPage($this->adminPath . '/' . ltrim($path, '/'));
	}

	/**
	 * Goes to the `admin-ajax.php` page.
	 *
	 * @return null|string
	 */
	public function amOnAdminAjaxPage()
	{
		return $this->amOnAdminPage('admin-ajax.php');
	}

	/**
	 * Goes to the cron page.
	 *
	 * Useful to trigger cron jobs.
	 *
	 * @return null|string
	 */
	public function amOnCronPage()
	{
		return $this->amOnPage('/wp-cron.php');
	}

	/**
	 * Goes to the post edit page for the post with the specified post ID.
	 *
	 * @param int $id
	 */
	public function amEditingPostWithId($id)
	{
		if (!is_numeric($id) && intval($id) == $id) {
			throw new \InvalidArgumentException('ID must be an int value');
		}

		$this->amOnAdminPage('/post.php?post=' . $id . '&action=edit');
	}
}