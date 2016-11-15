<?php


class WPDbHandlebarsCest
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
		$ids = $I->haveManyPostsInDatabase(2,
			['post_content' => 'Lorem{{#if n}} ipsum dolor sit{{/if}}{{#unless n}} foo{{/unless}}']);

		$I->seePostInDatabase(['ID' => reset($ids), 'post_content' => "Lorem foo"]);
		$I->seePostInDatabase(['ID' => last($ids), 'post_content' => "Lorem ipsum dolor sit"]);
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

	/**
	 * @test
	 * it should allow for nested handlebar helpers
	 */
	public function it_should_allow_for_nested_handlebar_helpers(FunctionalTester $I)
	{
		$templateData = [
			'content' => function ($n) {
				$map = [
					0 => 'first post content',
					1 => 'second post content',
					2 => 'third post content'
				];

				return array_key_exists($n, $map) ? $map[$n] : 'some content';
			},
			'odd' => function ($n) {
				return $n % 2;
			}
		];
		$ids = $I->haveManyPostsInDatabase(5, [
			'post_content' => 'Content of post {{n}}: {{#if odd}}(odd) {{/if}}{{content}}',
			'template_data' => $templateData
		]);

		$I->seePostInDatabase(['ID' => $ids[0], 'post_content' => "Content of post 0: first post content"]);
		$I->seePostInDatabase(['ID' => $ids[1], 'post_content' => "Content of post 1: (odd) second post content"]);
		$I->seePostInDatabase(['ID' => $ids[2], 'post_content' => "Content of post 2: third post content"]);
		$I->seePostInDatabase(['ID' => $ids[3], 'post_content' => "Content of post 3: (odd) some content"]);
		$I->seePostInDatabase(['ID' => $ids[4], 'post_content' => "Content of post 4: some content"]);
	}
}
