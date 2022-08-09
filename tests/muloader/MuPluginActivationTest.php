<?php
namespace lucatume\WPBrowser\Tests;

class MuPluginActivationTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @test
     * it should network activate network plugins on mu installation
     */
    public function it_should_network_activate_network_plugins_on_mu_installation()
    {
        $this->assertFalse(get_option('muplugin1'));
        $this->assertEquals('mu-activated', get_network_option(null, 'muplugin1'));
    }
}
