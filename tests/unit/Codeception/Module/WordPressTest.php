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
use tad\PublicTestCase;
use tad\WPBrowser\Connector\WordPress as WordPressConnector;
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
     * @test
     * it should allow specifying an admin index file
     */
    public function it_should_allow_specifying_an_admin_index_file()
    {
        $this->root->addChild(vfsStream::newFile('my-admin-index.php'));

        $indexPath = $this->root->url() . '/my-admin-index.php';
        $this->config['adminIndex'] = $indexPath;
        $sut = $this->make_instance();

        $this->assertEquals($indexPath, $sut->getAdminIndex());
    }

    /**
     * @test
     * it should throw if specified admin index is not existing
     */
    public function it_should_throw_if_specified_admin_index_is_not_existing()
    {
        $adminIndexPath = $this->root->url() . '/foo.php';
        $this->config['adminIndex'] = $adminIndexPath;

        $this->expectException(ModuleConfigException::class);

        $sut = $this->make_instance();
    }

    /**
     * @test
     * it should allow specifying an ajax index file
     */
    public function it_should_allow_specifying_an_ajax_index_file()
    {
        $this->root->addChild(vfsStream::newFile('my-ajax-index.php'));

        $indexPath = $this->root->url() . '/my-ajax-index.php';
        $this->config['ajaxIndex'] = $indexPath;
        $sut = $this->make_instance();

        $this->assertEquals($indexPath, $sut->getAjaxIndex());
    }

    /**
     * @test
     * it should throw if specified ajax index is not existing
     */
    public function it_should_throw_if_specified_ajax_index_is_not_existing()
    {
        $ajaxIndexPath = $this->root->url() . '/foo.php';
        $this->config['ajaxIndex'] = $ajaxIndexPath;

        $this->expectException(ModuleConfigException::class);

        $sut = $this->make_instance();
    }

    /**
     * @test
     * it should allow specifying an cron index file
     */
    public function it_should_allow_specifying_an_cron_index_file()
    {
        $this->root->addChild(vfsStream::newFile('my-cron-index.php'));

        $indexPath = $this->root->url() . '/my-cron-index.php';
        $this->config['cronIndex'] = $indexPath;
        $sut = $this->make_instance();

        $this->assertEquals($indexPath, $sut->getCronIndex());
    }

    /**
     * @test
     * it should throw if specified cron index is not existing
     */
    public function it_should_throw_if_specified_cron_index_is_not_existing()
    {
        $cronIndexPath = $this->root->url() . '/foo.php';
        $this->config['cronIndex'] = $cronIndexPath;

        $this->expectException(ModuleConfigException::class);

        $sut = $this->make_instance();
    }

    /**
     * @test
     * it should point client to specified index file
     */
    public function it_should_point_client_to_specified_index_file()
    {
        $this->root->addChild(vfsStream::newFile('my-index.php'));

        $indexPath = $this->root->url() . '/my-index.php';
        $this->config['index'] = $indexPath;

        /** @var WordPressConnector $client */
        $client = $this->prophesize(WordPressConnector::class);
        $client->followRedirects(true)->shouldBeCalled();
        $client->setIndex($indexPath)->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->_setClient($client->reveal());

        $sut->_before($this->prophesize(TestInterface::class)->reveal());
    }

    /**
     * @test
     * it should point client to admin index when requesting an admin page
     */
    public function it_should_point_client_to_admin_index_when_requesting_an_admin_page()
    {
        $this->root->addChild(vfsStream::newFile('my-index.php'));
        $this->root->addChild(vfsStream::newFile('my-admin-index.php'));
        $indexPath = $this->root->url() . '/my-index.php';
        $adminIndexPath = $this->root->url() . '/my-admin-index.php';
        $this->config['index'] = $indexPath;
        $this->config['adminIndex'] = $adminIndexPath;

        /** @var WordPressConnector $client */
        $client = $this->prophesize(WordPressConnector::class);
        $client->followRedirects(true)->shouldBeCalled();
        $client->setIndex($indexPath)->shouldBeCalledTimes(1);

        $sut = $this->make_instance();
        $sut->_setClient($client->reveal());
        $sut->_before($this->prophesize(TestInterface::class)->reveal());

        $sut->setAdminPath('/subfolder/wp-admin');

        $client->setIndex($adminIndexPath)->shouldBeCalledTimes(1);

        $sut->_isMockRequest(true);
        $sut->amOnPage('/subfolder/wp-admin/some-admin-page.php');
    }

    /**
     * @test
     * it should go from index to admin to index when requesting different index/admin/index pages
     */
    public function it_should_go_from_index_to_admin_to_index_when_requesting_different_index_admin_index_pages()
    {
        $this->root->addChild(vfsStream::newFile('my-index.php'));
        $this->root->addChild(vfsStream::newFile('my-admin-index.php'));
        $indexPath = $this->root->url() . '/my-index.php';
        $adminIndexPath = $this->root->url() . '/my-admin-index.php';
        $this->config['index'] = $indexPath;
        $this->config['adminIndex'] = $adminIndexPath;

        /** @var WordPressConnector $client */
        $client = $this->prophesize(WordPressConnector::class);
        $client->followRedirects(true)->shouldBeCalled();
        $client->setIndex($indexPath)->shouldBeCalledTimes(3);

        $sut = $this->make_instance();
        $sut->_setClient($client->reveal());
        $sut->_before($this->prophesize(TestInterface::class)->reveal());

        $sut->setAdminPath('/subfolder/wp-admin');

        $client->setIndex($adminIndexPath)->shouldBeCalledTimes(1);

        $sut->_isMockRequest(true);
        $sut->amOnPage('/foo-front-end-path');
        $this->assertFalse($sut->_lastRequestWasAdmin());

        $sut->amOnPage('/subfolder/wp-admin/some-admin-page.php');
        $this->assertTrue($sut->_lastRequestWasAdmin());

        $sut->amOnPage('/bar-front-end-path');
        $this->assertFalse($sut->_lastRequestWasAdmin());
    }

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
            'adminUsername' => 'admin',
            'adminPassword' => 'admin'
        ];
        $this->loader = $this->prophesize(WPLoader::class);
        $this->testCase = $this->prophesize(PublicTestCase::class);
        $this->wpFacade = $this->prophesize(WPFacade::class);
    }

    protected function _after()
    {
    }
}