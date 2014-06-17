<?php
namespace Codeception\Module;

use Codeception\Module\PhpBrowser;

class WPBrowser extends PhpBrowser
{
    protected $requiredFields = array('adminUsername', 'adminPassword', 'adminUrl');
    protected $loginUrl;
    protected $pluginsUrl;

    public function _initialize()
    {
        parent::_initialize();
        $this->loginUrl = str_replace('wp-admin', 'wp-login.php', $this->config['adminUrl']);
        $this->pluginsUrl = rtrim($this->config['adminUrl'], '/') . '/plugins.php';
    }

    public function loginAsAdmin() {
        $this->loginAs($this->config['adminUsername'], $this->config['adminPassword']);
    }

    public function loginAs($username, $password)
    {
        $this->amOnPage($this->loginUrl);
        $this->fillField('#user_login', $username);
        $this->fillField('#user_pass', $password);
        $this->click('#wp-submit');
    }

    public function activatePlugin($pluginSlug)
    {
        $this->click('Activate', '#' . $pluginSlug);
    }

    public function deactivatePlugin($pluginSlug)
    {
        $this->click('Deactivate', '#' . $pluginSlug);
    }


    public function amOnPluginsPage()
    {
        $this->amOnPage($this->pluginsUrl);
    }

    public function seePluginDeactivated($pluginSlug)
    {
        $this->seePluginInstalled($pluginSlug);
        $this->seeElement("#" . $pluginSlug . '.inactive');
    }

    public function seePluginActivated($pluginSlug)
    {
        $this->seePluginInstalled($pluginSlug);
        $this->seeElement("#" . $pluginSlug . '.active');
    }

    public function seePluginInstalled($pluginSlug)
    {
        $this->seeElement('#' . $pluginSlug);
    }

    public function doNotSeePluginInstalled($pluginSlug)
    {
        $this->doNotSeeElement('#' . $pluginSlug);
    }

    public function seeErrorMessage($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode('.', $classes);
        }
        $classes = '.' . $classes;
        $this->seeElement('#message.error' . $classes);
    }

    public function seeWpDiePage()
    {
        $this->seeElement('body#error-page');
    }

    public function seeMessage($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode('.', $classes);
        }
        $classes = '.' . $classes;
        $this->seeElement('#message.updated' . $classes);
    }
}
