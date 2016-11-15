<?php
namespace tad\WPBrowser\Iterators\Filters;

use tad\WPBrowser\Iterators\Filters\ClassMethodQueriesFilter as Filter;

class ClassMethodQueriesFilterTest extends \Codeception\TestCase\Test
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

	private function make_instance($class = '', $method = '')
	{
		return new Filter(new \ArrayIterator($this->array), $class, $method);
	}

	public function queries()
	{
		return [
			[1,
				[
					['query 1', 'ms', 'someClass->methodOne'],
					['query 2', 'ms', 'someClass->methodTwo'],
					['update someTable', 'ms', 'someClass->methodThree']
				],
				'someClass',
				'methodOne'
			],
			[2,
				[
					['query 1', 'ms', 'someClass->methodOne'],
					['query 2', 'ms', 'someClass->methodOne'],
					['update someTable', 'ms', 'someClass->methodThree']
				],
				'someClass',
				'methodOne'
			],
			[3,
				[
					['query 1', 'ms', 'someClass->methodOne'],
					['query 2', 'ms', 'someClass->methodOne'],
					['update someTable', 'ms', 'someClass->methodOne']
				],
				'someClass',
				'methodOne'
			],
			[0,
				[
					['query 1', 'ms', 'someClass->methodOne'],
					['query 2', 'ms', 'someClass->methodOne'],
					['update someTable', 'ms', 'someClass->methodOne']
				],
				'someClass',
				'methodTwo'
			],
			[1,
				[
					['query 1', 'ms', 'someClass::methodTwo'],
					['query 2', 'ms', 'someClass->methodOne'],
					['update someTable', 'ms', 'someClass->methodOne']
				],
				'someClass',
				'methodTwo'
			],
			[2,
				[
					['query 1', 'ms', 'someClass::methodTwo'],
					['query 2', 'ms', 'someClass->methodOne'],
					['update someTable', 'ms', 'someClass->methodTwo']
				],
				'someClass',
				'methodTwo'
			],
		];
	}

	/**
	 * @test
	 * it should filter queries by class and method
	 * @dataProvider queries
	 */
	public function it_should_filter_queries_by_class_and_method($expectedCount, $queries, $class, $method)
	{
		$this->array = $queries;

		$sut = $this->make_instance($class, $method);

		$items = [];

		foreach ($sut as $item) {
			$items[] = $item;
		}

		$this->assertCount($expectedCount, $items);
	}
}