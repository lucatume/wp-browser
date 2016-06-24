<?php

/*
 * Call order is this:
 * 
 * [12-Jun-2016 12:32:51 UTC] Codeception\Module\WordPress::__construct
 * [12-Jun-2016 12:32:51 UTC] Codeception\Module\WordPress::_initialize
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_beforeSuite
 * 
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_cleanup
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_before
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_beforeStep
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_afterStep
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_after
 * 
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_cleanup
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_before
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_beforeStep
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_afterStep
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_after
 * 
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_afterSuite
*/

// @todo: add a note in docs that _after and _before methods should call the parent!

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Step;
use Codeception\TestInterface;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;

class WordPress extends Framework implements DependsOnModule
{
    /**
     * @var \tad\WPBrowser\Connector\WordPress
     */
    public $client;

    /**
     * @var array
     */
    protected $requiredFields = ['adminUsername', 'adminPassword'];

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
    protected $dependencyMessage = <<< EOF
Example configuring WPDb
--
modules
    enabled:
        - WPDb:
            dsn: 'mysql:host=localhost;dbname=wp'
            user: 'root'
            password: 'root'
            dump: 'tests/_data/dump.sql'
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
     * WordPress constructor.
     * @param ModuleContainer $moduleContainer
     * @param array $config
     */
    public function __construct(ModuleContainer $moduleContainer, $config = [])
    {
        parent::__construct($moduleContainer, $config);
        $this->ensureWpRoot();
        $this->adminPath = $this->config['adminPath'];
    }

    private function ensureWpRoot()
    {
        $wpRootFolder = $this->config['wpRootFolder'];
        if (!file_exists($wpRootFolder . DIRECTORY_SEPARATOR . 'wp-settings.php')) {
            throw new ModuleConfigException(__CLASS__, "\nThe path `{$wpRootFolder}` is not pointing to a valid WordPress installation folder.");
        }
    }

    public function _initialize()
    {
    }

    public function _before(TestInterface $test)
    {
        $this->client = $this->client ?: new WordPressConnector();
        $this->client->followRedirects(true);
    }

    public function _cleanup()
    {
    }

    public function _beforeSuite($settings = [])
    {
    }

    public function _afterSuite()
    {
    }

    public function _beforeStep(Step $step)
    {
    }

    public function _afterStep(Step $step)
    {
    }

    public function _failed(TestInterface $test, $fail)
    {
    }

    public function _after(TestInterface $test)
    {
    }

    public function _setClient($client)
    {
        $this->client = $client;
    }

    public function _isMockRequest($isMockRequest = false)
    {
        $this->isMockRequest = $isMockRequest;
    }

    public function setAdminPath($adminPath)
    {
        $this->adminPath = $adminPath;
    }

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

    public function _inject(WPDb $wpdbModule)
    {
        $this->wpdbModule = $wpdbModule;
    }

    public function amOnAdminAjaxPage()
    {
        return $this->amOnAdminPage('admin-ajax.php');
    }

    public function amOnAdminPage($page)
    {
        $page = $page === '/' || $page === '' ? 'index.php' : $page;
        return $this->amOnPage($this->adminPath . '/' . ltrim($page, '/'));
    }

    /**
     * @param string $page The relative path to a page.
     *
     * @return null|string
     */
    public function amOnPage($page)
    {
        if ($this->isAdminPageRequest($page)) {
            $this->lastRequestWasAdmin = true;
        } else {
            if (empty($page) || $page === '/') {
                $page = '/index.php';
            }
            $this->lastRequestWasAdmin = false;
        }

        if ($this->isMockRequest) {
            return $page;
        }

        $parts = parse_url($page);
        $parameters = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $parameters);
        }

        $this->client->setIndex($this->config['wpRootFolder'] . $page);

        $this->_loadPage('GET', $page, $parameters);

        return null;
    }

    private function isAdminPageRequest($page)
    {
        return 0 === strpos($page, $this->adminPath);
    }

    public function amOnCronPage()
    {
        return $this->amOnPage('/wp-cron.php');
    }

}
