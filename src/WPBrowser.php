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
        $this->fillField('Username', $username);
        $this->fillField('Password', $password);
        $this->click('Log In');
    }

    public function activatePlugin($pluginSlug)
    {
        $I->click('Activate', '#' . $pluginSlug);
    }

    public function deactivatePlugin($pluginSlug)
    {
        $I->click('Deactivate', '#' . $pluginSlug);
    }


    public function amOnPluginsPage()
    {
        $this->amOnPage($this->pluginsUrl);
    }

    public function seePluginDeactivated($pluginSlug)
    {
        $I->seePluginInstalled($pluginSlug);
        $I->seeElement("#" . $pluginSlug . '.inactive');
    }

    public function seePluginActivated($pluginSlug)
    {
        $I->seePluginInstalled($pluginSlug);
        $I->seeElement("#" . $pluginSlug . '.active');
    }

    public function seePluginInstalled($pluginSlug)
    {
        $I->seeElement('#' . $pluginSlug);
    }

    public function doNotSeePluginInstalled($pluginSlug)
    {
        $I->doNotSeeElement('#' . $pluginSlug);
    }

    public function seeErrorMessage($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode('.', $classes);
        }
        $classes = '.' . $classes;
        $I->seeElement('#message.error' . $classes);
    }

    public function seeWpDiePage()
    {
        $I->seeElement('body#error-page');
    }

    public function seeMessage($classes = '')
    {
        if (is_array($classes)) {
            $classes = implode('.', $classes);
        }
        $classes = '.' . $classes;
        $I->seeElement('#message.updated' . $classes);
    }
}
