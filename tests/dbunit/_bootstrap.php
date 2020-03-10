<?php

use function tad\WPBrowser\Tests\Support\env;
use function tad\WPBrowser\Tests\Support\importDump;

$import = static function (array $filesMap) {
    $env = env();
    foreach ($filesMap as $file => $dbNameEnvVar) {
        importDump(
            codecept_data_dir('dump.sql'),
            $env($dbNameEnvVar),
            $env('WORDPRESS_DB_USER'),
            $env('WORDPRESS_DB_PASSWORD'),
            $env('WORDPRESS_DB_HOST')
        );
    }
};

$import (
    [
        'dump.sql' => 'WORDPRESS_DB_NAME',
        'mu-subdir-dump.sql' => 'WORDPRESS_SUBDIR_DB_NAME',
        'mu-subdomain-dump.sql' => 'WORDPRESS_SUBDOMAIN_DB_NAME'
    ]
);
