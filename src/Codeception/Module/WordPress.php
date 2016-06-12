<?php

/*
 * @todo: handle event hooks and setUp/tearDown the WP_UnitTestCase accordingly
 * 
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
 * [12-Jun-2016 12:32:52 UTC] Codeception\Module\WordPress::_afterSuite
*/

// @todo: add a note in docs that _after and _before methods should call the parent!

namespace Codeception\Module;

use Codeception\Lib\Connector\Universal as UniversalConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Generator\Test;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Step;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;

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
     * @var \WP_UnitTestCase
     */
    private $testCase;

    /**
     * WordPress constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param array $config
     * @param WPLoader|null $loader
     * @param \WP_UnitTestCase $testCase
     */
    public function __construct(ModuleContainer $moduleContainer, $config = [], WPLoader $loader = null, $testCase = null)
    {
        error_log(__METHOD__);
        $config = array_merge($this->config, (array)$config);
        $config['isolatedInstall'] = false;

        parent::__construct($moduleContainer, $config);

        $this->loader = $loader ? $loader : new WPLoader($moduleContainer, $config);
        $this->testCase = $testCase;

        $this->index = __DIR__ . '/wp-index.php';
    }

    public function _initialize()
    {
        error_log(__METHOD__);
        $this->bootstrapWordPress();
    }

    private function bootstrapWordPress()
    {
        $this->loader->_initialize();
    }

    public function _before(TestInterface $test)
    {
        error_log(__METHOD__);
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

    public function _cleanup()
    {
        error_log(__METHOD__);
        $this->resetTestCaseControlProperties();
    }

    private function resetTestCaseControlProperties()
    {
        $this->testCaseWasSetup = false;
        $this->testCaseWasTornDown = false;
    }

    public function _beforeSuite($settings = [])
    {
        error_log(__METHOD__);

        if (null === $this->testCase) {
            $this->testCase = new \WP_UnitTestCase();
        }

        \WP_UnitTestCase::setUpBeforeClass();

        $ref = new ReflectionHelper();
        $this->factory = $ref->invokePrivateMethod($this->testCase, 'factory', [], \WP_UnitTestCase::class);
    }

    public function _afterSuite()
    {
        error_log(__METHOD__);
        /** @var \WP_UnitTestCase $class */
        $class = get_class($this->testCase);
        $class::tearDownAfterClass();
    }

    public function _beforeStep(Step $step)
    {
        error_log(__METHOD__);
        $this->setUpTestCase();
    }

    public function _afterStep(Step $step)
    {
        error_log(__METHOD__);
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
        error_log(__METHOD__);
        $this->tearDownTestCase();
    }

    public function _after(TestInterface $test)
    {
        error_log(__METHOD__);
        $this->tearDownTestCase();
    }
}
