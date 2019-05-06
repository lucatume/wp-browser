<?php

namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use Symfony\Component\Console\Output\BufferedOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use tad\WPBrowser\Adapters\WP;

class WPLoaderTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;

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
     * @var WP
     */
    protected $wp;

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(WPLoader::class, $sut);
    }

    /**
     * @return WPLoader
     */
    private function make_instance()
    {
        return new WPLoader($this->moduleContainer->reveal(), $this->config, $this->wp->reveal());
    }

    /**
     * @test
     * it should set the template and stylesheet options when passed a `theme` config parameter
     */
    public function it_should_set_the_template_and_stylesheet_options_when_passed_a_theme_config_parameter()
    {
        $this->config['theme'] = 'foo';

        $sut = $this->make_instance();
        $sut->_setActiveTheme();

        global $wp_tests_options;
        $this->assertNotEmpty($wp_tests_options['template']);
        $this->assertEquals('foo', $wp_tests_options['template']);
        $this->assertNotEmpty($wp_tests_options['stylesheet']);
        $this->assertEquals('foo', $wp_tests_options['stylesheet']);
    }

    /**
     * @test
     * it should allow to set a child theme passing an array of parent, child
     */
    public function it_should_allow_to_set_a_child_theme_passing_an_array_of_parent_child()
    {
        $this->config['theme'] = ['foo', 'bar'];

        $sut = $this->make_instance();
        $sut->_setActiveTheme();

        global $wp_tests_options;
        $this->assertNotEmpty($wp_tests_options['template']);
        $this->assertEquals('foo', $wp_tests_options['template']);
        $this->assertNotEmpty($wp_tests_options['stylesheet']);
        $this->assertEquals('bar', $wp_tests_options['stylesheet']);
    }

    /**
     * @test
     * it should switch to theme if set
     */
    public function it_should_switch_to_theme_if_set()
    {
        $this->config['theme'] = ['foo', 'bar'];
        $this->wp->switch_theme('bar')->shouldBeCalled();
        $this->wp->WP_CONTENT_DIR()->willReturn('');
        $this->wp->do_action('after_switch_theme', 'bar')->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->_switchTheme();
    }

    /**
     * @test
     * it should switch theme to just stylesheet if no template
     */
    public function it_should_switch_theme_to_just_stylesheet_if_no_template()
    {
        $this->config['theme'] = 'foo';
        $this->wp->switch_theme('foo')->shouldBeCalled();
        $this->wp->WP_CONTENT_DIR()->willReturn('');
        $this->wp->do_action('after_switch_theme', 'foo')->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->_switchTheme();
    }

    /**
     * @test
     * it should not switch to theme if not set
     */
    public function it_should_not_switch_to_theme_if_not_set()
    {
        unset($this->config['theme']);
        $this->wp->switch_theme(Argument::type('string'))->shouldNotBeCalled();

        $sut = $this->make_instance();
        $sut->_switchTheme();
    }

    public function exitMessagesCombos()
    {
        return [
            'no_db_module_loadOnly_true' => [false, true],
            'no_db_module_loadOnly_false' => [false, false],
            'WPDb_module_loadOnly_true' => ['WPDb', true],
            'WPDb_module_loadOnly_false' => ['WPDb', false],
            'Db_module_loadOnly_true' => ['Db', true],
            'Db_module_loadOnly_false' => ['Db', false],
        ];
    }

    /**
     * Test exit messages
     *
     * @dataProvider exitMessagesCombos
     */
    public function test_exit_messages($dbModule, $loadOnly)
    {
        $this->moduleContainer->hasModule('WPDb')->willReturn($dbModule === 'WPDb');
        $this->moduleContainer->hasModule('Db')->willReturn($dbModule === 'Db');
        $sut = $this->make_instance();
        $output = new BufferedOutput();
        $sut->_setConfig(array_merge($sut->_getConfig(), [
            'loadOnly' => $loadOnly
        ]));

        $sut->_wordpressExitHandler($output);

        $this->assertMatchesStringSnapshot($output->fetch());
    }

    protected function _before()
    {
        $root = vfsStream::setup();
        $wpFolder = vfsStream::newDirectory('wp');
        $wpLoadFile = vfsStream::newFile('wp-load.php', 0777);
        $wpFolder->addChild($wpLoadFile);
        $root->addChild($wpFolder);

        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
        $this->config = [
            'wpRootFolder' => $root->url().'/wp',
            'dbName' => 'someDb',
            'dbHost' => 'localhost',
            'dbUser' => 'somePass',
            'dbPassword' => 'somePass',
        ];
        $this->wp = $this->prophesize(WP::class);
    }
}
