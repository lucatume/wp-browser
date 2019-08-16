<?php

class FactoryTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * It should expose the blog factory on the tester property
     *
     * @test
     */
    public function should_expose_the_blog_factory_on_the_tester_property()
    {
        $this->assertInstanceOf(WP_UnitTest_Factory_For_Blog::class, $this->tester->factory()->blog);
    }
}
