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
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Step;
use Codeception\TestInterface;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;
use tad\WPBrowser\Module\Support\Submodules;
use tad\WPBrowser\Module\Support\WPFacade;
use tad\WPBrowser\Module\Support\WPFacadeInterface;

class WordPress extends Framework
{
    /**
     * @var \tad\WPBrowser\Connector\WordPress
     */
    public $client;
    /**
     * @var string The absolute path to the index file that should be loaded to handle requests.
     */
    protected $index;
    /**
     * @var array
     */
    protected $requiredFields = array('wpRootFolder', 'adminUsername', 'adminPassword');
    /**
     * @var array
     */
    protected $config = array(
        'tablePrefix' => 'wp_',
        'reconnect' => false,
        'dump' => null
    );
    /**
     * @var WPLoader
     */
    protected $bootstrapper;
    /**
     * @var WPFacadeInterface
     */
    protected $wp;
    /**
     * @var string
     */
    protected $adminPath;
    /**
     * @var string
     */
    protected $adminIndex;
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
    protected $ajaxIndex;
    /**
     * @var string
     */
    protected $cronIndex;

    /**
     * @var Submodules
     */
    protected $submodules;

    /**
     * WordPress constructor.
     * @param ModuleContainer $moduleContainer
     * @param array $config
     * @param WPDb|null $wpdb
     * @param WPBootstrapper|null $bootstrapper
     * @param WPFacadeInterface|null $wp
     */
    public function __construct(ModuleContainer $moduleContainer,
                                $config = [],
                                Submodules $submodules = null,
                                WPFacadeInterface $wp = null
    )
    {
        $config = array_merge($this->config, (array)$config);

        $config['populate'] = true;
        $config['cleanup'] = true;

        parent::__construct($moduleContainer, $config);

        $this->submodules = $submodules ? $submodules : new Submodules([WPBootstrapper::class], $this->moduleContainer, $this->config);
        $this->wp = $wp ? $wp : new WPFacade();

        $this->setIndexFromConfig();
        $this->setAdminIndexFromConfig();
        $this->setAjaxIndexFromConfig();
        $this->setCronIndexFromConfig();
    }

    private function setIndexFromConfig()
    {
        if (empty($this->config['index'])) {
            return;
        }

        if (!file_exists($this->config['index'])) {
            throw new ModuleConfigException(__CLASS__, 'Index file [' . $this->config['index'] . '] does not exist.');
        }
        $this->index = $this->config['index'];
    }

    private function setAdminIndexFromConfig()
    {
        if (empty($this->config['adminIndex'])) {
            return;
        }

        if (!file_exists($this->config['adminIndex'])) {
            throw new ModuleConfigException(__CLASS__, 'Admin index file [' . $this->config['adminIndex'] . '] does not exist.');
        }
        $this->adminIndex = $this->config['adminIndex'];
    }

    private function setAjaxIndexFromConfig()
    {
        if (empty($this->config['ajaxIndex'])) {
            return;
        }

        if (!file_exists($this->config['ajaxIndex'])) {
            throw new ModuleConfigException(__CLASS__, 'Ajax index file [' . $this->config['ajaxIndex'] . '] does not exist.');
        }
        $this->ajaxIndex = $this->config['ajaxIndex'];
    }

    private function setCronIndexFromConfig()
    {
        if (empty($this->config['cronIndex'])) {
            return;
        }

        if (!file_exists($this->config['cronIndex'])) {
            throw new ModuleConfigException(__CLASS__, 'Cron index file [' . $this->config['cronIndex'] . '] does not exist.');
        }
        $this->cronIndex = $this->config['cronIndex'];
    }

    public function _initialize()
    {
//        $wpdbConfig = [
//            'dsn' => $this->config['dsn'],
//            'user' => $this->config['user'],
//            'password' => $this->config['password'],
//            'populate' => true,
//            'cleanup' => true,
//            'tablePrefix' => $this->config['tablePrefix'],
//            'reconnect' => $this->config['reconnect'],
//            'dump' => $this->config['dump'],
//            'url' => $this->config['url']
//        ];
        $wpBootstrapperConfig = [
            'wpRootFolder' => $this->config['wpRootFolder']
        ];

//        $this->submodules->addModuleConfig('WPDb', $wpdbConfig);
//        $this->submodules->initializeModule('WPDb');
        $this->submodules->addModuleConfig('WPBootstrapper', $wpBootstrapperConfig);
        $this->submodules->initializeModule('WPBootstrapper', ['bootstrapWp']);

        $this->adminPath = $this->wp->getAdminPath();

        $this->setIndexFile();
        $this->setAdminIndexFile();
        $this->setAjaxIndexFile();
        $this->setCronIndexFile();
    }

    private function setIndexFile()
    {
        if (empty($this->index)) {
            $this->index = rtrim($this->config['wpRootFolder'], '/') . '/index.php';
            if (!file_exists($this->index)) {
                throw new ModuleConfigException(__CLASS__, 'Index file [' . $this->index . '] does not exist.');
            }
        }

    }

    private function setAdminIndexFile()
    {
        if (empty($this->adminIndex)) {
            $this->adminIndex = rtrim($this->config['wpRootFolder'], '/') . $this->adminPath . '/index.php';
            if (!file_exists($this->adminIndex)) {
                throw new ModuleConfigException(__CLASS__, 'Admin index file [' . $this->adminIndex . '] does not exist.');
            }
        }
    }

    private function setAjaxIndexFile()
    {
        if (empty($this->ajaxIndex)) {
            $this->ajaxIndex = rtrim($this->config['wpRootFolder'], '/') . $this->adminPath . '/admin-ajax.php';
            if (!file_exists($this->ajaxIndex)) {
                throw new ModuleConfigException(__CLASS__, 'Ajax index file [' . $this->ajaxIndex . '] does not exist.');
            }
        }
    }

    private function setCronIndexFile()
    {
        if (empty($this->cronIndex)) {
            $this->cronIndex = rtrim($this->config['wpRootFolder'], '/') . '/wp-cron.php';
            if (!file_exists($this->cronIndex)) {
                throw new ModuleConfigException(__CLASS__, 'Cron index file [' . $this->cronIndex . '] does not exist.');
            }
        }
    }

    public function _before(TestInterface $test)
    {
        $this->client = $this->client ?: new WordPressConnector();
        $this->client->followRedirects(true);
        $this->client->setIndex($this->index);
    }

    /**
     * @param string $page The relative path to a page.
     *
     * @return null|string
     */
    public function amOnPage($page)
    {
        if ($this->isAdminPageRequest($page)) {
            $this->client->setIndex($this->adminIndex);
            $this->lastRequestWasAdmin = true;
        } else {
            $this->client->setIndex($this->index);
            $this->lastRequestWasAdmin = false;
        }

        $parts = parse_url($page);
        $parameters = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $parameters);
        }

        if ($this->isMockRequest) {
            return $page;
        }

        $this->_loadPage('GET', $page, $parameters);

        return null;
    }

    private function isAdminPageRequest($page)
    {
        return 0 === strpos($page, $this->adminPath);
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

    /**
     * @return \WP_UnitTest_Factory
     */
    public function factory()
    {
    }

    public function setPermalinkStructure($permalinkStructure)
    {
        $this->wp->update_option('permalink_structure', $permalinkStructure);
        $this->flushRewriteRules();
    }

    public function flushRewriteRules()
    {
        $permalinkStructure = get_option('permalink_structure');
        global /** @var \WP_Rewrite $wp_rewrite */
        $wp_rewrite;
        $wp_rewrite->permalink_structure = $permalinkStructure;
        $this->wp->flush_rewrite_rules(false);
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getAdminIndex()
    {
        return $this->adminIndex;
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

    public function getAjaxIndex()
    {
        return $this->ajaxIndex;
    }

    public function getCronIndex()
    {
        return $this->cronIndex;
    }
}
