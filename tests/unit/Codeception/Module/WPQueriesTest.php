<?php
namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use tad\WPBrowser\Environment\Constants;

class WPQueriesTest extends \Codeception\TestCase\Test
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
    protected $config = [];

    /**
     * @var Constants
     */
    protected $constants;

    protected function _before()
    {
        $this->moduleContainer = $this->prophesize('Codeception\Lib\ModuleContainer');
        $this->constants = $this->prophesize('tad\WPBrowser\Environment\Constants');
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

        $this->assertInstanceOf('Codeception\Module\WPQueries', $sut);
    }

    private function make_instance()
    {
        return new WPQueries($this->moduleContainer->reveal(),$this->config,$this->constants->reveal());
    }
}