<?php

namespace Codeception\TestCase;

abstract class WPRestControllerTestCase extends WPRestApiTestCase
{

    protected $server;

    public function _setUp()
    {
        parent::_setUp();
        add_filter('rest_url', array($this, 'filter_rest_url_for_leading_slash'), 10, 2);
        /** @var \WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \Spy_REST_Server;
        do_action('rest_api_init', $wp_rest_server);
    }

    public function _tearDown()
    {
        parent::_tearDown();
        /** @var callable $function_to_remove */
        $function_to_remove = [$this, 'test_rest_url_for_leading_slash'];
        remove_filter('rest_url', $function_to_remove, 10);
        /** @var \WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $wp_rest_server = null;
    }

    abstract public function test_register_routes();

    abstract public function test_context_param();

    abstract public function test_get_items();

    abstract public function test_get_item();

    abstract public function test_create_item();

    abstract public function test_update_item();

    abstract public function test_delete_item();

    abstract public function test_prepare_item();

    abstract public function test_get_item_schema();

    public function filter_rest_url_for_leading_slash($url, $path)
    {
        if (is_multisite()) {
            return $url;
        }

        // Make sure path for rest_url has a leading slash for proper resolution.
        $this->assertTrue(0 === strpos($path, '/'), 'REST API URL should have a leading slash.');

        return $url;
    }
}
