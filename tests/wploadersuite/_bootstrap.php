<?php
// Here you can initialize variables that will be available to your tests

// At bootstrap file time, WordPress should be loaded.
if (!class_exists(WP_Post::class)) {
    throw new RuntimeException('WP_Post class not found');
}
