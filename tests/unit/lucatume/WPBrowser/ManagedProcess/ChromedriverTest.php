<?php


namespace lucatume\WPBrowser\ManagedProcess;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;
use Symfony\Component\Process\Process;

class ChromedriverTest extends Unit
{
    use UopzFunctions;

    /**
     * @before
     * @after
     */
    public function removePidFiles(): void
    {
        $pidFile = ChromeDriver::getPidFile();
        if (is_file($pidFile)) {
            unlink($pidFile);
        }
    }

    /**
     * It should throw if binary not found
     *
     * @test
     */
    public function should_throw_if_binary_not_found(): void
    {
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', '/not-a-binary');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessInterface::ERR_BINARY_NOT_FOUND);

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
        $this->expectExceptionCode(ManagedProcessInterface::ERR_BINARY_NOT_FOUND);
        new ChromeDriver();
    }

    /**
     * It should throw if binary cannot be started with arguments
     *
     * @test
     */
    public function should_throw_if_binary_cannot_be_started_with_arguments(): void
    {
        $throwingBin = codecept_data_dir('bins/throwing-bin');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $throwingBin);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessInterface::ERR_START);

        $chromedriver = new ChromeDriver();
        $chromedriver->start();
    }

    /**
     * It should throw if PID is not integer on start
     *
     * @test
     */
    public function should_throw_if_pid_is_not_integer_on_start(): void
    {
        $mockProcess = new class(['chromedriver']) extends Process {
            public function getOutput(): string
            {
                return 'ChromeDriver was started successfully.';
            }
            public function getPid(): ?int
            {
                return null;
            }

        };
        $this->uopzSetMock(Process::class, $mockProcess);

        $chromedriver = new ChromeDriver(3456, ['--url-base=wd/hub', '--headless']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessInterface::ERR_PID);

        $chromedriver->start();
    }

    /**
     * It should throw if pif file cannot be written on start
     *
     * @test
     */
    public function should_throw_if_pif_file_cannot_be_written_on_start(): void
    {
        $mockProcess = new class(['chromedriver']) extends Process {
            public function getOutput(): string
            {
                return 'ChromeDriver was started successfully.';
            }
            public function getPid(): ?int
            {
                return 2389;
            }

        };
        $this->uopzSetMock(Process::class, $mockProcess);

        $chromedriver = new ChromeDriver(3456, ['--url-base=wd/hub', '--headless']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ManagedProcessInterface::ERR_PID_FILE);

        $this->uopzSetFunctionReturn('file_put_contents', function (string $file): false|int {
            return $file === ChromeDriver::getPidFile() ? false : 0;
        }, true);

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
        $pidFile = ChromeDriver::getPidFile();
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
        $this->expectExceptionCode(ManagedProcessInterface::ERR_PID_FILE_DELETE);

        $chromedriver->stop();
    }

    /**
     * It should start with random port if not specified
     *
     * @test
     */
    public function should_start_on_default_port_if_not_specified(): void
    {
        $bin = codecept_data_dir('/bins/chromedriver-mock');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);

        $chromedriver = new ChromeDriver();
        $chromedriver->start();
        $port = $chromedriver->getPort();
        $chromedriver->stop();

        $this->assertIsInt($port);
        $this->assertEquals(ChromeDriver::PORT_DEFAULT, $port);
    }
}
