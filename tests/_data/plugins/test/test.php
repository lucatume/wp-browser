<?php
/*
Plugin Name: Test plugin
Description: Test plugin
Version: 0.1.0
Author: Luca Tumedei
Author URI: http://theaveragedev.com
Text Domain: test
*/

add_action('rest_api_init', function () {
    register_rest_route('test', '/whoami/', [
        'methods'  => 'GET',
        'callback' => static function () {
            echo wp_get_current_user()->user_login;
            die(200);
        },
    ]);
});
