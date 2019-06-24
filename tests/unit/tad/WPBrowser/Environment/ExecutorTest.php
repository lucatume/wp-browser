<?php namespace tad\WPBrowser\Environment;

class ExecutorTest extends \Codeception\Test\Unit
{
    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(Executor::class, $sut);
    }

    /**
     * It should set the process timeout to default
     *
     * @test
     */
    public function should_set_the_process_timeout_to_default()
    {
        $executor = $this->make_instance();
        $executor->exec('echo "test"');
        $this->assertEquals(Executor::DEFAULT_TIMEOUT, $executor->getProcess()->getTimeout());
    }

    /**
     * It should set the process timeout to set value
     *
     * @test
     */
    public function should_set_the_process_timeout_to_set_value()
    {
        $executor = $this->make_instance();
        $executor->setTimeout(23);
        $executor->exec('echo "test"');
        $this->assertEquals(23, $executor->getProcess()->getTimeout());
    }

    /**
     * It should throw if trying to set value to non valid
     *
     * @test
     * @dataProvider invalidTimeoutValues
     */
    public function should_throw_if_trying_to_set_value_to_non_valid($timeout)
    {
        $this->expectException(\InvalidArgumentException::class);
        $executor = $this->make_instance();
        $executor->setTimeout($timeout);
    }

    /**
     * @return Executor
     */
    private function make_instance()
    {
        return new Executor();
    }

    public function invalidTimeoutValues()
    {
        return[
            'minus_one' => [-1],
            'minus_zero_point_one' => [-0.1],
            'string' => ['foo']
        ];
    }
}
