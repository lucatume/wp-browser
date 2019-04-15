<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException;

/**
 * An extension of Codeception WebDriver module offering specific WordPress browsing methods.
 */
class WPWebDriver extends WebDriver
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
     * @var string
     */
    protected $adminPath;

    /**
     * The plugin screen absolute URL
     *
     * @var string
     */
    protected $pluginsPath;

    protected $loginAttempt = 0;

    /**
     * Initializes the module setting the properties values.
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
     * Login as the administrator user using the credentials specified in the module configuration.
     *
     * The method will **not** follow redirection, after the login, to any page.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnAdminPage('/');
     * $I->see('Dashboard');
     * ```
     *
     * @param int    $timeout The max time, in seconds, to try to login.
     * @param int    $maxAttempts The max number of attempts to try to login.
     *
     * @throws \Codeception\Exception\ModuleException If all the attempts of obtaining the cookie fail.
     */
    public function loginAsAdmin($timeout = 10, $maxAttempts = 5)
    {
        $this->loginAs($this->config['adminUsername'], $this->config['adminPassword'], $timeout, $maxAttempts);
    }

    /**
     * Login as the specified user.
     *
     * The method will **not** follow redirection, after the login, to any page.
     * Depending on the driven browser the login might be "too fast" and the server might have not
     * replied with valid cookies yet; in that case the method will re-attempt the login to obtain
     * the cookies.
     * @example
     * ```php
     * $I->loginAs('user', 'password');
     * $I->amOnAdminPage('/');
     * $I->see('Dashboard');
     * ```
     *
     * @param string $username The user login name.
     * @param string $password The user password in plain text.
     * @param int    $timeout The max time, in seconds, to try to login.
     * @param int    $maxAttempts The max number of attempts to try to login.
     *
     * @throws \Codeception\Exception\ModuleException If all the attempts of obtaining the cookie fail.
     */
    public function loginAs($username, $password, $timeout = 10, $maxAttempts = 5)
    {
        if ($this->loginAttempt === $maxAttempts) {
            throw new ModuleException(
                __CLASS__,
                "Could not login as [{$username}, {$password}] after {$maxAttempts} attempts."
            );
        }

        $this->debug("Trying to login, attempt {$this->loginAttempt}/{$maxAttempts}...");

        $this->amOnPage($this->loginUrl);

        $this->waitForElement('#user_login', $timeout);
        $this->waitForElement('#user_pass', $timeout);
        $this->waitForElement('#wp-submit', $timeout);

        $this->fillField('#user_login', $username);
        $this->fillField('#user_pass', $password);
        $this->click('#wp-submit');

        $authCookie = $this->grabWordPressAuthCookie();
        $loginCookie = $this->grabWordPressLoginCookie();
        $empty_cookies = empty($authCookie) && empty($loginCookie);

        if ($empty_cookies) {
            $this->loginAttempt++;
            $this->wait(1);
            $this->loginAs($username, $password, $timeout, $maxAttempts);
        }

        $this->loginAttempt = 0;
    }

    /**
     * Returns all the cookies whose name matches a regex pattern.
     *
     * @example
     * ```php
     * $I->loginAs('customer','password');
     * $I->amOnPage('/shop');
     * $cartCookies = $I->grabCookiesWithPattern("#^shop_cart\\.*#");
     * ```
     *
     * @param string $cookiePattern The regular expression pattern to use for the matching.
     *
     * @return array|null An array of cookies matching the pattern.
     */
    public function grabCookiesWithPattern($cookiePattern)
    {
        $cookies = $this->webDriver->manage()->getCookies();

        if (!$cookies) {
            return null;
        }
        $matchingCookies = array_filter($cookies, function ($cookie) use ($cookiePattern) {

            return preg_match($cookiePattern, $cookie['name']);
        });
        $cookieList = array_map(function ($cookie) {
            return sprintf('{"%s": "%s"}', $cookie['name'], $cookie['value']);
        }, $matchingCookies);

        $this->debug('Cookies matching pattern ' . $cookiePattern . ' : ' . implode(', ', $cookieList));

        return is_array($matchingCookies) ? $matchingCookies : null;
    }

    /**
     * Waits for any jQuery triggered AJAX request to be resolved.
     *
     * @example
     * ```php
     * $I->amOnPage('/triggering-ajax-requests');
     * $I->waitForJqueryAjax();
     * $I->see('From AJAX');
     * ```
     *
     * @param int $time The max time to wait for AJAX requests to complete.
     */
    public function waitForJqueryAjax($time = 10)
    {
        return $this->waitForJS('return jQuery.active == 0', $time);
    }

    /**
     * Grabs the current page full URL including the query vars.
     *
     * @example
     * ```php
     * $today = date('Y-m-d');
     * $I->amOnPage('/concerts?date=' . $today);
     * $I->assertRegExp('#\\/concerts$#', $I->grabFullUrl());
     * ```
     */
    public function grabFullUrl()
    {
        return $this->executeJS('return location.href');
    }

    /**
     * @internal
     */
    protected function validateConfig()
    {
        $this->configBackCompat();

        parent::validateConfig();
    }
}
