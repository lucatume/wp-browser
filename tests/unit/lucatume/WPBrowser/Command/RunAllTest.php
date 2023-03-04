<?php


namespace Unit\lucatume\WPBrowser\Command;

use Codeception\Configuration;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Command\RunAll;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class RunAllTest extends Unit
{
    use SnapshotAssertions;
    use UopzFunctions;
    use ClassStubs;

    public function test_descritpion_and_command_name(): void
    {
        $command = new RunAll();
        $nameAndDescription = $command->getName() . '-' . $command->getDescription();
        $this->assertMatchesStringSnapshot($nameAndDescription);
    }

    /**
     * It should invoke codecept bin once for each suite
     *
     * @test
     */
    public function should_invoke_codecept_bin_once_for_each_suite(): void
    {
        $calls = 0;
        $mockParams = [
            '__construct' => function (array $command) use (&$calls) {
                global $_composer_bin_dir;
                $expectedCommand = [
                    "$_composer_bin_dir/codecept",
                    'run',
                    'suite-' . ++$calls
                ];
                Assert::assertEquals($expectedCommand, $command);
            },
            'getIterator' => fn() => yield from ["Running suite\n", "Done\n"],
            'isSuccessful' => fn() => true,
        ];
        $this->uopzSetMock(Process::class, $this->makeEmptyClass(Process::class, $mockParams));
        $this->uopzSetStaticMethodReturn(Configuration::class, 'suites', ['suite-1', 'suite-2', 'suite-3']);

        $command = new RunAll();
        $output = new BufferedOutput();
        $return = $command->run(new ArrayInput([], $command->getDefinition()), $output);

        $this->assertEquals(0, $return);
        $this->assertEquals("Running suite\nDone\nRunning suite\nDone\nRunning suite\nDone\n", $output->fetch());
    }
}
