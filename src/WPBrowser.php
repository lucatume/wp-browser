<?php

namespace Codeception\Module;

use Codeception\Module\PhpBrowser;
    
/**
 * A Codeception module offering specific WordPress browsing methods.
 */
class WPBrowser extends PhpBrowser
{
    /**
     * The module required fields, to be set in the suite .yml configuration file.
     *
     * @var array
     */
    protected $requiredFields = array('adminUsername', 'adminPassword', 'adminUrl');
    /**
     * The login screen absolute URL
     *
     * @var string
     */
    protected $loginUrl;
    /**
     * The plugin screen absolute URL
     *
     * @var string
     */
    protected $pluginsUrl;

    /**
     * Initializes the module setting the properties values.
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->loginUrl = str_replace('wp-admin', 'wp-login.php', $this->config['adminUrl']);
        $this->pluginsUrl = rtrim($this->config['adminUrl'], '/') . '/plugins.php';
    }

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
}
