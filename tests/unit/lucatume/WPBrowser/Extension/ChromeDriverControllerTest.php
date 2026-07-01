<?php


namespace Unit\lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Lib\Console\Output;
use Codeception\Suite;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Command\ParallelRun\WorkerResourceEnv;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\ManagedProcess\ChromeDriver;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Filesystem;
use stdClass;
use Symfony\Component\Console\Output\BufferedOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

/**
 * @group fast
 */
class ChromeDriverControllerTest extends Unit
{
    use UopzFunctions;
    use SnapshotAssertions;

    /**
     * @var \Codeception\Lib\Console\Output
     */
    private $output;

    /**
     * @var string|null
     */
    private $savedNeedsChromedriverServer;
    /**
     * @var string|null
     */
    private $savedNeedsChromedriverEnv;
    /**
     * @var string|false
     */
    private $savedNeedsChromedriverGetenv = false;

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
     * @before
     */
    public function isolateWorkerNeedsChromedriverEnv(): void
    {
        $var = WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER;
        $this->savedNeedsChromedriverServer = array_key_exists($var, $_SERVER) ? (string)$_SERVER[$var] : null;
        $this->savedNeedsChromedriverEnv    = array_key_exists($var, $_ENV) ? (string)$_ENV[$var] : null;
        $this->savedNeedsChromedriverGetenv = getenv($var);

        unset($_SERVER[$var], $_ENV[$var]);
        putenv($var);
    }

    /**
     * @after
     */
    public function restoreWorkerNeedsChromedriverEnv(): void
    {
        $var = WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER;
        if ($this->savedNeedsChromedriverServer !== null) {
            $_SERVER[$var] = $this->savedNeedsChromedriverServer;
        }
        if ($this->savedNeedsChromedriverEnv !== null) {
            $_ENV[$var] = $this->savedNeedsChromedriverEnv;
        }
        if ($this->savedNeedsChromedriverGetenv !== false) {
            putenv($var . '=' . $this->savedNeedsChromedriverGetenv);
        }
    }

    public function _before()
    {
        // Mock the binary.
        $bin = codecept_data_dir('/bins/chromedriver-mock');
        $this->setMethodReturn(Composer::class, 'binDir', $bin);
        // Silence output.
        $this->output = new Output(['verbosity' => Output::VERBOSITY_QUIET]);
        $this->setClassMock(Output::class, $this->output);
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

        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

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
        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

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
        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(ChromeDriver::getPidFile());

        $extension->stop($this->output);

        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

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
        $this->setFunctionReturn('file_get_contents', function (string $file): bool {
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
        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(ChromeDriver::getPidFile());

        $expectedPidFile = Filesystem::relativePath(codecept_root_dir(), ChromeDriver::getPidFile());

        $this->assertEquals([
            'running' => 'yes',
            'pidFile' => $expectedPidFile,
            'port' => 4444,
        ], $extension->getInfo());

        $extension->stop($this->output);

        $this->assertEquals([
            'running' => 'no',
            'pidFile' => $expectedPidFile,
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
        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

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
        $this->assertFileDoesNotExist(ChromeDriver::getPidFile());

        $config = ['suites' => ['end2end'], 'binary' => __DIR__ . '/foo-bar.file'];
        $options = [];

        $extension = new ChromeDriverController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "binary" configuration option must be an executable file.');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    public function test_start_skips_when_worker_marked_as_not_needing_chromedriver(): void
    {
        $_SERVER[WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER] = '0';
        $_ENV[WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER]    = '0';
        putenv(WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER . '=0');

        try {
            $controller = new ChromeDriverController([], []);
            $output = new BufferedOutput();
            $controller->start($output);

            $this->assertStringContainsString(
                'ChromeDriver not needed by this worker; skipping.',
                $output->fetch()
            );
            $this->assertFileDoesNotExist(ChromeDriver::getPidFile());
        } finally {
            unset($_SERVER[WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER], $_ENV[WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER]);
            putenv(WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER);
        }
    }
}
