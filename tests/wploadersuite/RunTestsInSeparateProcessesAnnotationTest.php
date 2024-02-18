<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * @runTestsInSeparateProcesses
 */
class RunTestsInSeparateProcessesAnnotationTest extends WPTestCase
{

    public function test_setting_a_constant(): void
    {
        define('TEST_CONST', 23);

        $this->assertEquals(23, TEST_CONST);
    }

    public function test_setting_another_constant(): void
    {
        define('TEST_CONST_2', 89);

        $this->assertFalse(defined('TEST_CONST'));
        $this->assertEquals(89, TEST_CONST_2);
    }

    public function test_using_post_factory(): void
    {
        $post = static::factory()->post->create();

        $this->assertInstanceOf(\WP_Post::class, get_post($post));
    }
}
