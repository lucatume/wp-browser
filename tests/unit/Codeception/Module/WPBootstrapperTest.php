<?php
namespace Codeception\Module;


use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Snapshot;

class WPBootstrapperTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ModuleContainer
     */
    protected $module_container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Restorer
     */
    protected $restorer;

    protected function _before()
    {
        global $_flag;
        $_flag = false;
        $this->module_container = $this->prophesize('Codeception\Lib\ModuleContainer');
        vfsStream::setup('wproot',
            null,
            ['wp' => ['wp-load.php' => '// load WordPress']]);
        $this->config = [
            'wpRootFolder' => vfsStream::url('wproot')
        ];
        $this->restorer = $this->prophesize('SebastianBergmann\GlobalState\Restorer');
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

        $this->assertInstanceOf('Codeception\Module\WPBootstrapper', $sut);
    }

    /**
     * @test
     * it should throw if wpRootFolder is missing
     */
    public function it_should_throw_if_wp_root_folder_is_missing()
    {
        $this->config = [];

        $this->setExpectedException('Codeception\Exception\ModuleConfigException');

        $sut = $this->make_instance();
    }

    /**
     * @test
     * it should throw if wpRootFolder is not a folder
     */
    public function it_should_throw_if_wp_root_folder_is_not_a_folder()
    {
        vfsStream::setup('wproot',
            null,
            ['wp' => 'a file really']);
        $this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

        $this->setExpectedException('Codeception\Exception\ModuleConfigException');

        $sut = $this->make_instance();
        $sut->_initialize();
    }

    /**
     * @test
     * it should throw if wpRootFolder is not readable
     */
    public function it_should_throw_if_wp_root_folder_is_not_readable()
    {
        $root = vfsStream::setup('wproot');
        $root->addChild(vfsStream::newDirectory('wp', 0000));
        $this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

        $this->setExpectedException('Codeception\Exception\ModuleConfigException');

        $sut = $this->make_instance();
        $sut->_initialize();
    }

    /**
     * @test
     * it should throw if wp-load.php is not in wpRootFolder
     */
    public function it_should_throw_if_wp_load_php_is_not_in_wp_root_folder()
    {
        $root = vfsStream::setup('wproot');
        $root->addChild(vfsStream::newDirectory('wp'));
        $this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

        $this->setExpectedException('Codeception\Exception\ModuleConfigException');

        $sut = $this->make_instance();
        $sut->_initialize();
    }

    /**
     * @test
     * it should throw if wp-load.php is not readable
     */
    public function it_should_throw_if_wp_load_php_is_not_readable()
    {
        $root = vfsStream::setup('wproot',
            null,
            ['wp' => ['wp-load.php' => 'some content']]);
        $this->config['wpRootFolder'] = vfsStream::url('wproot/wp');
        chmod(vfsStream::url('wproot/wp/wp-load.php'), 0);

        $this->setExpectedException('Codeception\Exception\ModuleConfigException');

        $sut = $this->make_instance();
        $sut->_initialize();
    }

    /**
     * @test
     * it should include the wp-load.php file
     */
    public function it_should_include_the_wp_load_php_file()
    {
        $root = vfsStream::setup('wproot',
            null,
            ['wp' => ['wp-load.php' => '<?php global $_flag; $_flag = true; ?>']]);
        $this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

        $sut = $this->make_instance();
        $sut->_initialize();
        $sut->bootstrapWp();

        global $_flag;
        $this->assertTrue($_flag);
    }

    /**
     * @test
     * it should not include wp-load.php twice
     */
    public function it_should_not_include_wp_load_php_twice()
    {
        $root = vfsStream::setup('wproot2',
            null,
            ['wp' => ['wp-load.php' => '<?php global $_flag; $_flag = true; ?>']]);
        $this->config['wpRootFolder'] = vfsStream::url('wproot2/wp');
        $sut = $this->make_instance();
        $sut->_initialize();
        $sut->bootstrapWp();

        global $_flag;
        $this->assertTrue($_flag);

        $_flag = false;

        $sut->bootstrapWp();

        $this->assertFalse($_flag);
    }

    /**
     * @test
     * it should restore global state after first bootstrapping
     */
    public function it_should_restore_global_state_after_first_bootstrapping()
    {
        $root = vfsStream::setup('wproot3',
            null,
            ['wp' => ['wp-load.php' => 'some content']]);
        $this->config['wpRootFolder'] = vfsStream::url('wproot3/wp');
        $this->restorer->restoreGlobalVariables(Argument::any())->shouldBeCalled();
        $this->restorer->restoreStaticAttributes(Argument::any())->shouldBeCalled();

        $sut = $this->make_instance();

        $sut->_initialize();

        global $someVar;

        $someVar = 'foo';

        $sut->bootstrapWp();

        $someVar = null;

        $sut->bootstrapWp();

        $this->assertEquals('foo', $someVar);
    }

    private function make_instance()
    {
        return new WPBootstrapper($this->module_container->reveal(), $this->config, $this->restorer->reveal());
    }
}