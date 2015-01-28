<?php

namespace Codeception\Module;

trait WPBrowserMethods
{
    /**
     * Goes to the login page and logs in as the site admin.
     *
     * @return void
     */
    public function loginAsAdmin()
    {
        $this->loginAs($this->config['adminUsername'], $this->config['adminPassword']);
    }

    /**
     * Goes to the login page and logs in using the given credentials.
     * @param string $username
     * @param string $password
     * @return void
     */
    public function loginAs($username, $password)
    {
        $this->amOnPage($this->loginUrl);
        $this->fillField('#user_login', $username);
        $this->fillField('#user_pass', $password);
        $this->click('#wp-submit');
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
    public function activatePlugin($pluginSlug)
    {
        $this->click('Activate', '#' . $pluginSlug);
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
    public function deactivatePlugin($pluginSlug)
    {
        $this->click('Deactivate', '#' . $pluginSlug);
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
        $this->amOnPage($this->pluginsUrl);
    }

    /**
     * Navigates the browser to the Pages administration screen.
     *
     * Makes no check about the user being logged in and authorized to do so.
     *
     * @return void
     */
    public function amOnPagesPage(){
        $this->amOnPage($this->adminUrl . '/edit.php?post_type=page');
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
        $this->seeElement("#" . $pluginSlug . '.inactive');
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
        $this->seeElement("#" . $pluginSlug . '.active');
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
        $this->seeElement('#' . $pluginSlug);
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
        $this->dontSeeElement('#' . $pluginSlug);
    }

    /**
     * In an administration screen will look for an error message.
     *
     * Allows for class-based error checking to decouple from internationalization.
     *
     * @param array $classes A list of classes the error notice should have.
     * @return void
     */
    public function seeErrorMessage($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode('.', $classes);
        }
        $classes = '.' . $classes;
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
     * @param array $classes A list of classes the message should have.
     * @return void
     */
    public function seeMessage($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode('.', $classes);
        }
        $classes = '.' . $classes;
        $this->seeElement('#message.updated' . $classes);
    }

    /**
     * Retrieves and stores WordPress auth and test cookies.
     *
     * The function will not leave them set though
     *
     * @param null $username
     * @param null $password
     */
    public function storeWpAuthCookies( $username = null, $password = null ) {
        if ( ! ( is_string( $username ) && ! is_string( $password ) ) ) {
            $this->loginAsAdmin();
        } else {
            $this->loginAs( $username, $password );
        }

        $this->setWpLoggedInCookie( $this->grabWpLoggedInCookie() );
        $this->setWpAuthCookie( $this->grabWpAuthCookie() );
        $this->setWpTestCookie( $this->grabWpTestCookie() );

        /**
         * @var CookieJar
         */
        $cookieJar = $this->client->getCookieJar();
        $cookieJar->clear();
    }

    /**
     * Stores and sets the WordPress auth and test cookies
     *
     * If no username and password are passed will login as admin.
     *
     * @param null $username Optional username for the user to log in
     * @param null $password Optional password for the user to log in
     */
    public function setWpAuthCookies( $username = null, $password = null ) {
        $this->storeWpAuthCookies( $username, $password );
        $cookies = $this->getWpCookies();
        foreach ( $cookies as $cookie ) {
            $this->setCookie( $cookie->getName(), $cookie->getValue() );
        }
    }

    /**
     * Returns WordPress test cookie.
     *
     * @return Cookie|null
     */
    public function grabWpTestCookie() {
        return $this->grabCookie( 'wordpress_test_cookie' );
    }

    /**
     * Returns WordPress logged_in cookie.
     *
     * @return Cookie|null
     */
    public function grabWpLoggedInCookie() {
        $cookiePattern = '/^wordpress_logged_in_[a-zA-Z0-9]{32}$/';
        $cookie = $this->grabCookieWithPattern( $cookiePattern );

        return is_array( $cookie ) ? array_pop( $cookie ) : null;
    }

    /**
     * Returns WordPress auth cookie.
     *
     * @return Cookie|null
     */
    public function grabWpAuthCookie() {
        $cookiePattern = '/^wordpress_[a-zA-Z0-9]{32}$/';
        $cookie = $this->grabCookieWithPattern( $cookiePattern );

        return is_array( $cookie ) ? array_pop( $cookie ) : null;
    }

    /**
     * Returns the value of a cookie with a name matching a pattern.
     *
     * @param $cookiePattern
     *
     * @return Cookie|null
     */
    public function grabCookieWithPattern( $cookiePattern ) {
        /**
         * @var Cookie[]
         */
        $cookies = $this->client->getCookieJar()->all();
        if ( ! $cookies ) {
            return null;
        }
        $matchingCookies = array_filter( $cookies, function ( Cookie $cookie ) use ( $cookiePattern ) {

            return preg_match( $cookiePattern, $cookie->getName() );
        } );
        $cookieList = array_map( function ( Cookie $cookie ) {
            return sprintf( '{"%s": "%s"}', $cookie->getName(), $cookie->getValue() );
        }, $matchingCookies );
        $this->debug( 'Cookies matching pattern ' . $cookiePattern . ' : ' . implode( ', ', $cookieList ) );

        return is_array( $matchingCookies ) ? $matchingCookies : null;
    }

    /**
     * @return Cookie
     */
    public function getWpLoggedInCookie() {
        return $this->wpLoggedInCookie;
    }

    /**
     * @param Cookie $wpLoggedInCookie
     */
    public function setWpLoggedInCookie( Cookie $wpLoggedInCookie = null ) {
        $this->wpLoggedInCookie = $wpLoggedInCookie;
    }

    /**
     * @return Cookie
     */
    public function getWpAuthCookie() {
        return $this->wpAuthCookie;
    }

    /**
     * @param Cookie $wpAuthCookie
     */
    public function setWpAuthCookie( Cookie $wpAuthCookie = null ) {
        $this->wpAuthCookie = $wpAuthCookie;
    }

    /**
     * @return Cookie
     */
    public function getWpTestCookie() {
        return $this->wpTestCookie;
    }

    /**
     * @param Cookie $wpTestCookie
     */
    public function setWpTestCookie( Cookie $wpTestCookie = null ) {
        $this->wpTestCookie = $wpTestCookie;
    }

    /**
     * Gets the WordPress cookies stored if any.
     *
     * @return Cookie[]
     */
    public function getWpCookies() {
        $cookies = [
            $this->getWpLoggedInCookie(),
            $this->getWpAuthCookie(),
            $this->getWpTestCookie()
        ];

        return $cookies;
    }
}