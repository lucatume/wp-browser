<?php

namespace Codeception\Module;

use function GuzzleHttp\Psr7\build_query;

trait WPBrowserMethods
{

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
     */
    public function loginAsAdmin()
    {
        return $this->loginAs($this->config['adminUsername'], $this->config['adminPassword']);
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
     */
    public function loginAs($username, $password)
    {
        $this->amOnPage($this->loginUrl);

        if (method_exists($this, 'waitForElementVisible')) {
            $this->waitForElementVisible('#loginform');
        }

        $params = ['log' => $username, 'pwd' => $password, 'testcookie' => '1', 'redirect_to' => ''];
        $this->submitForm('#loginform', $params, '#wp-submit');
    }

    protected function grabWordPressAuthCookie($pattern = null)
    {
        $pattern = $pattern ? $pattern : '/^wordpress_[a-z0-9]{32}$/';
        $cookies = $this->grabCookiesWithPattern($pattern);

        return empty($cookies) ? null : array_pop($cookies);
    }

    protected function grabWordPressLoginCookie($pattern = null)
    {
        $pattern = $pattern ? $pattern : '/^wordpress_logged_in_[a-z0-9]{32}$/';
        $cookies = $this->grabCookiesWithPattern($pattern);

        return empty($cookies) ? null : array_pop($cookies);
    }

    /**
     * In the plugin administration screen activates one or more plugins clicking the "Activate" link.
     *
     * The method will **not** handle authentication and navigation to the plugins administration page.
     *
     * @example
     * ```php
     * // Activate a plugin.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->activatePlugin('hello-dolly');
     * // Activate a list of plugins.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->activatePlugin(['hello-dolly','another-plugin']);
     * ```
     *
     * @param  string|array $pluginSlug The plugin slug, like "hello-dolly" or a list of plugin slugs.
     */
    public function activatePlugin($pluginSlug)
    {
        $plugins = (array)$pluginSlug;
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
     * In the plugin administration screen deactivate a plugin clicking the "Deactivate" link.
     *
     * The method will **not** handle authentication and navigation to the plugins administration page.
     *
     * @example
     * ```php
     * // Deactivate one plugin.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->deactivatePlugin('hello-dolly');
     * // Deactivate a list of plugins.
     * $I->loginAsAdmin();
     * $I->amOnPluginsPage();
     * $I->deactivatePlugin(['hello-dolly', 'my-plugin']);
     * ```
     *
     * @param  string|array $pluginSlug The plugin slug, like "hello-dolly", or a list of plugin slugs.
     */
    public function deactivatePlugin($pluginSlug)
    {
        foreach ((array)$pluginSlug as $plugin) {
            $option = '//*[@data-slug="' . $plugin . '"]/th/input';
            $this->scrollTo($option, 0, -40);
            $this->checkOption($option);
        }
        $this->scrollTo('select[name="action"]', 0, -40);
        $this->selectOption('action', 'deactivate-selected');
        $this->click("#doaction");
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
     */
    public function amOnPluginsPage()
    {
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
    public function amOnPagesPage()
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
     */
    public function seePluginDeactivated($pluginSlug)
    {
        $this->seePluginInstalled($pluginSlug);
        $this->seeElement("table.plugins tr[data-slug='$pluginSlug'].inactive");
    }

    /**
     * Assert a plugin is installed, no matter its activation status, in the plugin adminstration screen.
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
     */
    public function seePluginInstalled($pluginSlug)
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
     */
    public function seePluginActivated($pluginSlug)
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
     */
    public function dontSeePluginInstalled($pluginSlug)
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
     * $I->loginAsAdmin()ja
     * $I->amOnAdminPage('/');
     * $I->seeErrorMessage('.my-plugin');
     * ```
     *
     * @param array|string $classes A list of classes the notice should have other than the `.notice.notice-error` ones.
     */
    public function seeErrorMessage($classes = '')
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
     */
    public function seeWpDiePage()
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
     * $I->loginAsAdmin()ja
     * $I->amOnAdminPage('/');
     * $I->seeMessage('.missing-api-token.my-plugin');
     * ```
     *
     * @param array|string $classes A list of classes the message should have in addition to the `.notice` one.
     */
    public function seeMessage($classes = '')
    {
        $classes = (array)$classes;
        $classes = implode('.', $classes);

        $this->seeElement('.notice' . ($classes ?: ''));
    }

    /**
     * Returns WordPress default test cookie object if present.
     * @example
     * ```php
     * // Grab the default WordPress test cookie.
     * $wpTestCookie = $I->grabWordPressTestCookie();
     * // Grab a customized version of the test cookie.
     * $myTestCookie = $I->grabWordPressTestCookie('my_test_cookie');
     * ```
     *
     *
     * @param string $name Optional, overrides the default cookie name.
     *
     * @return mixed Either a cookie object or `null`.
     */
    public function grabWordPressTestCookie($name = null)
    {
        $name = $name ?: 'wordpress_test_cookie';

        return $this->grabCookie($name);
    }

    /**
     * Go to a page in the admininstration area of the site.
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
    public function amOnAdminPage($page)
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
     * @param array|string $queryVars A string or array of query variables to append to the AJAX path.
     */
    public function amOnAdminAjaxPage($queryVars = null)
    {
        $path = 'admin-ajax.php';
        if ($queryVars !== null) {
            $path = '/' . (is_array($queryVars) ? build_query($queryVars) : ltrim($queryVars, '/'));
        }
        return $this->amOnAdminPage($path);
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
     * @param array|string $queryVars A string or array of query variables to append to the AJAX path.
     */
    public function amOnCronPage($queryVars = null)
    {
        $path = '/wp-cron.php';
        if ($queryVars !== null) {
            $path = '/' . (is_array($queryVars) ? build_query($queryVars) : ltrim($queryVars, '/'));
        }
        return $this->amOnPage($path);
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
    public function amEditingPostWithId($id)
    {
        if (!is_numeric($id) && intval($id) == $id) {
            throw new \InvalidArgumentException('ID must be an int value');
        }

        $this->amOnAdminPage('/post.php?post=' . $id . '&action=edit');
    }
}
