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
use Codeception\Util\ReflectionHelper;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;
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
    protected $requiredFields = array('wpRootFolder', 'dbName', 'dbHost', 'dbUser', 'dbPassword',);
    /**
     * @var array
     */
    protected $config = array(
        'wpDebug' => false,
        'multisite' => false,
        'dbCharset' => 'utf8',
        'dbCollate' => '',
        'tablePrefix' => 'wptests_',
        'domain' => 'example.org',
        'adminEmail' => 'admin@example.org',
        'title' => 'Test Blog',
        'phpBinary' => 'php',
        'language' => '',
        'configFile' => '',
        'pluginsFolder' => '',
        'plugins' => '',
        'activatePlugins' => '',
        'bootstrapActions' => '',
    );
    /**
     * @var WPLoader
     */
    protected $loader;
    /**
     * @var bool
     */
    protected $testCaseWasSetup = false;
    /**
     * @var
     */
    protected $testCaseWasTornDown = false;
    /**
     * @var \WP_UnitTest_Factory
     */
    protected $factory;
    /**
     * @var \WP_UnitTestCase
     */
    protected $testCase;
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
     * WordPress constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param array $config
     * @param \WP_UnitTestCase $testCase
     * @param WPFacadeInterface $wp
     */
    public function __construct(ModuleContainer $moduleContainer, $config = [], WPLoader $loader = null, $testCase = null, WPFacadeInterface $wp = null
    )
    {
        $config = array_merge($this->config, (array)$config);
        $config['isolatedInstall'] = false;

        parent::__construct($moduleContainer, $config);
        $this->loader = $loader ? $loader : new WPLoader($moduleContainer, $this->config);

        $this->testCase = $testCase;

        $this->wp = $wp ? $wp : new WPFacade($this->loader);

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
        $this->initializeWPLoaderModule();
        $this->adminPath = $this->wp->getAdminPath();

        $this->setIndexFile();
        $this->setAdminIndexFile();
        $this->setAjaxIndexFile();
        $this->setCronIndexFile();
    }

    private function initializeWPLoaderModule()
    {
        $this->wp->initialize();
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

        $this->setUpTestCase();
    }

    private function setUpTestCase()
    {
        if (!$this->testCaseWasSetup) {
            $this->testCase->setUp();
            $this->testCaseWasSetup = true;
        }
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
    }

    private function isAdminPageRequest($page)
    {
        return 0 === strpos($page, $this->adminPath);
    }

    public function _cleanup()
    {
        $this->setWpQueryName();
        $this->resetTestCaseControlProperties();
    }

    private function setWpQueryName()
    {
        global $wp_query;
        if (!isset($wp_query->query_vars['name'])) {
            $wp_query->query_vars['name'] = '';
        }
    }

    private function resetTestCaseControlProperties()
    {
        $this->testCaseWasSetup = false;
        $this->testCaseWasTornDown = false;
    }

    public function _beforeSuite($settings = [])
    {

        if (null === $this->testCase) {
            $this->testCase = new \WP_UnitTestCase();
        }

        \WP_UnitTestCase::setUpBeforeClass();

        $ref = new ReflectionHelper();
        $this->factory = $ref->invokePrivateMethod($this->testCase, 'factory', [], \WP_UnitTestCase::class);
    }

    public function _afterSuite()
    {
        /** @var \WP_UnitTestCase $class */
        $class = get_class($this->testCase);
        $class::tearDownAfterClass();
    }

    public function _beforeStep(Step $step)
    {
        $this->setUpTestCase();
    }

    public function _afterStep(Step $step)
    {
        $this->tearDownTestCase();
    }

    private function tearDownTestCase()
    {
        if (!$this->testCaseWasTornDown) {
            $this->testCase->tearDown();
            global $wp_query, $wp_the_query;
            $wp_query = $wp_the_query;
            $this->testCaseWasTornDown = true;
        }
    }

    public function _failed(TestInterface $test, $fail)
    {
        $this->tearDownTestCase();
    }

    public function _after(TestInterface $test)
    {
        $this->tearDownTestCase();
    }

    /**
     * @return \WP_UnitTest_Factory
     */
    public function factory()
    {
        return $this->factory;
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
