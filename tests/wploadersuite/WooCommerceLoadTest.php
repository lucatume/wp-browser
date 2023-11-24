<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class WooCommerceLoadTest extends WPTestCase
{
    public function testWordPressLoadedCorrectly(): void
    {
        $this->assertTrue(function_exists('do_action'));
    }

    public function testWooCommerceIsActivated(): void
    {
        $this->assertTrue(is_plugin_active('woocommerce/woocommerce.php'));
    }

    public function testWooCommerceFunctionsExist(): void
    {
        $this->assertTrue(function_exists('wc_get_product'));
    }
}
