<?php


namespace Unit\lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Lib\Console\Output;
use Codeception\Suite;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\ManagedProcess\ChromeDriver;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;
use stdClass;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class ChromeDriverControllerTest extends Unit
{
    use UopzFunctions;
    use SnapshotAssertions;

    /**
     * @var \Codeception\Lib\Console\Output
     */
    private $output;

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

    public function _before()
    {
        // Mock the binary.
        $bin = codecept_data_dir('/bins/chromedriver-mock');
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);
        // Silence output.
        $this->output = new Output(['verbosity' => Output::VERBOSITY_QUIET]);
        $this->uopzSetMock(Output::class, $this->output);
    }

    /**
     * @before
     */
    public function backupPidFile():void{
        $pidFile = ChromeDriver::getPidFile();

        if (is_file($pidFile)) {
            rename($pidFile, $pidFile.'.bak');
        }
    }

    /**
     * @after
     */
    public static function restorePidFile():void{
        $pidFile = ChromeDriver::getPidFile();

        if (is_file($pidFile .'.bak')) {
            rename($pidFile.'.bak', $pidFile);
        }
    }

    public function notArrayOfStringsProvider(): array
    {
        return [
            'string' => ['string'],
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of mixed values' => [[1, 'string', true, new stdClass()]],
        ];
    }

    /**
     * It should throw if suite configuration parameter is not array of strings
     *
     * @test
     * @dataProvider notArrayOfStringsProvider
     * @param mixed $suites
     */
    public function should_throw_if_suite_configuration_parameter_is_not_array_of_strings($suites): void
    {
        $config = ['suites' => $suites];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "suites" configuration option must be an array.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    public function notIntGreaterThanZeroProvider(): array
    {
        return [
            'string' => ['string'],
            'int less than 1' => [0],
            'bool' => [true],
            'object' => [new stdClass()],
            'array of integers' => [[1, 2, 3]],
            'array of mixed values' => [[1, 'string', true, new stdClass()]],
        ];
    }

    /**
     * It should throw if config port is not int greater than 0
     *
     * @test
     * @dataProvider notIntGreaterThanZeroProvider
     * @param mixed $port
     */
    public function should_throw_if_config_port_is_not_int_greater_than_0($port): void
    {
        $config = ['port' => $port];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "port" configuration option must be an integer greater than 0.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should start for suite if suites specified
     *
     * @test
     */
    public function should_start_for_suite_if_suites_specified(): void
    {
        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(ChromeDriver::getPidFile());
    }

    /**
     * It should start for all suites if no suites specified
     *
     * @test
     */
    public function should_start_for_all_suites_if_no_suites_specified(): void
    {
        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $config = [];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(ChromeDriver::getPidFile());
    }

    /**
     * It should handle chromedriver lifecycle
     *
     * @test
     */
    public function should_handle_chromedriver_lifecycle(): void
    {
        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(ChromeDriver::getPidFile());

        $extension->stop($this->output);

        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $extension->stop($this->output);
    }

    /**
     * It should throw if pid file is not readable
     *
     * @test
     */
    public function should_throw_if_pid_file_is_not_readable(): void
    {
        file_put_contents(ChromeDriver::getPidFile(), '1233');
        $this->uopzSetFunctionReturn('file_get_contents', function (string $file): bool {
            if ($file === ChromeDriver::getPidFile()) {
                return false;
            }
            return file_get_contents($file);
        }, true);

        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Could not read the ChromeDriver PID file.');

        $extension->stop($this->output);
    }

    /**
     * It should correctly produce information
     *
     * @test
     */
    public function should_correctly_produce_information(): void
    {
        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(ChromeDriver::getPidFile());

        $this->assertEquals([
            'running' => 'yes',
            'pidFile' => 'var/_output/chromedriver.pid',
            'port' => 4444,
        ], $extension->getInfo());

        $extension->stop($this->output);

        $this->assertEquals([
            'running' => 'no',
            'pidFile' => 'var/_output/chromedriver.pid',
            'port' => 4444,
        ], $extension->getInfo());
    }

    /**
     * It should throw if binary set and is not string
     *
     * @test
     */
    public function should_throw_if_binary_set_and_is_not_string(): void
    {
        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end'], 'binary' => 23];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "binary" configuration option must be an executable file.');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should throw if binary set and is not executable
     *
     * @test
     */
    public function should_throw_if_binary_set_and_is_not_executable(): void
    {
        $this->assertFileNotExists(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end'], 'binary' => __DIR__ . '/foo-bar.file'];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "binary" configuration option must be an executable file.');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }
}
