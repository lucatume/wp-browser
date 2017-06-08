<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use tad\WPBrowser\Filesystem\Utils;
use function tad\WPBrowser\Tests\Support\rrmdir;

class WPFilesystemTest extends \Codeception\Test\Unit {

    /**
     * @var \Codeception\Lib\ModuleContainer
     */
    protected $moduleContainer;
    protected $config;
    protected $sandbox;
    protected $backupGlobals = false;
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function _before() {
        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
    }

    /**
     * It should be instantiatable
     *
     * @test
     */
    public function be_instantiatable() {
        $this->assertInstanceOf(WPFilesystem::class, $this->make_instance());
    }

    /**
     * @return WPFilesystem
     */
    protected function make_instance() {
        $this->config = null !== $this->config
            ? $this->config
            : $this->getDefaultConfig();
        $instance = new WPFilesystem($this->moduleContainer->reveal(), $this->config);
        $instance->_initialize();

        return $instance;
    }

    protected function getDefaultConfig(array $overrides = []) {
        $wpFolder = isset($overrides['wp']) ? $overrides['wp'] : '';

        $this->sandbox = codecept_output_dir('sandbox' . $wpFolder);

        rrmdir($this->sandbox);

        mkdir($this->sandbox . $wpFolder, 0777, true);
        file_put_contents($this->sandbox . '/wp-load.php', '<?php //silence is golden;');

        $themes = isset($overrides['themes']) ? $overrides['themes'] : '/wp-content/themes';
        $plugins = isset($overrides['plugins']) ? $overrides['plugins'] : '/wp-content/plugins';
        $muPlugins = isset($overrides['mu-plugins']) ? $overrides['mu-plugins'] : '/wp-content/mu-plugins';
        $uploads = isset($overrides['uploads']) ? $overrides['uploads'] : "/wp-content/uploads";

        mkdir($this->sandbox . $themes, 0777, true);
        mkdir($this->sandbox . $plugins, 0777, true);
        mkdir($this->sandbox . $muPlugins, 0777, true);
        $Y = date('Y');
        $m = date('m');
        mkdir($this->sandbox . "{$uploads}/{$Y}/{$m}", 0777, true);

        return [
            'wpRootFolder' => $this->sandbox,
            'themes'       => $themes,
            'plugins'      => $plugins,
            'mu-plugins'   => $muPlugins,
            'uploads'      => $uploads,
        ];
    }

    /**
     * It should throw if wpRootFolder param is missing
     *
     * @test
     */
    public function it_should_throw_if_wp_root_folder_param_is_missing() {
        $this->config = [];

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    /**
     * It should only require the wpRootFolder path parameter and default the other parameters
     *
     * @test
     */
    public function it_should_only_require_the_wp_root_folder_path_parameter_and_default_the_other_parameters() {
        $config = $this->getDefaultConfig();
        $wpRoot = $config['wpRootFolder'];
        $this->config = ['wpRootFolder' => $wpRoot];

        $sut = $this->make_instance();
        $sut->_initialize();

        $moduleConfig = $sut->_getConfig();
        $this->assertEquals(Utils::untrailslashit($wpRoot) . '/', $moduleConfig['wpRootFolder']);
        $this->assertEquals($wpRoot . '/wp-content/themes/', $moduleConfig['themes']);
        $this->assertEquals($wpRoot . '/wp-content/plugins/', $moduleConfig['plugins']);
        $this->assertEquals($wpRoot . '/wp-content/mu-plugins/', $moduleConfig['mu-plugins']);
        $this->assertEquals($wpRoot . '/wp-content/uploads/', $moduleConfig['uploads']);
    }

    /**
     * It should allow passing optional parameters as relative paths
     *
     * @test
     */
    public function it_should_allow_passing_optional_parameters_as_relative_paths() {
    }

    public function optionalRequiredPathParameters() {
        return [
            ['themes'],
            ['plugins'],
            ['mu-plugins'],
            ['uploads'],
        ];
    }

    /**
     * It should throw if optional parameter is specified but not existing
     *
     * @test
     * @dataProvider optionalRequiredPathParameters
     */
    public function it_should_throw_if_optional_parameter_is_specified_but_not_existing($parameter) {
        $config = $this->getDefaultConfig();

        $this->config = [
            'wpRootFolder' => $config['wpRootFolder'],
            $parameter     => __DIR__ . '/foo',
        ];

        $this->expectException(ModuleConfigException::class);

        $sut = $this->make_instance();
        $sut->_initialize();
    }

    /**
     * It should allow specifying wpRootFolder as relative path to the project root
     *
     * @test
     */
    public function it_should_allow_specifying_wp_root_folder_as_relative_path_to_the_project_root() {
        $this->config = [
            'wpRootFolder' => '/tests/_output/sandbox',
        ];

        $this->getDefaultConfig();
        $sut = $this->make_instance();
        $sut->_initialize();
        $this->assertEquals(codecept_output_dir('sandbox/'), $sut->_getConfig('wpRootFolder'));
    }

    /**
     * It should allow specifying optional path parameters as relative paths
     *
     * @test
     * @dataProvider optionalRequiredPathParameters
     */
    public function it_should_allow_specifying_optional_path_parameters_as_relative_paths($parameter) {
        $config = $this->getDefaultConfig();
        $path = $config['wpRootFolder'] . '/foo/';
        mkdir($path, 0777, true);

        $this->config = [
            'wpRootFolder' => $config['wpRootFolder'],
            $parameter     => 'foo',
        ];

        $sut = $this->make_instance();
        $sut->_initialize();
        $this->assertEquals($path, $sut->_getConfig($parameter));
    }

    protected function _after() {
        if ( ! empty($this->sandbox) && file_exists($this->sandbox)) {
            rrmdir($this->sandbox);
        }
    }

}