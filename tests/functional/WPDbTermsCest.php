<?php
use BaconStringUtils\Slugifier;

class WPDbTermsCest
{
	/**
	 * @test
	 * it should allow having a term in the database
	 */
	public function it_should_allow_having_a_term_in_the_database(FunctionalTester $I)
	{
		list($term_id, $term_taxonomy_id) = $I->haveTermInDatabase('Term One', 'Taxonomy');

		$I->seeTermInDatabase([
			'name' => 'Term one',
			'slug' => (new Slugifier())->slugify('Term One'),
			'term_group' => 0
		]);
		$I->seeTermInDatabase([
			'term_taxonomy_id' => $term_taxonomy_id,
			'term_id' => $term_id,
			'taxonomy' => 'Taxonomy',
			'description' => '',
			'parent' => 0,
			'count' => 0
		]);
	}

	/**
	 * @test
	 * it should allow overriding a term defaults
	 */
	public function it_should_allow_overriding_a_term_defaults(FunctionalTester $I)
	{
		$overrides = [
			'slug' => 'some-slug',
			'term_group' => 23,
			'description' => 'A term',
			'parent' => 45,
			'count' => 121,
		];
		list($term_id, $term_taxonomy_id) = $I->haveTermInDatabase('Term One', 'Taxonomy', $overrides);

		$I->seeTermInDatabase([
			'term_id' => $term_id,
			'name' => 'Term One',
			'slug' => $overrides['slug'],
			'term_group' => $overrides['term_group']
		]);
		$I->seeTermInDatabase([
			'term_taxonomy_id' => $term_taxonomy_id,
			'term_id' => $term_id,
			'taxonomy' => 'Taxonomy',
			'description' => $overrides['description'],
			'parent' => $overrides['parent'],
			'count' => $overrides['count']
		]);
	}

	/**
	 * @test
	 * it should allow not having a term in the database
	 */
	public function it_should_allow_not_having_a_term_in_the_database(FunctionalTester $I)
	{
		list($term_id, $term_taxonomy_id) = $I->haveTermInDatabase('Term One', 'Taxonomy');

		$I->seeTermInDatabase(['name' => 'Term One']);

		$I->dontHaveTermInDatabase(['name' => 'Term One']);

		$I->dontSeeTermInDatabase(['name' => 'Term One']);
	}

	/**
	 * @test
	 * it should allow not to have a term in the database using term taxonomy
	 */
	public function it_should_allow_not_to_have_a_term_in_the_database_using_term_taxonomy(FunctionalTester $I)
	{
		list($term_id, $term_taxonomy_id) = $I->haveTermInDatabase('Term One', 'Taxonomy');

		$I->seeTermInDatabase(['term_taxonomy_id' => $term_taxonomy_id]);

		$I->dontHaveTermInDatabase(['term_taxonomy_id' => $term_taxonomy_id]);

		$I->dontSeeTermInDatabase(['term_taxonomy_id' => $term_taxonomy_id]);
	}

	/**
	 * @test
	 * it should allow having many terms in database
	 */
	public function it_should_allow_having_many_terms_in_database(FunctionalTester $I)
	{
		$termIds = $I->haveManyTermsInDatabase(5, 'Some Term', 'Taxonomy');
		$termIds = array_column($termIds, 0);

		for ($i = 0; $i < 5; $i++) {
			$expectedTermName = 'Some Term ' . $i;
			$criteria = [
				'term_id' => $termIds[$i],
				'name' => $expectedTermName,
				'slug' => (new Slugifier())->slugify($expectedTermName)
			];
			$I->seeTermInDatabase($criteria);
		}
	}

	/**
	 * @test
	 * it should allow having many terms with number placeholder
	 */
	public function it_should_allow_having_many_terms_with_number_placeholder(FunctionalTester $I)
	{
		$termIds = $I->haveManyTermsInDatabase(5, 'Term {{n}}', 'Taxonomy {{n}}');
		$termIds = array_column($termIds, 0);

		for ($i = 0; $i < 5; $i++) {
			$expectedTermName = 'Term ' . $i;
			$expectedTaxonomy = 'Taxonomy ' . $i;
			$termId = $termIds[$i];
			$criteria = [
				'term_id' => $termId,
				'name' => $expectedTermName,
				'slug' => (new Slugifier())->slugify($expectedTermName)
			];
			$I->seeTermInDatabase($criteria);

			$I->seeTermTaxonomyInDatabase(['term_id' => $termId, 'taxonomy' => $expectedTaxonomy]);
		}
		$I->dontSeeTermTaxonomyInDatabase(['taxonomy' => 'Taxonomy {{n}}']);
	}

	/**
	 * @test
	 * it should allow having term meta in the database
	 */
	public function it_should_allow_having_term_meta_in_the_database(FunctionalTester $I)
	{
		$termId = $I->haveTermInDatabase('term_one', 'tax_one');
		$termId = reset($termId);
		$metaOneId = $I->haveTermMetaInDatabase($termId, 'foo', 'bar');
		$objectMeta = (object)['one' => 1, 'two' => 'more'];
		$metaTwoId = $I->haveTermMetaInDatabase($termId, 'woo', $objectMeta);

		$I->seeTermMetaInDatabase([
			'meta_id' => $metaOneId,
			'term_id' => $termId,
			'meta_key' => 'foo',
			'meta_value' => 'bar'
		]);
		$I->seeTermMetaInDatabase([
			'meta_id' => $metaTwoId,
			'term_id' => $termId,
			'meta_key' => 'woo',
			'meta_value' => serialize($objectMeta)
		]);
	}

	/**
	 * @test
	 * it should allow not to have term meta in the database
	 */
	public function it_should_allow_not_to_have_term_meta_in_the_database(FunctionalTester $I)
	{
		$termId = $I->haveTermInDatabase('term_one', 'tax_one');
		$termId = reset($termId);
		$metaOneId = $I->haveTermMetaInDatabase($termId, 'foo', 'bar');
		$objectMeta = (object)['one' => 1, 'two' => 'more'];
		$metaTwoId = $I->haveTermMetaInDatabase($termId, 'woo', $objectMeta);

		$I->dontHaveTermMetaInDatabase(['term_id' => $termId, 'meta_key' => 'foo']);

		$I->dontSeeTermMetaInDatabase(['term_id' => $termId, 'meta_key' => 'foo']);
	}

	/**
	 * @test
	 * it should allow having term meta while having term
	 */
	public function it_should_allow_having_term_meta_while_having_term(FunctionalTester $I)
	{
		$objectMeta = (object)[
			'one' => 2,
			'three' => 4
		];
		$termIds = $I->haveTermInDatabase('some_term', 'some_taxonomy', [
			'meta' => [
				'foo' => 'bar',
				'baz' => $objectMeta
			]
		]);
		$termId = reset($termIds);

		$I->seeTermInDatabase(['term_id' => $termId]);
		$I->seeTermMetaInDatabase(['term_id' => $termId, 'meta_key' => 'foo', 'meta_value' => 'bar']);
		$I->seeTermMetaInDatabase([
			'term_id' => $termId,
			'meta_key' => 'baz',
			'meta_value' => serialize($objectMeta)
		]);
	}

	/**
	 * @test
	 * it should allow having many terms meta
	 */
	public function it_should_allow_having_many_terms_meta(FunctionalTester $I)
	{
		$ids = $I->haveManyTermsInDatabase(5, 'some_term', 'some_taxonomy', ['meta' => ['foo' => 'bar']]);

		$termIds = array_column($ids, 0);
		for ($i = 0; $i < 5; $i++) {
			$I->seeTermMetaInDatabase(['term_id' => $termIds[$i], 'meta_key' => 'foo', 'meta_value' => 'bar']);
		}
	}

	/**
	 * @test
	 * it should allow having many terms meta with number placeholder
	 */
	public function it_should_allow_having_many_terms_meta_with_number_placeholder(FunctionalTester $I)
	{
		$ids = $I->haveManyTermsInDatabase(5, 'some_term', 'some_taxonomy',
			['meta' => ['foo_of_{{n}}' => 'bar_of_{{n}}']]);

		$termIds = array_column($ids, 0);
		for ($i = 0; $i < 5; $i++) {
			$I->seeTermMetaInDatabase([
				'term_id' => $termIds[$i],
				'meta_key' => 'foo_of_' . $i,
				'meta_value' => 'bar_of_' . $i
			]);
		}
	}
}

