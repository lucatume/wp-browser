<?php
tests_add_filter('status_header', function ($_, $code) {
    global $cli_statuses;
    $cli_statuses[] = $code;
}, 10, 2);