<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class RequestTimeTest extends WPTestCase
{
    public function test_server_unset_time()
    {
        unset($_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME_FLOAT']);
    }
}
