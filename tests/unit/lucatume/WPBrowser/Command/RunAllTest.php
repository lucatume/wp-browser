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
        $mockProcessClass = $this->makeEmptyClass(Process::class, [
            '__construct' => fn(array $command) => Assert::assertIsArray($command),
            'getIterator' => fn() => yield 'Running ...',
            'isSuccessful' => fn() => true,
        ]);
        $this->uopzSetStaticMethodReturn(Configuration::class, 'suites', ['suite-1', 'suite-2', 'suite-3']);
        $this->uopzSetMock(Process::class, $mockProcessClass);

        $command = new RunAll();
        $return = $command->run(new ArrayInput([], $command->getDefinition()), new BufferedOutput());

        $this->assertEquals(0, $return);
    }
}
