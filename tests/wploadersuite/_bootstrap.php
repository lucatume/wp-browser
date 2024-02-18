<?php
// Here you can initialize variables that will be available to your tests

// At bootstrap file time, WordPress should be loaded.
if (!class_exists(WP_Post::class)) {
    throw new RuntimeException('WP_Post class not found');
}

// Work around WooCommerce enqueueing this only on admin requests.
if (!function_exists('wc_get_page_screen_id')) {
    function wc_get_page_screen_id(): string
    {
        return '';
    }
}

