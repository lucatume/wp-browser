<?php


namespace Unit\lucatume\WPBrowser\Process;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Process\Loop;

class LoopTest extends Unit
{
    /**
     * It should work using normal payloads
     *
     * @test
     */
    public function should_work_using_normal_payloads(): void
    {
        $job = static function () {
            return 'Hello from the loop';
        };
        $loop = new Loop([$job]);
        $loop->setUseFilePayloads(false);

        $loop->run();
        $results = $loop->getResults();

        $this->assertEquals('Hello from the loop', $results[0]->getReturnValue());
    }

    /**
     * It should work using file payloads
     *
     * @test
     */
    public function should_work_using_file_payloads(): void
    {
        $job = static function () {
            return 'Hello from the loop with file payloads';
        };
        $loop = new Loop([$job]);
        $loop->setUseFilePayloads(true);

        $loop->run();
        $results = $loop->getResults();

        $this->assertEquals('Hello from the loop with file payloads', $results[0]->getReturnValue());
    }
}
