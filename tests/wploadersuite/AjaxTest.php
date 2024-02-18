<?php

use lucatume\WPBrowser\TestCase\WPAjaxTestCase;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;

class AjaxTest extends WPAjaxTestCase
{
    use UopzFunctions;

    public function ajaxCallback(): void
    {
        if (
            !isset($_POST['test_nonce']) ||
            !wp_verify_nonce(sanitize_key($_POST['test_nonce']), 'test_ajax_action')
        ) {
            wp_send_json_error('Number not only once is invalid', 404);
        }
        // The rest of the code...
    }

    public function setUp(): void
    {
        parent::setUp();
        add_action('wp_ajax_test_ajax_action', [$this, 'ajaxCallback']);
        add_action('wp_ajax_nopriv_test_ajax_action', [$this, 'ajaxCallback']);
    }

    public function testAjaxCallbackWillFailIfNumberNotOnlyOnceIsInvalid(): void
    {
        $response = null;
        try {
            global $wpdb;
            $wpdb->suppress_errors = true; // Due to a WooCommerce activation issue in older versions.
            $this->_handleAjax('test_ajax_action');
        } catch (\WPAjaxDieContinueException $exception) {
            $response = json_decode($this->_last_response);
        }

        $wpdb->suppress_errors = false;

        $this->assertNotNull($response);
        $this->assertFalse($response->success);
        $this->assertEquals('Number not only once is invalid', $response->data);
    }
}
