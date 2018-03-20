<?php

namespace tad\WPBrowser\Module\WPLoader;

use Codeception\Exception\ModuleException;

class FiltersTest extends \Codeception\Test\Unit {
	/**
	 * It should allow setting filters to remove
	 *
	 * @test
	 */
	public function should_allow_setting_filters_to_remove() {
		$sut = new Filters( [
			'remove' => [
				[ 'some-filter', 'some_callback', 23 ],
				[ 'some-other-filter', 'some_callback' ],
				[ 'some-filter', 'some_callback' ],
			]
		] );

		$removed = [];
		$sut->removeWith( function ( $tag, $callback, $priority ) use ( &$removed ) {
			$removed[] = [ $tag, $callback, $priority ];

			return true;
		} );

		$sut->toRemove()->remove();

		$this->assertEquals( [
			[ 'some-filter', 'some_callback', 23 ],
			[ 'some-other-filter', 'some_callback', 10 ],
			[ 'some-filter', 'some_callback', 10 ],
		], $removed );
	}

	/**
	 * It should allow readding removed filters
	 *
	 * @test
	 */
	public function should_allow_readding_removed_filters() {
		$sut = new Filters( [
			'remove' => [
				[ 'some-filter', 'some_callback', 23 ],
				[ 'some-other-filter', 'some_callback', 10, 2 ],
				[ 'some-filter', 'some_callback' ],
			]
		] );

		$added = [];
		$sut->removeWith( function ( $tag, $callback, $priority ) {
		} );
		$sut->addWith( function ( $tag, $callback, $priority, $acceptedArgs ) use ( &$added ) {
			$added[] = [ $tag, $callback, $priority, $acceptedArgs ];
		} );

		$sut->toRemove()->add();

		$this->assertEquals( [
			[ 'some-filter', 'some_callback', 23, 1 ],
			[ 'some-other-filter', 'some_callback', 10, 2 ],
			[ 'some-filter', 'some_callback', 10, 1 ],
		], $added );
	}

	/**
	 * It should allow setting filters to add
	 *
	 * @test
	 */
	public function should_allow_setting_filters_to_add() {
		$sut = new Filters( [
			'add' => [
				[ 'some-filter', 'some_callback', 23 ],
				[ 'some-other-filter', 'some_callback' ],
				[ 'some-filter', 'some_callback', 12, 3 ],
			]
		] );

		$added = [];
		$sut->addWith( function ( $tag, $callback, $priority, $acceptedArgs ) use ( &$added ) {
			$added[] = [ $tag, $callback, $priority, $acceptedArgs ];

			return true;
		} );

		$sut->toAdd()->add();

		$this->assertEquals( [
			[ 'some-filter', 'some_callback', 23, 1 ],
			[ 'some-other-filter', 'some_callback', 10, 1 ],
			[ 'some-filter', 'some_callback', 12, 3 ],
		], $added );
	}

	/**
	 * It should allow removing added filters
	 *
	 * @test
	 */
	public function should_allow_removing_added_filters() {
		$sut = new Filters( [
			'add' => [
				[ 'some-filter', 'some_callback', 23 ],
				[ 'some-other-filter', 'some_callback' ],
				[ 'some-filter', 'some_callback', 12, 3 ],
			]
		] );

		$removed = [];
		$sut->addWith( function () {
		} );
		$sut->removeWith( function ( $tag, $callback, $priority ) use ( &$removed ) {
			$removed[] = [ $tag, $callback, $priority ];
		} );

		$sut->toAdd()->remove();

		$this->assertEquals( [
			[ 'some-filter', 'some_callback', 23 ],
			[ 'some-other-filter', 'some_callback', 10 ],
			[ 'some-filter', 'some_callback', 12 ],
		], $removed );
	}

	/**
	 * It should allow formatting the filters
	 *
	 * @test
	 */
	public function should_allow_formatting_the_filters() {
		$formatted = Filters::format( [
			'foo'    => 'bar',
			'add'    => [
				[ 'one', 'foo', 11 ],
				[ 'one', 'bar' ],
				[ 'two', 'foo', 23, 2 ],
			],
			'remove' => [
				[ 'one', 'foo', 11 ],
				[ 'one', 'bar' ],
				[ 'two', 'foo', 23, 2 ],
			]
		] );

		$this->assertEquals( [
			'remove' => [
				[ 'one', 'foo', 11, 1 ],
				[ 'one', 'bar', 10, 1 ],
				[ 'two', 'foo', 23, 2 ],
			],
			'add'    => [
				[ 'one', 'foo', 11, 1 ],
				[ 'one', 'bar', 10, 1 ],
				[ 'two', 'foo', 23, 2 ],
			]
		], $formatted );
	}

	public function badFilters() {
		return [
			[ [ 'foo', new \stdClass() ] ],
			[ [ new \stdClass(), 'foo' ] ],
			[ [ '', new \stdClass() ] ],
			[ [ '', '' ] ],
			[ [ '' ] ],
			[ [] ],
			[ [ 'foo', [ 'bar' ] ] ],
			[ [ 'foo', [ 'bar', new \stdClass() ] ] ],
			[ [ 'foo', [ new \stdClass(), 'bar' ] ] ],
			[ [ 'foo', 'bar', 10, 12, 23 ] ],
		];
	}

	/**
	 * It should throw if filters information is not correct
	 *
	 * @test
	 *
	 * @dataProvider badFilters
	 */
	public function should_throw_if_filters_information_is_not_correct( array $filters ) {
		$this->expectException( ModuleException::class );
		new Filters( [
			'remove' => [ $filters ]
		] );
	}
}