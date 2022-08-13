<?php


class WPDbTemplatingCest
{

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    /**
     * @test
     * it should allow passing number placeholder strings
     */
    public function it_should_allow_passing_number_placeholder_strings(FunctionalTester $I)
    {
        $ids = $I->haveManyPostsInDatabase(5, ['post_content' => 'Content of post {{n}}']);

        for ($i = 0; $i < 5; $i++) {
            $I->seePostInDatabase(['ID' => $ids[$i], 'post_content' => "Content of post {$i}"]);
        }
    }

    /**
     * @test
     * it should pass n in the many templates
     */
    public function it_should_pass_n_in_the_many_templates(FunctionalTester $I)
    {
        $ids = $I->haveManyPostsInDatabase(
            2,
            ['post_content' => 'Test post {{n}}']
        );

        $I->seePostInDatabase(['ID' => reset($ids), 'post_content' => "Test post 0"]);
        $I->seePostInDatabase(['ID' => last($ids), 'post_content' => "Test post 1"]);
    }

    /**
     * @test
     * it should allow passing template data with many templates
     */
    public function it_should_allow_passing_template_data_with_many_templates(FunctionalTester $I)
    {
        $ids = $I->haveManyPostsInDatabase(2, [
            'post_content' => 'Content of post {{n}}: {{content}}',
            'template_data' => ['content' => 'lorem ipsum.']
        ]);

        $I->seePostInDatabase(['ID' => reset($ids), 'post_content' => "Content of post 0: lorem ipsum."]);
        $I->seePostInDatabase(['ID' => last($ids), 'post_content' => "Content of post 1: lorem ipsum."]);
    }

    /**
     * @test
     * it should allow passing closure template data
     */
    public function it_should_allow_passing_closure_template_data(FunctionalTester $I)
    {
        $templateData = [
            'content' => function ($n) {
                $map = [
                    0 => 'first post content',
                    1 => 'second post content',
                    2 => 'third post content'
                ];

                return array_key_exists($n, $map) ? $map[$n] : 'some content';
            }
        ];
        $ids = $I->haveManyPostsInDatabase(5, [
            'post_content' => 'Content of post {{n}}: {{content}}',
            'template_data' => $templateData
        ]);

        $I->seePostInDatabase(['ID' => $ids[0], 'post_content' => "Content of post 0: first post content"]);
        $I->seePostInDatabase(['ID' => $ids[1], 'post_content' => "Content of post 1: second post content"]);
        $I->seePostInDatabase(['ID' => $ids[2], 'post_content' => "Content of post 2: third post content"]);
        $I->seePostInDatabase(['ID' => $ids[3], 'post_content' => "Content of post 3: some content"]);
        $I->seePostInDatabase(['ID' => $ids[4], 'post_content' => "Content of post 4: some content"]);
    }
}
