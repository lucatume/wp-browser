<?php
namespace Codeception\Module;

require_once codecept_data_dir('classes/test-cases/PublicTestCase.php');

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use tad\WPBrowser\Connector\WordPress as Connector;
use tad\WPBrowser\Traits\WithStubProphecy;
use tad\WPBrowser\StubProphecy\Arg;

class WordPressTest extends \Codeception\Test\Unit
{
    use WithStubProphecy;
    protected $backupGlobals = false;
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
     * @var Connector
     */
    protected $client;

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
        return new WordPress($this->moduleContainer->reveal(), $this->config, $this->client->reveal());
    }

    /**
     * @test
     * it should point to index file when requesting page
     */
    public function it_should_point_to_index_file_when_requesting_page()
    {
        $page = '/';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnPage($page);

        $this->assertEquals($page, $page);
    }

    /**
     * @test
     * it should point to index file and query vars
     */
    public function it_should_point_to_index_file_and_query_vars()
    {
        $page = '/?some=var';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnPage($page);

        $this->assertEquals($page, $page);
    }

    /**
     * @test
     * it should point to index file when requesting pretty permalinks
     */
    public function it_should_point_to_index_file_when_requesting_pretty_permalinks()
    {
        $page = '/some/pretty/permalink';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnPage($page);

        $this->assertEquals($page, $page);
    }

    /**
     * @test
     * it should point to admin index when requesting admin root
     */
    public function it_should_point_to_admin_index_when_requesting_admin_root()
    {
        $page = '/wp-admin';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnAdminPage('/');

        $this->assertEquals('/wp-admin/index.php', $page);
    }

    /**
     * @test
     * it should point to specific admin page when requesting specific admin page
     */
    public function it_should_point_to_specific_admin_page_when_requesting_specific_admin_page()
    {
        $page = '/wp-admin/some-page.php';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

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
        $page = '/wp-admin/some/pretty/permalink';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

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
        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);
        $page = $sut->amOnAdminAjaxPage();

        $this->assertEquals('/wp-admin/admin-ajax.php', $page);
    }


    /**
     * @test
     * it should point to ajax file when requesting ajax page with query vars
     */
    public function it_should_point_to_ajax_file_when_requesting_ajax_page_with_query_vars()
    {
        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

        $this->config['adminPath'] = '/wp-admin';
        $sut = $this->make_instance();
        $sut->_isMockRequest(true);

        $array_single = $sut->amOnAdminAjaxPage(['action' => 'foo_action']);
        $this->assertEquals('/wp-admin/admin-ajax.php?foo_action', $array_single);

        $array_multiple = $sut->amOnAdminAjaxPage(['action' => 'foo_action', 'data' => 'bar_data', 'nonce' => 'baz_nonce']);
        $this->assertEquals('/wp-admin/admin-ajax.php?foo_action&bar_data&baz_nonce', $array_multiple);

        $string = $sut->amOnAdminAjaxPage('foo_action&bar_data&baz_nonce');
        $this->assertEquals('/wp-admin/admin-ajax.php?foo_action&bar_data&baz_nonce', $string);

        $string_with_question_mark = $sut->amOnAdminAjaxPage('?foo_action&bar_data&baz_nonce');
        $this->assertEquals('/wp-admin/admin-ajax.php?foo_action&bar_data&baz_nonce', $string_with_question_mark);
    }

    /**
     * @test
     * it should point to cron file when requesting cron page
     */
    public function it_should_point_to_cron_file_when_requesting_cron_page()
    {
        $page = '/wp-cron.php';

        $this->client->setHeaders(Arg::type('array'))->shouldBeCalled();

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
        $this->config['wpRootFolder'] = __DIR__;

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    protected function _before()
    {
        $this->moduleContainer = $this->stubProphecy(ModuleContainer::class);
        $this->config = [
            'wpRootFolder' => codecept_data_dir('folder-structures/default-wp'),
            'adminUsername' => 'admin',
            'adminPassword' => 'admin'
        ];

        $this->client = $this->stubProphecy(Connector::class);
    }

    protected function _after()
    {
    }
}
