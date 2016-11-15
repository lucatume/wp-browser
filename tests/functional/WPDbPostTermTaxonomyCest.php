<?php

class WPDbPostTermTaxonomyCest
{


	public function _before(FunctionalTester $I)
	{
	}

	public function _after(FunctionalTester $I)
	{
	}

	/**
	 * @test
	 * it should allow assigning a post an existing taxonomy term
	 */
	public function it_should_allow_assigning_a_post_an_existing_taxonomy_term(FunctionalTester $I)
	{
		list($termId, $termTaxonomyId) = $I->haveTermInDatabase('some_term', 'some_taxonomy');
		$postId = $I->havePostInDatabase(['terms' => ['some_taxonomy' => ['some_term']]]);

		$I->seeInDatabase($I->grabTermRelationshipsTableName(),
			['object_id' => $postId, 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
		$I->seeInDatabase($I->grabTermTaxonomyTableName(),
			['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'some_taxonomy', 'count' => 1]);
	}

	/**
	 * @test
	 * it should create missing terms assigned to post
	 */
	public function it_should_create_missing_terms_assigned_to_post(FunctionalTester $I)
	{
		$postId = $I->havePostInDatabase(['terms' => ['taxonomy_two' => ['term_one']]]);

		$termId = $I->grabTermIdFromDatabase(['name' => 'term_one']);
		$termTaxonomyId = $I->grabTermTaxonomyIdFromDatabase(['term_id' => $termId, 'taxonomy' => 'taxonomy_two']);
		$I->seeInDatabase($I->grabTermRelationshipsTableName(),
			['object_id' => $postId, 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
		$I->seeInDatabase($I->grabTermTaxonomyTableName(),
			['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'taxonomy_two', 'count' => 1]);
	}

	/**
	 * @test
	 * it should allow inserting many posts with same term
	 */
	public function it_should_allow_inserting_many_posts_with_same_term(FunctionalTester $I)
	{
		$postIds = $I->haveManyPostsInDatabase(5, ['terms' => ['taxonomy_three' => ['term_one']]]);

		$termId = $I->grabTermIdFromDatabase(['name' => 'term_one']);
		$termTaxonomyId = $I->grabTermTaxonomyIdFromDatabase(['term_id' => $termId, 'taxonomy' => 'taxonomy_three']);
		foreach ($postIds as $postId) {
			$I->seeInDatabase($I->grabTermRelationshipsTableName(),
				['object_id' => $postId, 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
		}
		$I->seeInDatabase($I->grabTermTaxonomyTableName(),
			['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'taxonomy_three', 'count' => 5]);
	}

	/**
	 * @test
	 * it should apply number placeholder to taxonomy terms too
	 */
	public function it_should_apply_number_placeholder_to_taxonomy_terms_too(FunctionalTester $I)
	{
		$postIds = $I->haveManyPostsInDatabase(3, ['terms' => ['taxonomy_{{n}}' => ['term_{{n}}']]]);

		for ($i = 0; $i < 3; $i++) {
			$termId = $I->grabTermIdFromDatabase(['name' => 'term_' . $i]);
			$termTaxonomyId = $I->grabTermTaxonomyIdFromDatabase([
				'term_id' => $termId,
				'taxonomy' => 'taxonomy_' . $i
			]);
			$I->seeInDatabase($I->grabTermRelationshipsTableName(),
				['object_id' => $postIds[$i], 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
			$I->seeInDatabase($I->grabTermTaxonomyTableName(),
				['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'taxonomy_' . $i, 'count' => 1]);
		}
	}

	/**
	 * @test
	 * it should allow assigning a post an existing taxonomy term
	 */
	public function it_should_allow_assigning_a_post_an_existing_taxonomy_tax_input(FunctionalTester $I)
	{
		list($termId, $termTaxonomyId) = $I->haveTermInDatabase('some_term', 'some_taxonomy');
		$postId = $I->havePostInDatabase(['tax_input' => ['some_taxonomy' => ['some_term']]]);

		$I->seeInDatabase($I->grabTermRelationshipsTableName(),
			['object_id' => $postId, 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
		$I->seeInDatabase($I->grabTermTaxonomyTableName(),
			['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'some_taxonomy', 'count' => 1]);
	}

	/**
	 * @test
	 * it should create missing terms assigned to post
	 */
	public function it_should_create_missing_tax_input_assigned_to_post(FunctionalTester $I)
	{
		$postId = $I->havePostInDatabase(['tax_input' => ['taxonomy_two' => ['term_one']]]);

		$termId = $I->grabTermIdFromDatabase(['name' => 'term_one']);
		$termTaxonomyId = $I->grabTermTaxonomyIdFromDatabase(['term_id' => $termId, 'taxonomy' => 'taxonomy_two']);
		$I->seeInDatabase($I->grabTermRelationshipsTableName(),
			['object_id' => $postId, 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
		$I->seeInDatabase($I->grabTermTaxonomyTableName(),
			['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'taxonomy_two', 'count' => 1]);
	}

	/**
	 * @test
	 * it should allow inserting many posts with same term
	 */
	public function it_should_allow_inserting_many_posts_with_same_tax_input(FunctionalTester $I)
	{
		$postIds = $I->haveManyPostsInDatabase(5, ['tax_input' => ['taxonomy_three' => ['term_one']]]);

		$termId = $I->grabTermIdFromDatabase(['name' => 'term_one']);
		$termTaxonomyId = $I->grabTermTaxonomyIdFromDatabase(['term_id' => $termId, 'taxonomy' => 'taxonomy_three']);
		foreach ($postIds as $postId) {
			$I->seeInDatabase($I->grabTermRelationshipsTableName(),
				['object_id' => $postId, 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
		}
		$I->seeInDatabase($I->grabTermTaxonomyTableName(),
			['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'taxonomy_three', 'count' => 5]);
	}

	/**
	 * @test
	 * it should apply number placeholder to taxonomy tax_input too
	 */
	public function it_should_apply_number_placeholder_to_taxonomy_tax_input_too(FunctionalTester $I)
	{
		$postIds = $I->haveManyPostsInDatabase(3, ['tax_input' => ['taxonomy_{{n}}' => ['term_{{n}}']]]);

		for ($i = 0; $i < 3; $i++) {
			$termId = $I->grabTermIdFromDatabase(['name' => 'term_' . $i]);
			$termTaxonomyId = $I->grabTermTaxonomyIdFromDatabase([
				'term_id' => $termId,
				'taxonomy' => 'taxonomy_' . $i
			]);
			$I->seeInDatabase($I->grabTermRelationshipsTableName(),
				['object_id' => $postIds[$i], 'term_taxonomy_id' => $termTaxonomyId, 'term_order' => 0]);
			$I->seeInDatabase($I->grabTermTaxonomyTableName(),
				['term_taxonomy_id' => $termTaxonomyId, 'taxonomy' => 'taxonomy_' . $i, 'count' => 1]);
		}
	}
}
