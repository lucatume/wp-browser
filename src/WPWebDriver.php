<?php

namespace Codeception\Module;

use Codeception\Module\WebDriver;

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
    protected $requiredFields = array('adminUsername', 'adminPassword', 'adminUrl');
    /**
     * The login screen absolute URL
     *
     * @var string
     */
    protected $loginUrl;
    /**
     * The admin absolute URL.
     *
     * @var [type]
     */
    protected $adminUrl;
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
        $this->adminUrl = rtrim($this->config['adminUrl'], '/');
        $this->pluginsUrl = $this->adminUrl . '/plugins.php';
    }
}
