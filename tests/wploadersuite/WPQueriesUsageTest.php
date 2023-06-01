<?php

use lucatume\WPBrowser\Module\WPQueries;
use lucatume\WPBrowser\TestCase\WPTestCase;

class WPQueriesUsageTest extends WPTestCase
{

    /**
     * It should provide the WPQueries method
     *
     * @test
     */
    public function should_provide_the_wp_queries_method()
    {
        $this->assertInstanceOf(WPQueries::class, $this->queries());
    }

    /**
     * It should not count factory queries
     *
     * @test
     */
    public function should_not_count_factory_queries()
    {
        $currentQueriesCount = $this->queries()->countQueries();

        $this->assertNotEmpty($currentQueriesCount);

        static::factory()->post->create_many(3);

        $this->assertNotEmpty($currentQueriesCount);
        $this->assertEquals($currentQueriesCount, $this->queries()->countQueries());
    }

    /**
     * It should allow testing queries
     *
     * @test
     */
    public function should_allow_testing_queries()
    {
        $currentQueriesCount = $this->queries()->countQueries();

        $this->assertNotEmpty($currentQueriesCount);

        foreach (range(1, 3) as $i) {
            wp_insert_post([
                'post_title' => 'Post ' . $i,
                'post_content' => str_repeat('test', $i)
            ]);
        }

        $this->assertNotEmpty($currentQueriesCount);
        $this->assertGreaterThan($currentQueriesCount, $this->queries()->countQueries());
    }
}
