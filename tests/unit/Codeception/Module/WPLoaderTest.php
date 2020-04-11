<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Output\BufferedOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use tad\WPBrowser\Adapters\WP;
use tad\WPBrowser\StubProphecy\Arg;
use tad\WPBrowser\Traits\WithStubProphecy;

class WPLoaderTest extends \Codeception\Test\Unit
{
    use WithStubProphecy;
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
        $this->wp->getWpContentDir()->willReturn('');
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
        $this->wp->getWpContentDir()->willReturn('');
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
        $this->wp->switch_theme(Arg::type('string'))->shouldNotBeCalled();

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

        $sut->_wordPressExitHandler($output);

        $this->assertMatchesStringSnapshot($output->fetch());
    }

    protected function _before()
    {
        $root = vfsStream::setup();
        $wpFolder = vfsStream::newDirectory('wp');
        $wpLoadFile = vfsStream::newFile('wp-load.php', 0777);
        $wpFolder->addChild($wpLoadFile);
        $root->addChild($wpFolder);

        $this->moduleContainer = $this->stubProphecy(ModuleContainer::class);
        $this->config = [
            'wpRootFolder' => $root->url().'/wp',
            'dbName' => 'someDb',
            'dbHost' => 'localhost',
            'dbUser' => 'somePass',
            'dbPassword' => 'somePass',
        ];
        $this->wp = $this->stubProphecy(WP::class);
    }

    /**
     * It should accept absolute paths in the pluginsDir parameter
     *
     * @test
     */
    public function should_accept_absolute_paths_in_the_plugins_dir_parameter()
    {
        $pluginsRoot = vfsStream::setup();
        $pluginsDir  = vfsStream::newDirectory('plugins');
        $pluginsRoot->addChild($pluginsDir);

        $this->config['pluginsFolder'] = $pluginsRoot->url() . '/plugins';

        $wpLoader = $this->make_instance();

        $this->assertEquals($pluginsRoot->url() . '/plugins', $wpLoader->getPluginsFolder());
        $this->assertEquals($pluginsRoot->url() . '/plugins/foo/bar', $wpLoader->getPluginsFolder('foo/bar'));
    }

    /**
     * It should throw if absolute path for pluginsFolder does not exist
     *
     * @test
     */
    public function should_throw_if_absolute_path_for_plugins_folder_does_not_exist()
    {
        $pluginsRoot = vfsStream::setup();

        $this->config['pluginsFolder'] = $pluginsRoot->url() . '/plugins';

        $wpLoader = $this->make_instance();

        $this->expectException(ModuleConfigException::class);

        $wpLoader->getPluginsFolder();
    }


    /**
     * It should throw if WP_PLUGINS_DIR does not exist
     *
     * @test
     */
    public function should_throw_if_wp_plugins_dir_does_not_exist()
    {
        if (!extension_loaded('uopz')) {
            $this->markTestSkipped('This test cannot run without the uopz extension');
        }

        uopz_redefine('WP_PLUGIN_DIR', '/foo/bar/baz');

        $wpLoader = $this->make_instance();

        $this->expectException(ModuleConfigException::class);

        $wpLoader->getPluginsFolder();
    }

    /**
     * It should correctly build paths when the WP_PLUGIN_DIR constant is defined
     *
     * @test
     */
    public function should_correctly_build_paths_when_the_wp_plugin_dir_const_is_defined()
    {
        if (!extension_loaded('uopz')) {
            $this->markTestSkipped('This test cannot run without the uopz extension');
        }
        $pluginsRoot = vfsStream::setup();
        $pluginsDir  = vfsStream::newDirectory('plugins');
        $pluginsRoot->addChild($pluginsDir);
        uopz_redefine('WP_PLUGIN_DIR', $pluginsRoot->url() . '/plugins');

        $wpLoader = $this->make_instance();

        $this->assertEquals($pluginsRoot->url() . '/plugins', $wpLoader->getPluginsFolder());
        $this->assertEquals($pluginsRoot->url() . '/plugins/foo/bar', $wpLoader->getPluginsFolder('foo/bar'));
    }

    /**
     * It should handle absolute path for configFile parameter
     *
     * @test
     */
    public function should_handle_absolute_path_for_config_file_parameter()
    {
        $configFile = __FILE__;
        $this->config['configFile'] = $configFile;

        $wpLoader = $this->make_instance();

        $this->assertEquals([__FILE__], $wpLoader->_getConfigFiles());
    }

    /**
     * It should handle multiple absolute and relative paths for config files
     *
     * @test
     */
    public function should_handle_multiple_absolute_and_relative_paths_for_config_files()
    {
        $filesHere                  = glob(__DIR__ . '/*.php');
        $configFiles                 = [
            basename(__FILE__),
            reset($filesHere),
            __FILE__
        ];
        $this->config['configFile'] = $configFiles;

        $wpLoader = $this->make_instance();

        $this->assertEquals([
            __FILE__,
            reset($filesHere)
        ], $wpLoader->_getConfigFiles(__DIR__));
    }
}
