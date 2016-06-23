<?php

namespace Codeception\Module;

/**
 * A Codeception module offering specific WordPress browsing methods.
 */
class WPWebDriver extends WebDriver
{
    use WPBrowserMethods;
    /**
     * The module required fields, to be set in the suite .yml configuration file.
     *
     * @var array
     */
    protected $requiredFields = array('adminUsername', 'adminPassword', 'adminPath');

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

    /**
     * Initializes the module setting the properties values.
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
        $adminPath = isset($this->config['adminPath']) ? $this->config['adminPath'] : $this->config['adminUrl'];
        $this->loginUrl = str_replace('wp-admin', 'wp-login.php', $adminPath);
        $this->adminPath = rtrim($adminPath, '/');
        $this->pluginsPath = $this->adminPath . '/plugins.php';
    }

    /**
     * Returns all the cookies whose name matches a regex pattern.
     *
     * @param string $cookiePattern
     *
     * @return array|null
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
     * @param int $time
     */
    public function waitForJqueryAjax($time = 10)
    {
        return $this->waitForJS('return jQuery.active == 0', $time);
    }

    /**
     * Grabs the current page full URL including the query vars.
     */
    public function grabFullUrl()
    {
        return $this->executeJS('return location.href');
    }

}
