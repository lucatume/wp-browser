<?php


namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ChromedriverUpdateTest extends \Codeception\Test\Unit
{
    /**
     * It should throw if specified platform is not a string
     *
     * @test
     */
    public function should_throw_if_specified_platform_is_not_a_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The platform option must be a string.');

        $command = new ChromedriverUpdate();
        $command->execute(new ArrayInput(['--platform' => 23], $command->getDefinition()), new NullOutput());
    }

    /**
     * It should throw if specified platform is not supported
     *
     * @test
     */
    public function should_throw_if_specified_platform_is_not_supported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid platform, supported platforms are: linux64, mac-arm64, mac-x64, win32, win64.');

        $command = new ChromedriverUpdate();
        $command->execute(new ArrayInput(['--platform' => 'loremx86'], $command->getDefinition()), new NullOutput());
    }
}
