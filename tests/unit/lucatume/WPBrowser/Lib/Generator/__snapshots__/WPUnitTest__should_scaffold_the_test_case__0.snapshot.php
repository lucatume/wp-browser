<?php

namespace Acme;

class SomeClassTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    
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
    public function test_factory() :void
    {
        $post = static::factory()->post->create_and_get();
        
        $this->assertInstanceOf(\WP_Post::class, $post);
    }
}