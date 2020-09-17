<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\ModuleContainer;
use Codeception\TestInterface;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;
use function tad\WPBrowser\parseUrl;
use function tad\WPBrowser\requireCodeceptionModules;
use function tad\WPBrowser\resolvePath;
use function tad\WPBrowser\untrailslashit;

//phpcs:disable
requireCodeceptionModules('WordPress', [ '\\Codeception\\Lib\\Framework' ]);
//phpcs:enable

/**
 * A module dedicated to functional testing using acceptance-like methods.
 * Differently from WPBrowser or WpWebDriver modules the WordPress code will be loaded in the same scope as the tests.
 *
 * @package Codeception\Module
 */
class WordPress extends Framework implements DependsOnModule
{

    use WPBrowserMethods;

    /**
     * @var WordPressConnector|null
     */
    public $client;

    /**
     * @var string
     */
    public $wpRootFolder;

    /**
     * @var array
     */
    protected $requiredFields = ['wpRootFolder', 'adminUsername', 'adminPassword'];

    /**
     * @var array
     */
    protected $config = ['adminPath' => '/wp-admin'];

    /**
     * @var bool
     */
    protected $isMockRequest = false;

    /**
     * @var bool
     */
    protected $lastRequestWasAdmin = false;

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
     * @var string The string that will hold the response content after each request handling.
     */
    public $response = '';

    /**
     * WordPress constructor.
     *
     * @param \Codeception\Lib\ModuleContainer $moduleContainer The module container this module is loaded from.
     * @param array<string,string|int|bool>    $config          The module configuration
     * @param WordPressConnector               $client          The client connector that will process the requests.
     *
     * @throws \Codeception\Exception\ModuleConfigException
     */
    public function __construct(
        ModuleContainer $moduleContainer,
        $config = [],
        WordPressConnector $client = null
    ) {
        parent::__construct($moduleContainer, $config);

        $this->getWpRootFolder();
        $this->setAdminPath($this->config['adminPath']);
        $this->client = $client ?: $this->buildConnector();
    }

    /**
     * @param TestInterface $test
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function _before(TestInterface $test)
    {
        /** @var WPDb $wpdb */
        $wpdb          = $this->getModule('WPDb');
        $this->siteUrl = $wpdb->grabSiteUrl();
        $this->setLoginUrl('/wp-login.php');
        $this->setupClient($wpdb->getSiteDomain());
    }

    /**
     * Sets up the client/connector for the request.
     *
     * @param string $siteDomain The current site domain, e.g. 'wordpress.test'.
     */
    private function setupClient($siteDomain)
    {
        $this->client = $this->client ?: $this->buildConnector();
        $this->client->setUrl($this->siteUrl);
        $this->client->setDomain($siteDomain);
        $this->client->setRootFolder($this->config['wpRootFolder']);
        $this->client->followRedirects(true);
        $this->client->resetCookies();
        $this->setCookiesFromOptions();
    }

    /**
     * Internal method to inject the client to use.
     *
     * @param WordPressConnector $client The client object that should be used.
     */
    public function _setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @param bool $isMockRequest
     */
    public function _isMockRequest($isMockRequest = false)
    {
        $this->isMockRequest = $isMockRequest;
    }

    /**
     * @return bool
     */
    public function _lastRequestWasAdmin()
    {
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
    public function _depends()
    {
        return ['Codeception\Module\WPDb' => $this->dependencyMessage];
    }

    /**
     * @param WPDb $wpdbModule
     */
    public function _inject(WPDb $wpdbModule)
    {
        $this->wpdbModule = $wpdbModule;
    }

    /**
     * Go to a page in the admininstration area of the site.
     *
     * @example
     * ```php
     * $I->loginAs('user', 'password');
     * // Go to the plugins management screen.
     * $I->amOnAdminPage('/plugins.php');
     * ```
     *
     * @param string $page The path, relative to the admin area URL, to the page.
     */
    public function amOnAdminPage($page)
    {
        $preparedPage = $this->preparePage(ltrim($page, '/'));
        if ($preparedPage === '/') {
            $preparedPage = 'index.php';
        }
        $page         = $this->getAdminPath() . '/' . $preparedPage;
        return $this->amOnPage($page);
    }

    /**
     * Prepares the page path for the request.
     *
     * @param string $page The input page path.
     *
     * @return string The prepared page path.
     */
    private function preparePage($page)
    {
        $page = untrailslashIt($page);
        $page = empty($page) || preg_match('~\\/?index\\.php\\/?~', $page) ? '/' : $page;

        return $page;
    }

    /**
     * Go to a page on the site.
     *
     * The module will try to reach the page, relative to the URL specified in the module configuration, without
     * applying any permalink resolution.
     *
     * @example
     * ```php
     * // Go the the homepage.
     * $I->amOnPage('/');
     * // Go to the single page of post with ID 23.
     * $I->amOnPage('/?p=23');
     * // Go to search page for the string "foo".
     * $I->amOnPage('/?s=foo');
     * ```
     *
     * @param string $page The path to the page, relative to the the root URL.
     *
     * @return string The page path.
     */
    public function amOnPage($page)
    {
        $this->setRequestType($page);

        $parts      = parseUrl($page);
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

        return $page;
    }

    private function setRequestType($page)
    {
        if ($this->isAdminPageRequest($page)) {
            $this->lastRequestWasAdmin = true;
        } else {
            $this->lastRequestWasAdmin = false;
        }
    }

    private function isAdminPageRequest($page)
    {
        return 0 === strpos($page, $this->getAdminPath());
    }

    /**
     * Returns a list of recognized domain names for the test site.
     *
     * @internal This method is public for inter-operability and compatibility purposes and should
     *           not be considered part of the API.
     *
     * @return array
     */
    public function getInternalDomains()
    {
        $internalDomains   = [];
        $host = parse_url($this->siteUrl, PHP_URL_HOST) ?: 'localhost';
        $internalDomains[] = '#^' . preg_quote($host, '#') . '$#';

        return $internalDomains;
    }

    /**
     * Returns the absolute path to the WordPress root folder.
     *
     * @example
     * ```php
     * $root = $I->getWpRootFolder();
     * $this->assertFileExists($root . '/someFile.txt');
     * ```
     *
     * @return string The absolute path to the WordPress root folder, without a trailing slash.
     *
     * @throws \InvalidArgumentException If the WordPress root folder is not valid.
     */
    public function getWpRootFolder()
    {
        if (empty($this->wpRootFolder)) {
            try {
                $this->wpRootFolder = resolvePath($this->config['wpRootFolder']);
            } catch (\Exception $e) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "\nThe path `{$this->config['wpRootFolder']}` is not pointing to a valid WordPress " .
                    'installation folder: directory not found.'
                );
            }
            if (!file_exists(untrailslashit($this->wpRootFolder) . '/wp-settings.php')) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "\nThe `{$this->config['wpRootFolder']}` is not pointing to a valid WordPress installation " .
                    'folder: wp-settings.php file not found.'
                );
            }
        }

        return $this->wpRootFolder;
    }

    /**
     * Returns content of the last response.
     * This method exposes an underlying API for custom assertions.
     *
     * @example
     * ```php
     * // In test class.
     * $this->assertContains($text, $this->getResponseContent(), "foo-bar");
     * ```
     *
     * @return string The response content, in plain text.
     *
     * @throws \Codeception\Exception\ModuleException If the underlying modules is not available.
     */
    public function getResponseContent()
    {
        return $this->_getResponseContent();
    }

    protected function getAbsoluteUrlFor($uri)
    {
        $uri = str_replace(
            $this->siteUrl,
            'http://localhost',
            str_replace(urlencode($this->siteUrl), urlencode('http://localhost'), $uri)
        );
        return parent::getAbsoluteUrlFor($uri);
    }

    /**
     * Grab a cookie value from the current session, sets it in the $_COOKIE array and returns its value.
     *
     * This method utility is to get, in the scope of test code, the value of a cookie set during the tests.
     *
     * @example
     * ```php
     * $id = $I->haveUserInDatabase('user', 'subscriber', ['user_pass' => 'pass']);
     * $I->loginAs('user', 'pass');
     * // The cookie is now set in the `$_COOKIE` super-global.
     * $I->extractCookie(LOGGED_IN_COOKIE);
     * // Generate a nonce using WordPress methods (see WPLoader in loadOnly mode) with correctly set context.
     * wp_set_current_user($id);
     * $nonce = wp_create_nonce('wp_rest');
     * // Use the generated nonce to make a request to the the REST API.
     * $I->haveHttpHeader('X-WP-Nonce', $nonce);
     * ```
     *
     * @param string $cookie The cookie name.
     * @param array  $params Parameters to filter the cookie value.
     *
     * @return mixed|string|null The cookie value or `null` if no cookie matching the parameters is found.
     */
    public function extractCookie($cookie, array $params = [])
    {
        $cookieValue = $this->grabCookie($cookie, $params);
        $_COOKIE[$cookie] = $cookieValue;

        return $cookieValue;
    }

    /**
     * Login as the specified user.
     *
     * The method will **not** follow redirection, after the login, to any page.
     *
     * @example
     * ```php
     * $I->loginAs('user', 'password');
     * $I->amOnAdminPage('/');
     * $I->seeElement('.admin');
     * ```
     *
     * @param string $username The user login name.
     * @param string $password The user password in plain text.
     */
    public function loginAs($username, $password)
    {
        $this->amOnPage($this->getLoginUrl());
        $this->submitForm('#loginform', [
        'log' =>$username,
        'pwd' => $password ,
        'testcookie' => '1',
        'redirect_to' => ''
        ], '#wp-submit');
    }

    /**
     * Builds and returns an instance of the WordPress connector.
     *
     * The method will trigger the load of required Codeception library polyfills.
     *
     * @return WordPressConnector
     */
    protected function buildConnector()
    {
        return new WordPressConnector();
    }
}
