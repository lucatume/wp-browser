<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\ModuleContainer;
use Codeception\TestInterface;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;

class WordPress extends Framework implements DependsOnModule {

	use WPBrowserMethods;

	/**
	 * @var \tad\WPBrowser\Connector\WordPress
	 */
	public $client;

	/**
	 * @var array
	 */
	protected $requiredFields = ['wpRootFolder', 'adminUsername', 'adminPassword'];

	/**
	 * @var array
	 */
	protected $config = ['adminPath' => '/wp-admin'];

	/**
	 * @var string
	 */
	protected $adminPath;

	/**
	 * @var bool
	 */
	protected $isMockRequest = FALSE;

	/**
	 * @var bool
	 */
	protected $lastRequestWasAdmin = FALSE;

	/**
	 * @var string
	 */
	protected $dependencyMessage
		= <<< EOF
Example configuring WPDb
--
modules
    enabled:
        - WPDb:
            dsn: 'mysql:host=localhost;dbname=wp'
            user: 'root'
            password: 'root'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            reconnect: false
            url: 'http://wp.dev'
            tablePrefix: 'wp_'
        - WordPress:
            depends: WPDb
            wpRootFolder: "/Users/Luca/Sites/codeception-acceptance"
            adminUsername: 'admin'
            adminPassword: 'admin'
EOF;

	/**
	 * @var WPDb
	 */
	protected $wpdbModule;

	/**
	 * @var string The site URL.
	 */
	protected $siteUrl;

	/**
	 * @var string
	 */
	protected  $loginUrl = '';

	/**
	 * @var string The string that will hold the response content after each request handling.
	 */
	public $response = '';

	/**
	 * WordPress constructor.
	 *
	 * @param ModuleContainer $moduleContainer
	 * @param array           $config
	 * @param                 $client WordPressConnector
	 */
	public function __construct(ModuleContainer $moduleContainer, $config = [], WordPressConnector $client = NULL) {
		parent::__construct($moduleContainer, $config);
		$this->ensureWpRoot();
		$this->adminPath = $this->config['adminPath'];
		$this->client    = $client;
	}

	private function ensureWpRoot() {
		$wpRootFolder = $this->config['wpRootFolder'];
		if (!file_exists($wpRootFolder . DIRECTORY_SEPARATOR . 'wp-settings.php')) {
			throw new ModuleConfigException(__CLASS__,
				"\nThe path `{$wpRootFolder}` is not pointing to a valid WordPress installation folder.");
		}
	}

	/**
	 * @param TestInterface $test
	 */
	public function _before(TestInterface $test) {
		/** @var WPDb $wpdb */
		$wpdb          = $this->getModule('WPDb');
		$this->siteUrl = $wpdb->grabSiteUrl();
		$this->loginUrl = '/wp-login.php';
		$this->setupClient($wpdb->getSiteDomain());
	}

	private function setupClient($siteDomain) {
		$this->client = $this->client ? $this->client : new WordPressConnector();
		$this->client->setUrl($this->siteUrl);
		$this->client->setDomain($siteDomain);
		$this->client->setRootFolder($this->config['wpRootFolder']);
		$this->client->followRedirects(TRUE);
		$this->client->resetCookies();
		$this->setCookiesFromOptions();
	}

	/**
	 * @param $client
	 */
	public function _setClient($client) {
		$this->client = $client;
	}

	/**
	 * @param bool $isMockRequest
	 */
	public function _isMockRequest($isMockRequest = FALSE) {
		$this->isMockRequest = $isMockRequest;
	}

	/**
	 * @return bool
	 */
	public function _lastRequestWasAdmin() {
		return $this->lastRequestWasAdmin;
	}

	/**
	 * Specifies class or module which is required for current one.
	 *
	 * THis method should return array with key as class name and value as error message
	 * [className => errorMessage]
	 *
	 * @return array
	 */
	public function _depends() {
		return ['Codeception\Module\WPDb' => $this->dependencyMessage];
	}

	/**
	 * @param WPDb $wpdbModule
	 */
	public function _inject(WPDb $wpdbModule) {
		$this->wpdbModule = $wpdbModule;
	}

	/**
	 * Goes to an admin page on the site.
	 *
	 * @param string $page The relative path to an admin page.
	 *
	 * @return null|string
	 */
	public function amOnAdminPage($page) {
    $preparedPage = $this->preparePage(ltrim($page, '/'));
    if ($preparedPage === '/') {
      $preparedPage = 'index.php';
    }
    $page         = $this->adminPath . '/' . $preparedPage;
		return $this->amOnPage($page);
	}

	/**
	 * @param $page
	 *
	 * @return string
	 */
	private function preparePage($page) {
		$page = $this->untrailslashIt($page);
		$page = empty($page) || preg_match('~\\/?index\\.php\\/?~', $page) ? '/' : $page;

		return $page;
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	private function untrailslashIt($path) {
		$path = preg_replace('~\\/?$~', '', $path);
		return $path;
	}

	/**
	 * Goes to a page on the site.
	 *
	 * @param string $page The relative path to a page.
	 *
	 * @return null|string
	 */
	public function amOnPage($page) {
		$this->setRequestType($page);

		$parts      = parse_url($page);
		$parameters = [];
		if (!empty($parts['query'])) {
			parse_str($parts['query'], $parameters);
		}

		$this->client->setHeaders($this->headers);

		if ($this->isMockRequest) {
			return $page;
		}

		$this->setCookie('wordpress_test_cookie', 'WP Cookie check');
		$this->_loadPage('GET', $page, $parameters);

		return NULL;
	}

	/**
	 * @param $page
	 */
	private function setRequestType($page) {
		if ($this->isAdminPageRequest($page)) {
			$this->lastRequestWasAdmin = TRUE;
		} else {
			$this->lastRequestWasAdmin = FALSE;
		}
	}

	private function isAdminPageRequest($page) {
		return 0 === strpos($page, $this->adminPath);
	}

	/**
	 * Returns a list of recognized domain names
	 *
	 * @return array
	 */
	public function getInternalDomains() {
		$internalDomains   = [];
		$internalDomains[] = '/^' . preg_quote(parse_url($this->siteUrl, PHP_URL_HOST)) . '$/';
		return $internalDomains;
	}

	/**
	 * Returns the absolute path to the WordPress root folder.
	 *
	 * @return string
	 */
	public function getWpRootFolder() {
		return $this->config['wpRootFolder'];
	}

	/**
	 * Returns the raw response content.
	 *
	 * @return string
	 */
	public function getResponseContent() {
		return $this->_getResponseContent();
	}

	protected function getAbsoluteUrlFor($uri) {
		$uri = str_replace($this->siteUrl, 'http://localhost',
			str_replace(urlencode($this->siteUrl), urlencode('http://localhost'), $uri));
		return parent::getAbsoluteUrlFor($uri);
	}

	/**
	 * Gets a cookie value and sets it on the current $_COOKIE array.
	 *
	 * @param string $cookie The cookie name
	 * @param array  $params Parameter to filter the cookie value
	 */
	public function extractCookie($cookie, array $params = []) {
		$cookieValue      = $this->grabCookie($cookie, $params);
		$_COOKIE[$cookie] = $cookieValue;
	}

	/**
	 * Logs in as the specified user.
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function loginAs($username, $password) {
    $this->amOnPage($this->loginUrl);
    $this->submitForm('#loginform', [
      'log' =>$username,
      'pwd' => $password ,
      'testcookie' => '1',
      'redirect_to' => ''
    ], '#wp-submit');
	}
}
