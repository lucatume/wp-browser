<?php

namespace Codeception\Module;

use Symfony\Component\BrowserKit\Cookie;

/**
 * A Codeception module offering specific WordPress browsing methods.
 */
class WPBrowser extends PhpBrowser
{
	use WPBrowserMethods;

	/**
	 * The module required fields, to be set in the suite .yml configuration file.
	 *
	 * @var array
	 */
	protected $requiredFields = ['adminUsername', 'adminPassword', 'adminPath'];
	/**
	 * The login screen absolute URL
	 *
	 * @var string
	 */
	protected $loginUrl;
	/**
	 * The admin absolute URL.
	 *
	 * @var [type]
	 */
	protected $adminPath;
	/**
	 * The plugin screen absolute URL
	 *
	 * @var string
	 */
	protected $pluginsPath;

	/**
	 * Initializes the module setting the properties values.
	 * @return void
	 */
	public function _initialize()
	{
		parent::_initialize();

		$this->configBackCompat();

		$adminPath = $this->config['adminPath'];
		$this->loginUrl = str_replace('wp-admin', 'wp-login.php', $adminPath);
		$this->adminPath = rtrim($adminPath, '/');
		$this->pluginsPath = $this->adminPath . '/plugins.php';
	}

	protected function configBackCompat()
	{
		if (isset($this->config['adminUrl']) && !isset($this->config['adminPath'])) {
			$this->config['adminPath'] = $this->config['adminUrl'];
		}
	}

	/**
	 * Returns all the cookies whose name matches a regex pattern.
	 *
	 * @param string $cookiePattern
	 *
	 * @return Cookie|null
	 */
	public function grabCookiesWithPattern($cookiePattern)
	{
		/**
		 * @var Cookie[]
		 */
		$cookies = $this->client->getCookieJar()->all();

		if (!$cookies) {
			return null;
		}
		$matchingCookies = array_filter($cookies, function (Cookie $cookie) use ($cookiePattern) {

			return preg_match($cookiePattern, $cookie->getName());
		});
		$cookieList = array_map(function (Cookie $cookie) {
			return sprintf('{"%s": "%s"}', $cookie->getName(), $cookie->getValue());
		}, $matchingCookies);

		$this->debug('Cookies matching pattern ' . $cookiePattern . ' : ' . implode(', ', $cookieList));

		return is_array($matchingCookies) ? $matchingCookies : null;
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
		$plugins = (array)$pluginSlug;
		foreach ($plugins as $plugin) {
			$this->checkOption('//*[@data-slug="' . $plugin . '"]/th/input');
		}
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
	public function deactivatePlugin($pluginSlug) {
		$plugins = (array) $pluginSlug;
		foreach ($plugins as $plugin) {
			$this->checkOption('//*[@data-slug="' . $plugin . '"]/th/input');
		}
		$this->selectOption('action', 'deactivate-selected');
		$this->click("#doaction");
	}

	protected function validateConfig()
	{
		$this->configBackCompat();

		parent::validateConfig();
	}


}
