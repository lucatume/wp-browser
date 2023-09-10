<?php
namespace lucatume\WPBrowser\Iterators\Filters;

use ArrayObject;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Iterators\Filters\FactoryQueriesFilter as Filter;
use UnitTester;

class FactoryQueriesFilterTest extends Unit
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

        $this->assertInstanceOf('lucatume\WPBrowser\Iterators\Filters\FactoryQueriesFilter', $sut);
    }

    private function make_instance(): FactoryQueriesFilter
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
                'a stack trace including WP_UnitTest_Factory_For_Thing->create'
            ],
            [
                'second SQL statement',
                'some ms timing',
                'a stack trace not including WP_UnitTest_Factory_For_Thing create calls'
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
                'a stack trace including WP_UnitTest_Factory_For_Thing->create'
            ],
            [
                'some SQL statement',
                'some ms timing',
                'a stack trace including WP_UnitTest_Factory_For_Thing->create'
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
