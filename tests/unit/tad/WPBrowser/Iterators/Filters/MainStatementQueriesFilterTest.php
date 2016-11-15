<?php
namespace tad\WPBrowser\Iterators\Filters;

use tad\WPBrowser\Iterators\Filters\MainStatementQueriesFilter as Filter;

class MainStatementQueriesFilterTest extends \Codeception\TestCase\Test
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

	private function make_instance($statement = 'SELECT')
	{
		return new Filter(new \ArrayIterator($this->array), $statement);
	}

	public function queries()
	{
		return [
			[1,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['insert into', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'select'
			],
			[1,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['insert into', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'SELECT'
			],
			[1,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['insert into', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'Select'
			],
			[1,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['insert into', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'insert'
			],
			[1,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['insert into', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'INSERT'
			],
			[1,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['insert into', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'Insert'
			],
			[2,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['select * from', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'SELECT'
			],
			[2,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['select * from', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'select'
			],
			[2,
				[
					['SELECT * from', 'ms', 'stack trace'],
					['select * from', 'ms', 'stack trace'],
					['update someTable', 'ms', 'stack trace']
				],
				'Select'
			],
			[2,
				[
					['SELECT * from (INSERT INTO...', 'ms', 'stack trace'],
					['select * from (UPDATE someTable...', 'ms', 'stack trace'],
					['update someTable ... select(...', 'ms', 'stack trace']
				],
				'SELECT'
			],
			[1,
				[
					['SELECT * from (INSERT INTO...', 'ms', 'stack trace'],
					['select * from (UPDATE someTable...', 'ms', 'stack trace'],
					['update someTable ... select(...', 'ms', 'stack trace']
				],
				'update'
			],
			[0,
				[
					['SELECT * from (INSERT INTO...', 'ms', 'stack trace'],
					['select * from (UPDATE someTable...', 'ms', 'stack trace'],
					['update someTable ... select(...', 'ms', 'stack trace']
				],
				'insert'
			],
		];
	}

	/**
	 * @test
	 * it should filter queries by main statement
	 * @dataProvider queries
	 */
	public function it_should_filter_queries_by_main_statement($expectedCount, $queries, $statement)
	{
		$this->array = $queries;

		$sut = $this->make_instance($statement);

		$items = [];

		foreach ($sut as $item) {
			$items[] = $item;
		}

		$this->assertCount($expectedCount, $items);
	}
}