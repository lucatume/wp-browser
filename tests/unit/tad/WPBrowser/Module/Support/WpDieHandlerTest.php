<?php
namespace tad\WPBrowser\Module\Support;


class WpDieHandlerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
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

        $this->assertInstanceOf(WpDieHandler::class, $sut);
    }

    /**
     * @return WpDieHandlerInterface
     */
    private function make_instance()
    {
        return new WpDieHandler();
    }

    /**
     * @test
     * it should return the die message echoer when wp_dieing
     */
    public function it_should_return_the_die_message_echoer_when_wp_dieing()
    {
        $sut = $this->make_instance();

        $function = $sut->handleDie();

        $this->assertEquals([$sut, 'echoDieMessage'], $function);
    }

    /**
     * @test
     * it should return the die message echoer when wp ajax dieing
     */
    public function it_should_return_the_die_message_echoer_when_wp_ajax_dieing()
    {
        $sut = $this->make_instance();

        $function = $sut->handleAjaxDie();

        $this->assertEquals([$sut, 'echoDieMessage'], $function);
    }

    /**
     * @test
     * it should return the die message echoer when wp_xmlrpc_dieing
     */
    public function it_should_return_the_die_message_echoer_when_wp_xmlrpc_dieing()
    {
        $sut = $this->make_instance();

        $function = $sut->handleXmlrpcDie();

        $this->assertEquals([$sut, 'echoDieMessage'], $function);
    }
}