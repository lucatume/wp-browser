<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Facebook\WebDriver\Cookie as FacebookWebdriverCookie;
use JsonException;
use Symfony\Component\BrowserKit\Cookie;

trait WPBrowserMethods
{
    /**
     * The plugin screen absolute URL.
     *
     * @var string|null
     */
    protected $pluginsPath;

    /**
     * The admin UI path, relative to the WordPress installation root URL.
     *
     * @var string
     */
    protected $adminPath = '/wp-admin';

    /**
     * The login screen absolute URL
     *
     * @var string
     */
    protected $loginUrl;

    /**
     * Navigate to the default WordPress logout page and click the logout link.
     *
     * @example
     * ```php
     * // Log out using the `wp-login.php` form and return to the current page.
     * $I->logOut(true);
     * // Log out using the `wp-login.php` form and remain there.
     * $I->logOut(false);
     * // Log out using the `wp-login.php` form and move to another page.
     * $I->logOut('/some-other-page');
     * ```
     *
     * @param bool|string $redirectTo Whether to redirect to another (optionally specified) page after the logout.
     *
     * @throws ModuleException IF the current URI cannot be retrieved from the inner browser.
     */
    public function logOut($redirectTo = false): void
    {
        $previousUri = $this->_getCurrentUri();
        $loginUri = $this->getLoginUrl();
        $this->amOnPage($loginUri . '?action=logout');
        // Use XPath to have a better performance and find the link in any language.
        $this->click("//a[contains(@href,'action=logout')]");
        $this->seeInCurrentUrl('loggedout=true');
        if ($redirectTo) {
            $redirectUri = $redirectTo === true ? $previousUri : $redirectTo;
            $this->amOnPage($redirectUri);
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
     * @throws ModuleException
     */
    public function loginAsAdmin(): void
    {
        /** @var array{adminUsername: string, adminPassword: string} $config */
        $config = $this->config;
        $this->loginAs($config['adminUsername'], $config['adminPassword']);
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
     * $I->see('Dashboard');
     * ```
     *
     * @param string $username The user login name.
     * @param string $password The user password in plain text.
     * @throws JsonException
     */
    public function loginAs(string $username, string $password): void
    {
        $this->amOnPage($this->loginUrl);

        if (method_exists($this, 'waitForElementVisible')) {
            $this->waitForElementVisible('#loginform');
        }

        $params = ['log' => $username, 'pwd' => $password, 'testcookie' => '1', 'redirect_to' => ''];
        $this->submitForm('#loginform', $params, '#wp-submit');
    }

    /**
     * Initializes the module setting the properties values.
     */
    public function _initialize(): void
    {
        parent::_initialize();

        /** @var array{adminPath: string} $config */
        $config = $this->config;
        $adminPath = $config['adminPath'];
        $this->loginUrl = str_replace('wp-admin', 'wp-login.php', $adminPath);
        $this->adminPath = rtrim($adminPath, '/');
        $this->pluginsPath = $this->adminPath . '/plugins.php';
    }

    /**
     * Returns the WordPress authentication cookie.
     *
     * @param string|null $pattern The pattern to filter the cookies by.
     *
     * @return FacebookWebdriverCookie|Cookie|null The WordPress authorization cookie or `null` if not found.
     */
    protected function grabWordPressAuthCookie(?string $pattern = null)
    {
        $pattern = $pattern ?: '/^wordpress_[a-z0-9]{32}$/';
        $cookies = $this->grabCookiesWithPattern($pattern);

        return empty($cookies) ? null : array_pop($cookies);
    }

    /**
     * Returns the WordPress login cookie.
     *
     * @param string|null $pattern The pattern to filter the cookies by.
     *
     * @return FacebookWebdriverCookie|Cookie|null The WordPress login cookie or `null` if not found.
     */
    protected function grabWordPressLoginCookie(?string $pattern = null)
    {
        $pattern = $pattern ?: '/^wordpress_logged_in_[a-z0-9]{32}$/';
        $cookies = $this->grabCookiesWithPattern($pattern);

        return empty($cookies) ? null : array_pop($cookies);
    }

    /**
     * Go to the plugins administration screen.
     *
     *  The method will **not** handle authentication.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->activatePlugin('hello-dolly');
     * ```
     *
     *
     * @throws ModuleException If the class `pluginsPath` property is not set when the method is called.
     */
    public function amOnPluginsPage(): void
    {
        if (!isset($this->pluginsPath)) {
            throw new ModuleException($this, 'Plugins path is not set.');
        }
        $this->amOnPage($this->pluginsPath);
    }

    /**
     * Go the "Pages" administration screen.
     *
     * The method will **not** handle authentication.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnPagesPage();
     * $I->see('Add New');
     * ```
     */
    public function amOnPagesPage(): void
    {
        $this->amOnPage($this->adminPath . '/edit.php?post_type=page');
    }

    /**
     * Assert a plugin is not activated in the plugins administration screen.
     *
     * The method will **not** handle authentication and navigation to the plugin administration screen.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->seePluginDeactivated('my-plugin');
     * ```
     *
     * @param string $pluginSlug The plugin slug, like "hello-dolly".
     *
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function seePluginDeactivated(string $pluginSlug): void
    {
        $this->seePluginInstalled($pluginSlug);
        $this->seeElement("table.plugins tr[data-slug='$pluginSlug'].inactive");
    }

    /**
     * Assert a plugin is installed, no matter its activation status, in the plugin administration screen.
     *
     * The method will **not** handle authentication and navigation to the plugin administration screen.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->seePluginInstalled('my-plugin');
     * ```
     *
     * @param string $pluginSlug The plugin slug, like "hello-dolly".
     *
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function seePluginInstalled(string $pluginSlug): void
    {
        $this->seeElement("table.plugins tr[data-slug='$pluginSlug']");
    }

    /**
     * Assert a plugin is activated in the plugin administration screen.
     *
     * The method will **not** handle authentication and navigation to the plugin administration screen.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->seePluginActivated('my-plugin');
     * ```
     *
     * @param string $pluginSlug The plugin slug, like "hello-dolly".
     *
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function seePluginActivated(string $pluginSlug): void
    {
        $this->seePluginInstalled($pluginSlug);
        $this->seeElement("table.plugins tr[data-slug='$pluginSlug'].active");
    }

    /**
     * Assert a plugin is not installed in the plugins administration screen.
     *
     * The method will **not** handle authentication and navigation to the plugin administration screen.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->dontSeePluginInstalled('my-plugin');
     * ```
     *
     * @param string $pluginSlug The plugin slug, like "hello-dolly".
     *
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function dontSeePluginInstalled(string $pluginSlug): void
    {
        $this->dontSeeElement("table.plugins tr[data-slug='$pluginSlug']");
    }

    /**
     * In an administration screen look for an error admin notice.
     *
     * The check is class-based to decouple from internationalization.
     * The method will **not** handle authentication and navigation the administration area.
     *
     * @example
     * ```php
     * $I->loginAsAdmin()
     * $I->amOnAdminPage('/');
     * $I->seeErrorMessage('.my-plugin');
     * ```
     *
     * @param string|array<string> $classes A list of classes the notice should have other than the
     *                                      `.notice.notice-error` ones.
     *
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function seeErrorMessage($classes = ''): void
    {
        $classes = (array)$classes;
        $classes = implode('.', $classes);

        $this->seeElement('.notice.notice-error' . ($classes ?: ''));
    }

    /**
     * Checks that the current page is one generated by the `wp_die` function.
     *
     * The method will try to identify the page based on the default WordPress die page HTML attributes.
     *
     * @example
     * ```php
     * $I->loginAs('user', 'password');
     * $I->amOnAdminPage('/forbidden');
     * $I->seeWpDiePage();
     * ```
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function seeWpDiePage(): void
    {
        $this->seeElement('body#error-page');
    }

    /**
     * In an administration screen look for an admin notice.
     *
     * The check is class-based to decouple from internationalization.
     * The method will **not** handle authentication and navigation the administration area.
     *
     * @example
     * ```php
     * $I->loginAsAdmin()
     * $I->amOnAdminPage('/');
     * $I->seeMessage('.missing-api-token.my-plugin');
     * ```
     *
     * @param array<string>|string $classes A list of classes the message should have in addition to the `.notice` one.
     *
     * @throws JsonException If there's any issue stringifying the selector.
     */
    public function seeMessage($classes = ''): void
    {
        $classes = (array)$classes;
        $classes = implode('.', $classes);

        $this->seeElement('.notice' . ($classes ?: ''));
    }

    /**
     * Returns WordPress default test cookie object if present.
     *
     * @example
     * ```php
     * // Grab the default WordPress test cookie.
     * $wpTestCookie = $I->grabWordPressTestCookie();
     * // Grab a customized version of the test cookie.
     * $myTestCookie = $I->grabWordPressTestCookie('my_test_cookie');
     * ```
     *
     * @param string|null $name Optional, overrides the default cookie name.
     *
     * @return Cookie|null Either a cookie object or `null`.
     */
    public function grabWordPressTestCookie(?string $name = null): ?Cookie
    {
        $name = $name ?: 'wordpress_test_cookie';

        $matching = $this->grabCookiesWithPattern('/^' . preg_quote($name, '/') . '$/');

        if ($matching && is_array($matching)) {
            return $matching[0];
        }

        return null;
    }

    /**
     * Go to a page in the administration area of the site.
     *
     * This method will **not** handle authentication to the administration area.
     *
     * @example
     *
     * ```php
     * $I->loginAs('user', 'password');
     * // Go to the plugins management screen.
     * $I->amOnAdminPage('/plugins.php');
     * ```
     *
     * @param string $page The path, relative to the admin area URL, to the page.
     */
    public function amOnAdminPage(string $page): void
    {
        $this->amOnPage($this->adminPath . '/' . ltrim($page, '/'));
    }

    /**
     * Go to the `admin-ajax.php` page to start a synchronous, and blocking, `GET` AJAX request.
     *
     * The method will **not** handle authentication, nonces or authorization.
     *
     * @example
     * ```php
     * $I->amOnAdminAjaxPage(['action' => 'my-action', 'data' => ['id' => 23], 'nonce' => $nonce]);
     * ```
     *
     * @param string|array<string,mixed> $queryVars A string or array of query variables to append to the AJAX path.
     */
    public function amOnAdminAjaxPage($queryVars = null): void
    {
        $path = 'admin-ajax.php';
        if ($queryVars !== null) {
            $path .= '?' . (is_array($queryVars) ? http_build_query($queryVars) : ltrim($queryVars, '?'));
        }

        $this->amOnAdminPage($path);
    }

    /**
     * Go to the cron page to start a synchronous, and blocking, `GET` request to the cron script.
     *
     * @example
     * ```php
     * // Triggers the cron job with an optional query argument.
     * $I->amOnCronPage('/?some-query-var=some-value');
     * ```
     *
     * @param string|array<string,mixed> $queryVars A string or array of query variables to append to the Cron path.
     */
    public function amOnCronPage($queryVars = null): void
    {
        $path = 'wp-cron.php';
        if ($queryVars !== null) {
            $path .= '?' . (is_array($queryVars) ? http_build_query($queryVars) : ltrim($queryVars, '?'));
        }

        $this->amOnPage($path);
    }

    /**
     * Go to the admin page to edit the post with the specified ID.
     *
     * The method will **not** handle authentication the admin area.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $postId = $I->havePostInDatabase();
     * $I->amEditingPostWithId($postId);
     * $I->fillField('post_title', 'Post title');
     * ```
     *
     * @param int $id The post ID.
     */
    public function amEditingPostWithId(int $id): void
    {
        $this->amOnAdminPage('/post.php?post=' . $id . '&action=edit');
    }

    /**
     * Configures for back-compatibility.
     */
    protected function configBackCompat(): void
    {
        if (isset($this->config['adminUrl']) && !isset($this->config['adminPath'])) {
            $this->config['adminPath'] = $this->config['adminUrl'];
        }
    }

    /**
     * Sets the admin path.
     *
     * @param string $adminPath The admin path.
     */
    protected function setAdminPath(string $adminPath): void
    {
        $this->adminPath = $adminPath;
    }

    /**
     * Returns the admin path.
     *
     * @return string The admin path.
     */
    protected function getAdminPath(): string
    {
        return $this->adminPath;
    }

    /**
     * Sets the login URL.
     *
     * @param string $loginUrl The login URL.
     */
    protected function setLoginUrl(string $loginUrl): void
    {
        $this->loginUrl = $loginUrl;
    }

    /**
     * Returns the login URL.
     *
     * @return string The login URL.
     */
    private function getLoginUrl(): string
    {
        return $this->loginUrl;
    }

    /**
     * Validates the module configuration.
     *
     * @throws ModuleConfigException|ModuleException If there's any issue.
     */
    protected function validateConfig(): void
    {
        $this->configBackCompat();

        parent::validateConfig();

        foreach (['adminUsername', 'adminPassword', 'adminPath'] as $param) {
            if (!is_string($this->config[$param])) {
                throw new ModuleConfigException($this, "Configuration parameter $param must be a string.");
            }
        }
    }

    /**
     * Go to the admin page to edit the user with the specified ID.
     *
     * The method will **not** handle authentication the admin area.
     *
     * @example
     * ```php
     * $I->loginAsAdmin();
     * $userId = $I->haveUserInDatabase('luca', 'editor');
     * $I->amEditingUserWithId($userId);
     * $I->fillField('email', 'new@example.net');
     * ```
     *
     * @param int $id The user ID.
     *
     * @return void
     */
    public function amEditingUserWithId(int $id): void
    {
        $this->amOnAdminPage('/user-edit.php?user_id=' . $id);
    }
}
