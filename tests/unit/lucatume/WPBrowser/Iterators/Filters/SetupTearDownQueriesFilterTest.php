<?php
namespace lucatume\WPBrowser\Iterators\Filters;

use ArrayObject;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Iterators\Filters\SetupTearDownQueriesFilter as Filter;
use UnitTester;

class SetupTearDownQueriesFilterTest extends Unit
{
    protected $backupGlobals = false;
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    protected $array = [];

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf('lucatume\WPBrowser\Iterators\Filters\SetupTearDownQueriesFilter', $sut);
    }

    private function make_instance(): SetupTearDownQueriesFilter
    {
        return new Filter((new ArrayObject($this->array))->getIterator());
    }

    /**
     * @test
     * it should return empty array when filtering empty array
     */
    public function it_should_return_empty_array_when_filtering_empty_array()
    {
        $this->array = [];

        $sut = $this->make_instance();

        $items = [];

        foreach ($sut as $item) {
            $items[] = $item;
        }

        $this->assertCount(0, $items);
    }

    /**
     * @test
     * it should filter out WPTestCase setUp method generated queries
     */
    public function it_should_filter_out_wp_test_case_set_up_method_generated_queries()
    {
        $this->array = [
            [
                'some SQL statement',
                'some ms timing',
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp'
            ],
            [
                'second SQL statement',
                'some ms timing',
                'a stack trace not including lucatume\WPBrowser\TestCase\WPTestCase setUp methods'
            ],
        ];

        $sut = $this->make_instance();

        $items = [];

        foreach ($sut as $item) {
            $items[] = $item;
        }

        $this->assertCount(1, $items);
        $this->assertEquals('second SQL statement', $items[0][0]);
    }

    /**
     * @test
     * it should filter out WPTestCase tearDown method generated queries
     */
    public function it_should_filter_out_wp_test_case_tear_down_method_generated_queries()
    {
        $this->array = [
            [
                'some SQL statement',
                'some ms timing',
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown'
            ],
            [
                'second SQL statement',
                'some ms timing',
                'a stack trace not including lucatume\WPBrowser\TestCase\WPTestCase tearDown methods'
            ],
        ];

        $sut = $this->make_instance();

        $items = [];

        foreach ($sut as $item) {
            $items[] = $item;
        }

        $this->assertCount(1, $items);
        $this->assertEquals('second SQL statement', $items[0][0]);
    }

    /**
     * @test
     * it should return empty array if only queries are from setUp and tearDown methods
     */
    public function it_should_return_empty_array_if_only_queries_are_from_set_up_and_tear_down_methods()
    {
        $this->array = [
            [
                'some SQL statement',
                'some ms timing',
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->setUp'
            ],
            [
                'some SQL statement',
                'some ms timing',
                'a stack trace including lucatume\WPBrowser\TestCase\WPTestCase->tearDown'
            ],
        ];

        $sut = $this->make_instance();

        $items = [];

        foreach ($sut as $item) {
            $items[] = $item;
        }

        $this->assertCount(0, $items);
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
