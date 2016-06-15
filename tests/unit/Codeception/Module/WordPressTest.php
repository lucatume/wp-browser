<?php
namespace Codeception\Module;

require_once codecept_data_dir('classes/test-cases/PublicTestCase.php');

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Step;
use Codeception\TestInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecyInterface;
use Prophecy\Prophet;
use tad\PublicTestCase;
use tad\WPBrowser\Module\Support\WPFacade;
use tad\WPBrowser\Module\Support\WPFacadeInterface;

class WordPressTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var WPLoader
     */
    protected $loader;

    /**
     * @var PublicTestCase
     */
    protected $testCase;

    /**
     * @var WPFacadeInterface
     */
    protected $wpFacade;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    protected function _before()
    {
        $root = vfsStream::setup();
        $wpLoadFile = vfsStream::newFile('wp-load.php');
        $wpLoadFile->setContent('wp-load.php content');
        $root->addChild($wpLoadFile);

        $this->root = $root;

        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
        $this->config = [
            'wpRootFolder' => $root->url(),
            'dbName' => 'dbName',
            'dbHost' => 'dbHost',
            'dbUser' => 'dbUser',
            'dbPassword' => 'dbPassword'
        ];
        $this->loader = $this->prophesize(WPLoader::class);
        $this->testCase = $this->prophesize(PublicTestCase::class);
        $this->wpFacade = $this->prophesize(WPFacade::class);
    }

    protected function _after()
    {
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(WordPress::class, $sut);
    }

    /**
     * @test
     * it should set WPLoader not to run isolated installation routine
     */
    public function it_should_set_wp_loader_not_to_run_isolated_installation_routine()
    {
        $sut = $this->make_instance();

        $this->assertFalse($sut->_getConfig('isolatedInstall'));
    }

    /**
     * @test
     * it should set WPLoader not to run isolated install event if config specifies it
     */
    public function it_should_set_wp_loader_not_to_run_isolated_install_event_if_config_specifies_it()
    {
        $this->config['isolatedInstall'] = true;

        $sut = $this->make_instance();

        $this->assertFalse($sut->_getConfig('isolatedInstall'));
    }

    /**
     * @test
     * it should initialize WPLoader and hook on _initialize
     */
    public function it_should_initialize_wp_loader_and_hook_on_initialize()
    {
        $this->wpFacade->initialize()->shouldBeCalled();
        $this->wpFacade->home_url()->shouldBeCalled();
        $this->wpFacade->admin_url()->shouldBeCalled();

        $this->wpFacade->add_filter('template_include', [$this->wpFacade, 'includeTemplate'], Argument::type('int'), Argument::type('int'))->shouldBeCalled();
        $this->wpFacade->add_action('get_header', [$this->wpFacade, 'getHeader'], Argument::type('int'), Argument::type('int'))->shouldBeCalled();
        $this->wpFacade->add_action('get_footer', [$this->wpFacade, 'getFooter'], Argument::type('int'), Argument::type('int'))->shouldBeCalled();
        $this->wpFacade->add_action('get_sidebar', [$this->wpFacade, 'getSidebar'], Argument::type('int'), Argument::type('int'))->shouldBeCalled();

        $this->wpFacade->add_filter('wp_die_ajax_handler', [$this->wpFacade, 'handleAjaxDie'])->shouldBeCalled();
        $this->wpFacade->add_filter('wp_die_xmlrpc_handler', [$this->wpFacade, 'handleXmlrpcDie'])->shouldBeCalled();
        $this->wpFacade->add_filter('wp_die_handler', [$this->wpFacade, 'handleDie'])->shouldBeCalled();

        $sut = $this->make_instance();

        $sut->_initialize();
    }

    /**
     * @test
     * it should set up the testacase on _before
     */
    public function it_should_set_up_the_testacase_on_before()
    {
        $this->testCase->setUp()->shouldBeCalledTimes(1);
        $sut = $this->make_instance();

        $test = $this->prophesize(TestInterface::class)->reveal();
        $step = $this->prophesize(Step::class)->reveal();

        $sut->_before($test);
        $sut->_beforeStep($step);
        $sut->_before($test);
        $sut->_beforeStep($step);
    }

    /**
     * @test
     * it should tear down test case on _after
     */
    public function it_should_tear_down_test_case_on_after()
    {
        $this->testCase->tearDown()->shouldBeCalledTimes(1);
        $sut = $this->make_instance();

        $test = $this->prophesize(TestInterface::class)->reveal();
        $step = $this->prophesize(Step::class)->reveal();

        $sut->_after($test);
        $sut->_afterStep($step);
        $sut->_after($test);
        $sut->_afterStep($step);
    }

    /**
     * @test
     * it should reset inclusions on _cleanup
     */
    public function it_should_reset_inclusions_on_cleanup()
    {
        $this->wpFacade->resetInclusions()->shouldBeCalledTimes(1);

        $sut = $this->make_instance();

        $sut->_cleanup();
    }

    /**
     * @test
     * it should allow setting the index in the config
     */
    public function it_should_allow_setting_the_index_in_the_config()
    {
        $this->root->addChild(vfsStream::newFile('my-index.php'));

        $indexPath = $this->root->url() . '/my-index.php';
        $this->config['index'] = $indexPath;
        $sut = $this->make_instance();

        $this->assertEquals($indexPath, $sut->getIndex());
    }

    /**
     * @test
     * it should throw if specified index is not existing
     */
    public function it_should_throw_if_specified_index_is_not_existing()
    {
        $indexPath = $this->root->url() . '/foo.php';
        $this->config['index'] = $indexPath;

        $this->expectException(ModuleConfigException::class);

        $sut = $this->make_instance();
    }

    /**
     * @return WordPress
     */
    private function make_instance()
    {
        return new WordPress(
            $this->moduleContainer->reveal(),
            $this->config,
            $this->loader->reveal(),
            $this->testCase->reveal(),
            $this->wpFacade->reveal());
    }
}