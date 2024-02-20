<?php


namespace lucatume\WPBrowser\Command;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MonkeyCacheClearTest extends Unit
{
    /**
     * It should correctly clear cache
     *
     * @test
     */
    public function should_correctly_clear_cache(): void
    {
        $input = new StringInput('');
        $output = new BufferedOutput();

        $this->assertDirectoryExists(MonkeyPatch::getCachePath());
        touch(MonkeyPatch::getCachePath() . '/some-file.txt');
        $this->assertFileExists(MonkeyPatch::getCachePath() . '/some-file.txt');
        mkdir(MonkeyPatch::getCachePath() . '/some-dir');
        touch(MonkeyPatch::getCachePath() . '/some-dir/some-file.txt');
        $this->assertFileExists(MonkeyPatch::getCachePath() . '/some-dir/some-file.txt');

        $command = new MonkeyCacheClear();
        $command->execute($input, $output);

        $this->assertDirectoryExists(MonkeyPatch::getCachePath());
        $this->assertCount(0, (array)glob(MonkeyPatch::getCachePath() . '/*'));
        /** @noinspection PhpUnitTestsInspection */
        $this->assertFalse(is_dir(MonkeyPatch::getCachePath() . '/some-dir'));
        $this->assertEquals("Monkey patch cache cleared.\n", $output->fetch());
    }

    /**
     * It should correctly clean cache with porcelain output
     *
     * @test
     */
    public function should_correctly_clean_cache_with_porcelain_output(): void
    {
        $input = new StringInput('--porcelain');
        $output = new BufferedOutput();

        $this->assertDirectoryExists(MonkeyPatch::getCachePath());
        touch(MonkeyPatch::getCachePath() . '/some-file.txt');
        $this->assertFileExists(MonkeyPatch::getCachePath() . '/some-file.txt');
        mkdir(MonkeyPatch::getCachePath() . '/some-dir');
        touch(MonkeyPatch::getCachePath() . '/some-dir/some-file.txt');
        $this->assertFileExists(MonkeyPatch::getCachePath() . '/some-dir/some-file.txt');

        $command = new MonkeyCacheClear();
        $input->bind($command->getDefinition());
        $command->execute($input, $output);

        $this->assertDirectoryExists(MonkeyPatch::getCachePath());
        $this->assertCount(0, (array)glob(MonkeyPatch::getCachePath() . '/*'));
        /** @noinspection PhpUnitTestsInspection */
        $this->assertFalse(is_dir(MonkeyPatch::getCachePath() . '/some-dir'));
        $this->assertEquals('', $output->fetch());
    }
}
