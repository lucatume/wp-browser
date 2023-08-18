<?php

require_once __DIR__ . '/../vendor/autoload.php';


define('WPMU_PLUGIN_DIR', __DIR__ . '/fixtures/mu-plugins');
define('ABSPATH', __DIR__ . '/fixtures/wp/');

// Can't activate strict mode due to untestable code
// WP_Mock::activateStrictMode();
WP_Mock::bootstrap();

