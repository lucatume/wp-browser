<?php

use Wploader_wpdb_interactionTester as Tester;

class FactoryCest
{
    /**
     * It should allow getting the post factory from the tester
     *
     * @test
     */
    public function should_allow_getting_the_post_factory_from_the_tester(Tester $I): void
    {
        $postFactory = $I->factory()->post;
        $I->assertInstanceOf(\WP_Post::class, $postFactory->create_and_get());
    }
}
