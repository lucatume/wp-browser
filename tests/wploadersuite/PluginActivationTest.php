<?php

class PluginActivationTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    /**
     * @test
     * it should not activate network plugins on non mu installation
     */
    public function it_should_not_activate_network_plugins_on_non_mu_installation()
    {
        $this->assertEquals('not-mu-activated', get_option('muplugin1'));
    }
}
