<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class Api
{
}

class AdminApi
{
}

function get_api()
{
    static $api;

    if (null !== $api) {
        return $api;
    }

    if (is_admin()) {
        $api = new AdminApi();
    } else {
        $api = new Api();
    }

    return $api;
}

class apiTest extends WPTestCase
{
    public function test_get_api_exists()
    {
        $this->assertTrue(function_exists('get_api'));
    }

    public function test_get_api_will_cache()
    {
        $this->assertSame(get_api(), get_api());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_return_api_if_not_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', false);

        $api = get_api();

        $this->assertInstanceOf(Api::class, $api);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_cache_api_if_not_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', false);

        $api = get_api();

        $this->assertSame(get_api(), $api);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_return_api_if_is_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', true);

        $api = get_api();

        $this->assertInstanceOf(AdminApi::class, $api);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_get_api_will_cache_api_if_is_admin()
    {
        // Let's make sure we're NOT in admin context.
        define('WP_ADMIN', true);

        $api = get_api();

        $this->assertSame(get_api(), $api);
    }
}
