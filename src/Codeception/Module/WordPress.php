<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\ModuleContainer;
use Codeception\TestInterface;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;
use tad\WPBrowser\Filesystem\Utils;

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
     * @var \tad\WPBrowser\Connector\WordPress
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
     * @var string
     */
    protected $adminPath;

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
     * @var string
     */
    protected $loginUrl = '';

    /**
     * @var string The string that will hold the response content after each request handling.
     */
    public $response = '';

    /**
     * WordPress constructor.
     *
     * @param \Codeception\Lib\ModuleContainer        $moduleContainer
     * @param array                                   $config
     * @param \tad\WPBrowser\Connector\WordPress|null $client
     *
     * @throws \Codeception\Exception\ModuleConfigException
     */
    public function __construct(ModuleContainer $moduleContainer, $config = [], WordPressConnector $client = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->ensureWpRoot();
        $this->adminPath = $this->config['adminPath'];
        $this->client    = $client;
    }

    private function ensureWpRoot()
    {
        $wpRootFolder = $this->getWpRootFolder();
        if (!file_exists($wpRootFolder . '/wp-settings.php')) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nThe path `{$wpRootFolder}` is not pointing to a valid WordPress installation folder."
            );
        }
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
        $this->loginUrl = '/wp-login.php';
        $this->setupClient($wpdb->getSiteDomain());
    }

    private function setupClient($siteDomain)
    {
        $this->client = $this->client ? $this->client : new WordPressConnector();
        $this->client->setUrl($this->siteUrl);
        $this->client->setDomain($siteDomain);
        $this->client->setRootFolder($this->config['wpRootFolder']);
        $this->client->followRedirects(true);
        $this->client->resetCookies();
        $this->setCookiesFromOptions();
    }

    /**
     * @param $client
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
     * Will this comment show up in the output?
     * And can I use <code>HTML</code> tags? Like <em>this</em> <stron>one</strong>?
     * Or **Markdown** tags? *Please...*
     *
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
        $page         = $this->adminPath . '/' . $preparedPage;
        return $this->amOnPage($page);
    }

    private function preparePage($page)
    {
        $page = $this->untrailslashIt($page);
        $page = empty($page) || preg_match('~\\/?index\\.php\\/?~', $page) ? '/' : $page;

        return $page;
    }

    private function untrailslashIt($path)
    {
        $path = preg_replace('~\\/?$~', '', $path);
        return $path;
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
     */
    public function amOnPage($page)
    {
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

        return null;
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
        return 0 === strpos($page, $this->adminPath);
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
        $internalDomains[] = '/^' . preg_quote(parse_url($this->siteUrl, PHP_URL_HOST), '/') . '$/';
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
     */
    public function getWpRootFolder()
    {
        if (empty($this->wpRootFolder)) {
            // allow me not to bother with trailing slashes
            $wpRootFolder = Utils::untrailslashit($this->config['wpRootFolder']) . DIRECTORY_SEPARATOR;

            // maybe the user is using the `~` symbol for home?
            $this->wpRootFolder = Utils::homeify($wpRootFolder);

            // remove `\ ` spaces in folder paths
            $this->wpRootFolder = str_replace('\ ', ' ', $this->wpRootFolder);
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
        $this->amOnPage($this->loginUrl);
        $this->submitForm('#loginform', [
        'log' =>$username,
        'pwd' => $password ,
        'testcookie' => '1',
        'redirect_to' => ''
        ], '#wp-submit');
    }
}
