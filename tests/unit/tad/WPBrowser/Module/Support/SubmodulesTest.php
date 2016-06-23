<?php
namespace tad\WPBrowser\Module\Support;

require_once  codecept_data_dir('classes/modules/ModuleOne.php');

use Codeception\Lib\ModuleContainer;
use Codeception\Module\ModuleOne;
use Codeception\Module\WPBootstrapper;
use Codeception\Module\WPDb;

class SubmodulesTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    protected $modules = [];

    protected $moduleContainer;
    protected $config = [];

    protected function _before()
    {
        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
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

        $this->assertInstanceOf(Submodules::class, $sut);
    }

    /**
     * @test
     * it should throw if not all the modules are Codeception modules
     */
    public function it_should_throw_if_not_all_the_modules_are_codeception_modules()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->modules = [__CLASS__];

        $sut = $this->make_instance();
    }

    /**
     * @test
     * it should throw if trying to initialize non registered module
     */
    public function it_should_throw_if_trying_to_initialize_non_registered_module()
    {
        $this->expectException(\RuntimeException::class);

        $this->modules = [WPDb::class, WPBootstrapper::class];

        $sut = $this->make_instance();

        $sut->initializeModule('NotModule');
    }

    /**
     * @test
     * it should initialize the modules
     */
    public function it_should_initialize_the_modules()
    {
        $this->modules = [ModuleOne::class, WPBootstrapper::class];

        $sut = $this->make_instance();

        $sut->initializeModule('ModuleOne');
        
        $this->assertTrue($sut->isInitializedModule('ModuleOne'));
    }

    /**
     * @return Submodules
     */
    private function make_instance()
    {
        return new Submodules($this->modules, $this->moduleContainer->reveal(), $this->config);
    }
}