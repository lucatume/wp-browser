<?php
namespace tad\WPBrowser\Iterators\Filters;

use tad\WPBrowser\Iterators\Filters\FunctionQueriesFilter as Filter;

class FunctionQueriesFilterTest extends \Codeception\TestCase\Test
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
					['query 1', 'ms', 'functionOne'],
					['query 2', 'ms', 'functionTwo'],
					['update someTable', 'ms', 'functionThree']
				],
				'functionOne'
			],
			[2,
				[
					['query 1', 'ms', 'functionOne'],
					['query 2', 'ms', 'functionOne'],
					['update someTable', 'ms', 'functionThree']
				],
				'functionOne'
			],
			[3,
				[
					['query 1', 'ms', 'functionOne'],
					['query 2', 'ms', 'functionOne'],
					['update someTable', 'ms', 'functionOne']
				],
				'functionOne'
			],
			[0,
				[
					['query 1', 'ms', 'functionOne'],
					['query 2', 'ms', 'functionOne'],
					['update someTable', 'ms', 'functionOne']
				],
				'functionTwo'
			],
			[1,
				[
					['query 1', 'ms', 'functionTwo'],
					['query 2', 'ms', 'functionOne'],
					['update someTable', 'ms', 'functionOne']
				],
				'functionTwo'
			],
			[2,
				[
					['query 1', 'ms', 'functionTwo'],
					['query 2', 'ms', 'functionOne'],
					['update someTable', 'ms', 'functionTwo']
				],
				'functionTwo'
			],
			[1,
				[
					['query 1', 'ms', 'functionOne'],
					['query 2', 'ms', "apply_filters('functionOne')"],
					['update someTable', 'ms', 'functionTwo']
				],
				'functionOne'
			],
			[1,
				[
					['query 1', 'ms', 'functionOne'],
					['query 2', 'ms', "do_action('functionOne')"],
					['update someTable', 'ms', 'functionTwo']
				],
				'functionOne'
			],
		];
	}

	/**
	 * @test
	 * it should filter queries by function
	 * @dataProvider queries
	 */
	public function it_should_filter_queries_by_function($expectedCount, $queries, $f)
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