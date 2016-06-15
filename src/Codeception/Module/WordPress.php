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
use Codeception\Lib\Connector\Universal as UniversalConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Generator\Test;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Step;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use tad\WPBrowser\Module\Support\WPFacade;
use tad\WPBrowser\Module\Support\WPFacadeInterface;

class WordPress extends Framework
{
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

        $this->setIndexFile();
    }

    private function setIndexFile()
    {
        if (empty($this->config['index'])) {
            $this->index = __DIR__ . '/scripts/wp-index.php';
        } else {
            if (!file_exists($this->config['index'])) {
                throw new ModuleConfigException(__CLASS__, 'Index file [' . $this->config['index'] . '] does not exist.');
            }
            $this->index = $this->config['index'];
        }
    }

    public function _initialize()
    {
        $this->initializeWPLoaderModule();
        $this->adminPath = $this->getAdminPath();
        $this->hookTemplateInterception();
        $this->hookWpDieHandler();
    }

    private function initializeWPLoaderModule()
    {
        $this->wp->initialize();
    }

    private function getAdminPath()
    {
        return str_replace($this->wp->home_url(), '', $this->wp->admin_url());
    }

    private function hookTemplateInterception()
    {
        $this->wp->add_filter('template_include', [$this->wp, 'includeTemplate'], PHP_INT_MAX, 1);
        $this->wp->add_action('get_header', [$this->wp, 'getHeader'], PHP_INT_MAX, 1);
        $this->wp->add_action('get_footer', [$this->wp, 'getFooter'], PHP_INT_MAX, 1);
        $this->wp->add_action('get_sidebar', [$this->wp, 'getSidebar'], PHP_INT_MAX, 1);
    }

    private function hookWpDieHandler()
    {
        $this->wp->add_filter('wp_die_ajax_handler', [$this->wp, 'handleAjaxDie']);
        $this->wp->add_filter('wp_die_xmlrpc_handler', [$this->wp, 'handleXmlrpcDie']);
        $this->wp->add_filter('wp_die_handler', [$this->wp, 'handleDie']);
    }

    public function _before(TestInterface $test)
    {
        $this->client = new UniversalConnector();
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

    public function amOnPage($page)
    {
        $parts = parse_url($page);
        $parameters = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $parameters);
        }

        $this->_loadPage('GET', $page, $parameters);
    }

    public function _cleanup()
    {
        $this->setWpQueryName();
        $this->resetTestCaseControlProperties();
        $this->wp->resetInclusions();
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

    public function resetTemplateInclusions()
    {
        $this->wp->resetInclusions();
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
}
