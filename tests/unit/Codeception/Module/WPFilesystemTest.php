<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
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

    protected function _after() {
        if ( ! empty($this->sandbox) && file_exists($this->sandbox)) {
            rrmdir($this->sandbox);
        }
    }

}