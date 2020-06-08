<?php

class RequestTimeTest extends \Codeception\TestCase\WPTestCase
{
    public function test_server_unset_time()
    {
        unset($_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME_FLOAT']);
    }
}
