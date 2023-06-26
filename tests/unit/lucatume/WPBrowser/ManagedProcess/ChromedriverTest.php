<?php


namespace lucatume\WPBrowser\ManagedProcess;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;

class ChromedriverTest extends Unit
{
    use UopzFunctions;

    /**
     * It should throw if binary not found
     *
     * @test
     */
    public function should_throw_if_binary_not_found(): void
    {
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', '/not-a-binary');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessinterface::ERR_BINARY_NOT_FOUND);

        new ChromeDriver();
    }

    /**
     * It should throw if binary is not executable
     *
     * @test
     */
    public function should_throw_if_binary_is_not_executable(): void
    {
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', __FILE__);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessinterface::ERR_BINARY_NOT_FOUND);
        new ChromeDriver();
    }

    /**
     * It should throw if binary cannot be started with arguments
     *
     * @test
     */
    public function should_throw_if_binary_cannot_be_started_with_arguments(): void
    {
        $throwingBin = codecept_data_dir('/bins/throwing-bin');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $throwingBin);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessinterface::ERR_START);

        $chromedriver = new ChromeDriver();
        $chromedriver->start();
    }

    /**
     * It should handle ChromeDriverController lifecycle correctly
     *
     * @test
     */
    public function should_handle_chromedriver_lifecycle_correctly(): void
    {
        $bin = codecept_data_dir('/bins/chromedriver-mock');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);

        $chromedriver = new ChromeDriver(3456, ['--url-base=wd/hub', '--headless']);
        $chromedriver->start();

        $this->assertEquals(3456, $chromedriver->getPort());
        $this->assertIsInt($chromedriver->getPid());
        $pidFile = $chromedriver->getPidFile();
        $this->assertFileExists($pidFile);
        $this->assertStringEqualsFile($pidFile, $chromedriver->getPid());

        $this->assertEquals(0, pcntl_wexitstatus($chromedriver->stop()));

        $this->assertFileNotExists($pidFile);
    }

    /**
     * It should throw if pid file removal fails
     *
     * @test
     */
    public function should_throw_if_pid_file_removal_fails(): void
    {
        $bin = codecept_data_dir('/bins/chromedriver-mock');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);

        $chromedriver = new ChromeDriver(3456, ['--url-base=wd/hub', '--headless']);
        $chromedriver->start();

        $this->uopzSetFunctionReturn('unlink', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessinterface::ERR_STOP);

        $chromedriver->stop();
    }

    /**
     * It should start with random port if not specified
     *
     * @test
     */
    public function should_start_with_random_port_if_not_specified(): void
    {
        $bin = codecept_data_dir('/bins/chromedriver-mock');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);

        $chromedriver = new ChromeDriver();
        $chromedriver->start();
        $port = $chromedriver->getPort();
        $chromedriver->stop();

        $this->assertIsInt($port);
        $this->assertGreaterThan(0, $port);
    }
}
