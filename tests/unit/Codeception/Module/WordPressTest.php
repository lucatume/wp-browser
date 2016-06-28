<?php
namespace Codeception\Module;

require_once codecept_data_dir('classes/test-cases/PublicTestCase.php');

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Step;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prophecy\Argument;

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
        return new WordPress($this->moduleContainer->reveal(), $this->config);
    }

    /**
     * @test
     * it should point to index file when requesting page
     */
    public function it_should_point_to_index_file_when_requesting_page()
    {
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnPage('/');

        $this->assertEquals('/', $page);
    }

    /**
     * @test
     * it should point to index file and query vars
     */
    public function it_should_point_to_index_file_and_query_vars()
    {
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnPage('/?some=var');

        $this->assertEquals('/?some=var', $page);
    }

    /**
     * @test
     * it should point to index file when requesting pretty permalinks
     */
    public function it_should_point_to_index_file_when_requesting_pretty_permalinks()
    {
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnPage('/some/pretty/permalink');

        $this->assertEquals('/some/pretty/permalink', $page);
    }

    /**
     * @test
     * it should point to admin index when requesting admin root
     */
    public function it_should_point_to_admin_index_when_requesting_admin_root()
    {
        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnAdminPage('/');

        $this->assertEquals('/wp-admin/', $page);
    }

    /**
     * @test
     * it should point to specific admin page when requesting specific admin page
     */
    public function it_should_point_to_specific_admin_page_when_requesting_specific_admin_page()
    {
        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnAdminPage('/some-page.php');

        $this->assertEquals('/wp-admin/some-page.php', $page);
    }

    /**
     * @test
     * it should point to admin pretty page when specifying admin pretty page
     */
    public function it_should_point_to_admin_pretty_page_when_specifying_admin_pretty_page()
    {
        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnAdminPage('/some/pretty/permalink');

        $this->assertEquals('/wp-admin/some/pretty/permalink', $page);
    }

    /**
     * @test
     * it should point to ajax file when requesting ajax page
     */
    public function it_should_point_to_ajax_file_when_requesting_ajax_page()
    {
        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnAdminAjaxPage();

        $this->assertEquals('/wp-admin/admin-ajax.php', $page);
    }

    /**
     * @test
     * it should point to cron file when requesting cron page
     */
    public function it_should_point_to_cron_file_when_requesting_cron_page()
    {
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnCronPage();

        $this->assertEquals('/wp-cron.php', $page);
    }

    /**
     * @test
     * it should throw if specified wpRootFolder does not exist
     */
    public function it_should_throw_if_specified_wp_root_folder_does_not_exist()
    {
        $this->config['wpRootFolder'] = '/some/folder';

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    /**
     * @test
     * it should throw if specified wpRootFolder does not contain wp-settings.php file
     */
    public function it_should_throw_if_specified_wp_root_folder_does_not_contain_wp_settings_php_file()
    {
        $root = vfsStream::setup();

        $this->config['wpRootFolder'] = $root->url();

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    protected function _before()
    {
        $root = vfsStream::setup();
        $wpLoadFile = vfsStream::newFile('wp-settings.php');
        $wpLoadFile->setContent('wp-settings.php content');
        $root->addChild($wpLoadFile);

        $this->root = $root;

        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
        $this->config = [
            'wpRootFolder' => $root->url(),
            'adminUsername' => 'admin',
            'adminPassword' => 'admin'
        ];
    }

    protected function _after()
    {
    }
}