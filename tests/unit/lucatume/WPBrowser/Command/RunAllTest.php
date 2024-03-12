<?php


namespace Unit\lucatume\WPBrowser\Command;

use Codeception\Configuration;
use Codeception\Test\Unit;
use Exception;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Command\RunAll;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Traits\UopzFunctions;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class RunAllTest extends Unit
{
    use SnapshotAssertions;
    use UopzFunctions;
    use ClassStubs;

    public function test_description_and_command_name(): void
    {
        $command = new RunAll();
        $nameAndDescription = $command->getCommandName() . ' - ' . $command->getDescription();
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
                global $argv;
                $expectedCommand = [
                    $argv[0],
                    'codeception:run',
                    'suite-' . ++$calls
                ];
                Assert::assertEquals($expectedCommand, $command);
            },
            'getIterator' => fn() => yield from ["Running suite\n", "Done\n"],
            'isSuccessful' => fn() => true,
        ];
        $this->setClassMock(Process::class, $this->makeEmptyClass(Process::class, $mockParams));
        $this->setMethodReturn(Configuration::class, 'suites', ['suite-1', 'suite-2', 'suite-3']);

        $command = new RunAll();
        $output = new BufferedOutput();
        $return = $command->run(new ArrayInput([], $command->getDefinition()), $output);

        $this->assertEquals(0, $return);
        $this->assertEquals("Running suite\nDone\nRunning suite\nDone\nRunning suite\nDone\n", $output->fetch());
    }

    public function failingSuiteProvider(): array
    {
        return [
            'suite-1 fails' => [1, "..."],
            'suite-2 fails' => [2, "......"],
            'suite-3 fails' => [3, "........."]
        ];
    }

    /**
     * It should return 1 if any suite fails
     *
     * @test
     * @dataProvider failingSuiteProvider
     */
    public function should_return_1_if_any_suite_fails(int $failingSuite, string $expectedOutput): void
    {
        $currentSuite = 1;
        $mockParams = [
            'getIterator' => fn() => yield from ['.', '.', '.'],
            // Fail on the 2nd call.
            'isSuccessful' => function () use ($failingSuite, &$currentSuite) {
                return $currentSuite++ !== $failingSuite;
            },
        ];
        $this->setClassMock(Process::class, $this->makeEmptyClass(Process::class, $mockParams));
        $this->setMethodReturn(Configuration::class, 'suites', ['suite-1', 'suite-2', 'suite-3']);

        $command = new RunAll();
        $output = new BufferedOutput();
        $return = $command->run(new ArrayInput([], $command->getDefinition()), $output);

        $this->assertEquals(1, $return);
        $this->assertEquals($expectedOutput, $output->fetch());
    }

    /**
     * It should return 1 if failing to build process
     *
     * @test
     */
    public function should_return_1_if_failing_to_build_process(): void
    {
        $this->setClassMock(Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => fn() => throw new Exception('Failed to build process.')
            ]));
        $this->setMethodReturn(Configuration::class, 'suites', ['suite-1', 'suite-2', 'suite-3']);

        $command = new RunAll();
        $output = new BufferedOutput();
        $return = $command->run(new ArrayInput([], $command->getDefinition()), $output);

        $this->assertEquals(1, $return);
    }
}
