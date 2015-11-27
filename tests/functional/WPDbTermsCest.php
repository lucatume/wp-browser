<?php
use BaconStringUtils\Slugifier;

class WPDbTermsCest {

	public function _before( FunctionalTester $I ) {
	}

	public function _after( FunctionalTester $I ) {
	}

	/**
	 * @test
	 * it should allow having a term in the database
	 */
	public function it_should_allow_having_a_term_in_the_database( FunctionalTester $I ) {
		list( $term_id, $term_taxonomy_id ) = $I->haveTermInDatabase( 'Term One', 'Taxonomy' );

		$I->seeTermInDatabase( [ 'name' => 'Term one', 'slug' => ( new Slugifier() )->slugify( 'Term One' ), 'term_group' => 0 ] );
		$I->seeTermInDatabase( [ 'term_taxonomy_id' => $term_taxonomy_id, 'term_id' => $term_id, 'taxonomy' => 'Taxonomy', 'description' => '', 'parent' => 0, 'count' => 0 ] );
	}

	/**
	 * @test
	 * it should allow overriding a term defaults
	 */
	public function it_should_allow_overriding_a_term_defaults( FunctionalTester $I ) {
		$overrides = [ 'slug' => 'some-slug', 'term_group' => 23, 'description' => 'A term', 'parent' => 45, 'count' => 121, ];
		list( $term_id, $term_taxonomy_id ) = $I->haveTermInDatabase( 'Term One', 'Taxonomy', $overrides );

		$I->seeTermInDatabase( [ 'term_id' => $term_id, 'name' => 'Term One', 'slug' => $overrides['slug'], 'term_group' => $overrides['term_group'] ] );
		$I->seeTermInDatabase( [ 'term_taxonomy_id' => $term_taxonomy_id, 'term_id' => $term_id, 'taxonomy' => 'Taxonomy', 'description' => $overrides['description'], 'parent' => $overrides['parent'], 'count' => $overrides['count'] ] );
	}

	/**
	 * @test
	 * it should allow not having a term in the database
	 */
	public function it_should_allow_not_having_a_term_in_the_database( FunctionalTester $I ) {
		list( $term_id, $term_taxonomy_id ) = $I->haveTermInDatabase( 'Term One', 'Taxonomy' );

		$I->seeTermInDatabase( [ 'name' => 'Term One' ] );

		$I->dontHaveTermInDatabase( [ 'name' => 'Term One' ] );

		$I->dontSeeTermInDatabase( [ 'name' => 'Term One' ] );
	}

	/**
	 * @test
	 * it should allow not to have a term in the database using term taxonomy
	 */
	public function it_should_allow_not_to_have_a_term_in_the_database_using_term_taxonomy( FunctionalTester $I ) {
		list( $term_id, $term_taxonomy_id ) = $I->haveTermInDatabase( 'Term One', 'Taxonomy' );

		$I->seeTermInDatabase( [ 'term_taxonomy_id' => $term_taxonomy_id ] );

		$I->dontHaveTermInDatabase( [ 'term_taxonomy_id' => $term_taxonomy_id ] );

		$I->dontSeeTermInDatabase( [ 'term_taxonomy_id' => $term_taxonomy_id ] );
	}
}

