<?php
// This is global bootstrap for autoloading.

use function tad\WPBrowser\Tests\Support\db;
use function tad\WPBrowser\Tests\Support\env;

$env = env();
$host = $env('WORDPRESS_DB_HOST');
$user = $env('WORDPRESS_DB_USER');
$pass = $env('WORDPRESS_DB_PASSWORD');
$subfolderDb = $env('WORDPRESS_SUBDIR_DB_NAME');
$subdomainDb = $env('WORDPRESS_SUBDOMAIN_DB_NAME');
$emptyDb = $env('WORDPRESS_EMPTY_DB_NAME');
$db = db($host, $user, $pass);
$db("CREATE DATABASE IF NOT EXISTS {$subfolderDb}");
$db("CREATE DATABASE IF NOT EXISTS {$subdomainDb}");
$db("CREATE DATABASE IF NOT EXISTS {$emptyDb}");

// If the `uopz` extension is installed, then allow `exit` and `die` to work normally.
if (function_exists('uopz_allow_exit')) {
    uopz_allow_exit(true);
}
