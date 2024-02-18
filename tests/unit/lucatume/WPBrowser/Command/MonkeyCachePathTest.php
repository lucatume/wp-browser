<?php


namespace lucatume\WPBrowser\Command;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MonkeyCachePathTest extends Unit
{
    /**
     * It should provide porcelain output correctly
     *
     * @test
     */
    public function should_provide_porcelain_output_correctly(): void
    {
        $input = new StringInput('--porcelain');
        $output = new BufferedOutput();

        $command = new MonkeyCachePath();
        $input->bind($command->getDefinition());
        $exitCode = $command->execute($input, $output);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals(MonkeyPatch::getCachePath() . "\n", $output->fetch());

        $command = new MonkeyCachePath();
        $input = new StringInput('--porcelain');
        $input->bind($command->getDefinition());
        $exitCode = $command->execute($input, $output);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals(MonkeyPatch::getCachePath() . "\n", $output->fetch());
    }

    /**
     * It should provide pretty output correctly
     *
     * @test
     */
    public function should_provide_pretty_output_correctly(): void
    {
        $input = new StringInput('');
        $output = new BufferedOutput();

        $command = new MonkeyCachePath();
        $exitCode = $command->execute($input, $output);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals('Monkey patch cache path: ' . MonkeyPatch::getCachePath() . "\n", $output->fetch());
    }
}
