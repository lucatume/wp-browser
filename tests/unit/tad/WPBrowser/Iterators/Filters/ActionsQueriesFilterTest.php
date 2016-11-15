<?php
namespace tad\WPBrowser\Iterators\Filters;

use tad\WPBrowser\Iterators\Filters\ActionsQueriesFilter as Filter;

class ActionsQueriesFilterTest extends \Codeception\TestCase\Test
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
					['query 1', 'ms', "functionOne, do_action('some_action')"],
					['query 2', 'ms', 'functionTwo'],
					['update someTable', 'ms', 'functionThree']
				],
				'some_action'
			],
			[0,
				[
					['query 1', 'ms', "functionOne, do_action('another_action')"],
					['query 2', 'ms', 'functionTwo'],
					['update someTable', 'ms', 'functionThree']
				],
				'some_action'
			],
			[2,
				[
					['query 1', 'ms', "functionOne, do_action('some_action')"],
					['query 2', 'ms', "functionTwo, do_action('some_action')"],
					['update someTable', 'ms', "functionThree, do_action('another_action')"]
				],
				'some_action'
			],
			[1,
				[
					['query 1', 'ms', "functionOne, do_action('some_action')"],
					['query 2', 'ms', "functionTwo, do_action('some_action')"],
					['update someTable', 'ms', "functionThree, do_action('another_action')"]
				],
				'another_action'
			],
		];
	}

	/**
	 * @test
	 * it should filter queries by action
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