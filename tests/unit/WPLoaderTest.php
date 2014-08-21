<?php

use tad\wordpress\loader\WPLoader;

class WPLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $this->assertInstanceOf('\tad\wordpress\loader\WPLoader', new WPLoader());
    }
}