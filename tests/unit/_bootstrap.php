<?php
// Here you can initialize variables that will be available to your tests

use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;

require_once dirname(__DIR__, 2) . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php';

Env::loadEnvMap(
    [
        'WPBROWSER_WORDPRESS_SOURCE_DIR' => FS::realpath(FS::cacheDir() . '/wordpress'),
        'WPBROWSER_CHROMEDRIVER_ZIP_FILE' => FS::realpath(FS::cacheDir() . '/chromedriver/chromedriver.zip')
    ]
);
