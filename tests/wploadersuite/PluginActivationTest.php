<?php
namespace lucatume\WPBrowser\Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

class PluginActivationTest extends WPTestCase
{
    /**
     * @test
     * it should network not activate network plugins on non mu installation
     */
    public function it_should_not_network_activate_network_plugins_on_non_mu_installation()
    {
        $this->assertEquals('not-mu-activated', get_option('muplugin1'));
    }
}
