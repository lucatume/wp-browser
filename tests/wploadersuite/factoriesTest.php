<?php


class factoriesTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @test
     * it should allow using the posts factory
     */
    public function it_should_allow_using_the_posts_factory()
    {
        $this->factory()->post->create_many(10, ['post_type' => 'post']);

        $this->assertCount(10, get_posts(['nopaging' => true, 'post_type' => 'post']));
    }
}
