<?php

/**
 * Filter the `status_header` actiion to log response stati set during WordPress execution.
 */
tests_add_filter('status_header', function ($_, $code) {
    global $cli_statuses;
    $cli_statuses[] = $code;
}, 10, 2);