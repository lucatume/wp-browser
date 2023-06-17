<?php

namespace Acme;

class SomeClassTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \Fixer
     */
    protected $tester;
    
    public function setUp() :void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown() :void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_it_works()
    {
        $post = static::factory()->post->create_and_get();
        
        $this->assertInstanceOf(\WP_Post::class, $post);
    }
}
