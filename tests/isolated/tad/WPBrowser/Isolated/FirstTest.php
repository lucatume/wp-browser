<?php
namespace tad\WPBrowser\Isolated;

class FirstTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \IsolatedTester
     */
    protected $tester;

    public function setUp()
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown()
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_running_in_separate_process_one()
    {
        $post = static::factory()->post->create_and_get();

        $this->assertInstanceOf(\WP_Post::class, $post);
        $this->assertCount(1, get_posts());

        define('TEST_CONST', 23);

        $this->assertTrue(defined('TEST_CONST'));
        $this->assertEquals(23, TEST_CONST);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_running_in_separate_process_two()
    {
        $post = static::factory()->post->create_and_get();

        $this->assertInstanceOf(\WP_Post::class, $post);
        $this->assertCount(1, get_posts());

        $this->assertFalse(defined('TEST_CONST'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_running_in_separate_process_three()
    {
        $post = static::factory()->post->create_and_get();

        $this->assertInstanceOf(\WP_Post::class, $post);
        $this->assertCount(1, get_posts());

        $this->assertFalse(defined('TEST_CONST'));
    }
}
