<?php

class ExtendedMySqlTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = $this->getMockBuilder('\Codeception\Lib\Driver\ExtendedMySql')
            ->disableOriginalConstructor()
            ->setMethods(['load']) // mock a method not under test
            ->getMock();
    }

    /**
     * @test
     * it should generate proper delete statements
     */
    public function it_should_generate_proper_delete_statements()
    {
        $expected = 'DELETE FROM Customers WHERE CustomerName="Alfred" AND ContactName="Maria"';
        $output = $this->sut->delete('Customers', ['CustomerName' => 'Alfred', 'ContactName' => 'Maria']);
        $this->assertEquals($expected, $output);
    }

    /**
     * @test
     * it should generate proper delete statement for single criteria
     */
    public function it_should_generate_proper_delete_statement_for_single_criteria()
    {
        $expected = 'DELETE FROM Customers WHERE CustomerName="Alfred"';
        $output = $this->sut->delete('Customers', ['CustomerName' => 'Alfred']);
        $this->assertEquals($expected, $output);
    }

    /**
     * @test
     * it should generate proper insert statement for single criteria
     */
    public function it_should_generate_proper_insert_statement_for_single_criteria()
    {
        $pattern = '/^INSERT INTO .* ON DUPLICATE KEY UPDATE CustomerName="Alfred"$/';
        $output = $this->sut->insertOrUpdate('Customers', ['CustomerName' => 'Alfred']);
        $this->assertRegExp($pattern, $output);
    }

    /**
     * @test
     * it should generate proper insert statement for multiple criteria
     */
    public function it_should_generate_proper_insert_statement_for_multiple_criteria()
    {
        $pattern = '/^INSERT INTO .* ON DUPLICATE KEY UPDATE CustomerName="Alfred", ContactName="Maria"$/';
        $output = $this->sut->insertOrUpdate('Customers', ['CustomerName' => 'Alfred', 'ContactName' => 'Maria']);
        $this->assertRegExp($pattern, $output);
    }
}