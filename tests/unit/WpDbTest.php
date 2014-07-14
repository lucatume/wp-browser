<?php

class WpDbTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    /**
     * @test
     * it should define a dontHaveInDatabase method
     */
    public function it_should_define_a_dont_have_in_database_method()
    {
        $this->assertTrue(method_exists($this->sut, 'dontHaveInDatabase'));
    }

    protected function setUp()
    {
        $class = '\Codeception\Module\WPDb';
        $this->sut = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods(['load']) // mock a method not under test
            ->getMock();
    }
}