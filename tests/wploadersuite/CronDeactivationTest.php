<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class CronDeactivationTest extends WPTestCase
{
    /**
     * It should disable CRON by default
     *
     * @test
     */
    public function should_disable_cron_by_default()
    {
        $this->assertEquals(true, (bool)DISABLE_WP_CRON);
    }
}
