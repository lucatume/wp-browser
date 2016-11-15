<?php
namespace tad\WPBrowser\Iterators\Filters;

use tad\WPBrowser\Iterators\Filters\FiltersQueriesFilter as Filter;

class FiltersQueriesFilterTest extends \Codeception\TestCase\Test
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var array
	 */
	protected $array = [];

	/**
	 * @test
	 * it should return empty array if filtering empty array
	 */
	public function it_should_return_empty_array_if_filtering_empty_array()
	{
		$this->array = [];

		$sut = $this->make_instance();

		$items = [];

		foreach ($sut as $item) {
			$items[] = $item;
		}

		$this->assertCount(0, $items);
	}

	private function make_instance($f = '')
	{
		return new Filter(new \ArrayIterator($this->array), $f);
	}

	public function queries()
	{
		return [
			[1,
				[
					['query 1', 'ms', "functionOne, apply_filters('some_filter')"],
					['query 2', 'ms', 'functionTwo'],
					['update someTable', 'ms', 'functionThree']
				],
				'some_filter'
			],
			[0,
				[
					['query 1', 'ms', "functionOne, apply_filters('another_filter')"],
					['query 2', 'ms', 'functionTwo'],
					['update someTable', 'ms', 'functionThree']
				],
				'some_filter'
			],
			[2,
				[
					['query 1', 'ms', "functionOne, apply_filters('some_filter')"],
					['query 2', 'ms', "functionTwo, apply_filters('some_filter')"],
					['update someTable', 'ms', "functionThree, apply_filters('another_filter')"]
				],
				'some_filter'
			],
			[1,
				[
					['query 1', 'ms', "functionOne, apply_filters('some_filter')"],
					['query 2', 'ms', "functionTwo, apply_filters('some_filter')"],
					['update someTable', 'ms', "functionThree, apply_filters('another_filter')"]
				],
				'another_filter'
			],
		];
	}

	/**
	 * @test
	 * it should filter queries by filter
	 * @dataProvider queries
	 */
	public function it_should_filter_queries_by_filter($expectedCount, $queries, $f)
	{
		$this->array = $queries;

		$sut = $this->make_instance($f);

		$items = [];

		foreach ($sut as $item) {
			$items[] = $item;
		}

		$this->assertCount($expectedCount, $items);
	}
}