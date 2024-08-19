<?php


namespace lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Lib\Console\Output;
use Codeception\Suite;
use Codeception\Test\Unit;
use Exception;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use stdClass;
use Symfony\Component\Yaml\Yaml;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class DockerComposeControllerTest extends Unit
{
    use UopzFunctions;
    use SnapshotAssertions;
    use ClassStubs;

    /**
     * @var \Codeception\Lib\Console\Output
     */
    private $output;

    /**
     * @before
     * @after
     */
    public function removeRunningFiles(): void
    {
        $pidFile = DockerComposeController::getRunningFile();
        if (is_file($pidFile)) {
            unlink($pidFile);
        }
    }

    public function _before()
    {
        // Mock the binary.
        $bin = codecept_data_dir('/bins/docker-compose-mock');
        $this->setMethodReturn(Composer::class, 'binDir', $bin);
        // Silence output.
        $this->output = new Output(['verbosity' => Output::VERBOSITY_QUIET]);
        $this->setClassMock(Output::class, $this->output);
        // Intercept reading and writing of the running file.
        $runningFile = DockerComposeController::getRunningFile();
        $this->setFunctionReturn('is_file', function (string $file) use ($runningFile): bool {
            return $file === $runningFile ? false : is_file($file);
        }, true);
        $this->setFunctionReturn(
            'file_put_contents',
            function (string $file, string $contents) use ($runningFile): bool {
                if ($file === $runningFile) {
                    return false;
                }
                return file_put_contents($file, $contents);
            },
            true
        );
        $this->setFunctionReturn('unlink', function (string $file) use ($runningFile): bool {
            if ($file === $runningFile) {
                return true;
            }
            return unlink($file);
        }, true);
        $this->setClassMock(Process::class, $this->makeEmptyClass(Process::class, [
            '__construct' => function (...$args) {
                throw new AssertionFailedError('Unexpected Process::__construct call for ' . print_r($args, true));
            }
        ]));
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

        $extension = new DockerComposeController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "suites" configuration option must be an array.');

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should not run any command if already running
     *
     * @test
     */
    public function should_not_run_any_command_if_already_running(): void
    {
        $this->setFunctionReturn('is_file', function (string $file): bool {
            return $file === DockerComposeController::getRunningFile() ? true : is_file($file);
        }, true);
        $constructed = 0;
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function (...$args) use (&$constructed) {
                    $constructed++;
                    throw new AssertionFailedError('Unexpected Process::__construct call for ' . print_r($args, true));
                }
            ])
        );

        $config = ['suites' => ['end2end']];
        $options = [];

        $extension = new DockerComposeController($config, $options);
        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertEquals(0, $constructed);
    }

    /**
     * It should start stack correctly
     *
     * @test
     */
    public function should_start_the_stack_correctly(): void
    {
        $runningFileExists = false;
        $this->setFunctionReturn('is_file', function (string $file) use ($runningFileExists): bool {
            return $file === DockerComposeController::getRunningFile() ? $runningFileExists : is_file($file);
        }, true);
        $this->setFunctionReturn(
            'file_put_contents',
            function (string $file, string $contents) use (&$runningFileExists): bool {
                if ($file === DockerComposeController::getRunningFile()) {
                    $runningFileExists = true;
                    return true;
                }
                return file_put_contents($file, $contents);
            },
            true
        );
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function ($command, ...$args) use (&$constructedProcesses) {
                    Assert::assertEquals([
                        'docker',
                        'compose',
                        '-f',
                        'docker-compose.yml',
                        'up',
                        '--wait'
                    ], $command);
                },
                'mustRun' => '__itself',
                'getPid' => 2389,
            ])
        );

        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);
        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertTrue($runningFileExists);
    }

    /**
     * It should throw if config compose-file is not valid existing file
     *
     * @test
     */
    public function should_throw_if_config_compose_file_is_not_valid_existing_file(): void
    {
        $config = ['suites' => ['end2end'], 'compose-file' => 'not-a-file.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "compose-file" configuration option must be a valid file.');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should throw if config env file is not valid file
     *
     * @test
     */
    public function should_throw_if_config_env_file_is_not_valid_file(): void
    {
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml', 'env-file' => 'not-an-env-file'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('The "env-file" configuration option must be a valid file.');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should correctly handle stack lifecycle
     *
     * @test
     */
    public function should_correctly_handle_stack_lifecycle(): void
    {
        $runningFileExists = false;
        $this->setFunctionReturn('is_file', function (string $file) use (&$runningFileExists): bool {
            return $file === DockerComposeController::getRunningFile() ? $runningFileExists : is_file($file);
        }, true);
        $this->setFunctionReturn(
            'file_put_contents',
            function (string $file, string $contents) use (&$runningFileExists): bool {
                if ($file === DockerComposeController::getRunningFile()) {
                    $runningFileExists = true;
                    return true;
                }
                return file_put_contents($file, $contents);
            },
            true
        );
        $this->setFunctionReturn('unlink', function (string $file) use (&$runningFileExists): bool {
            if ($file === DockerComposeController::getRunningFile()) {
                $runningFileExists = false;
                return true;
            }
            return unlink($file);
        }, true);
        $step = 'not-running';
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function ($command) use (&$step) {
                    if ($step === 'not-running') {
                        $step = 'started';
                        Assert::assertEquals([
                            'docker',
                            'compose',
                            '-f',
                            'docker-compose.yml',
                            'up',
                            '--wait'
                        ], $command);
                        return;
                    }

                    $step = 'stopped';
                    Assert::assertEquals([
                        'docker',
                        'compose',
                        '-f',
                        'docker-compose.yml',
                        'down'
                    ], $command);
                },
                'mustRun' => '__itself',
                'stop' => 0,
                'getPid' => 2389
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertTrue($runningFileExists);

        $extension->stop($this->output);

        $this->assertFalse($runningFileExists);

        $extension->stop($this->output);
    }

    /**
     * It should throw if docker compose start fails
     *
     * @test
     */
    public function should_throw_if_docker_compose_start_fails(): void
    {
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                'mustRun' => static function () {
                    throw new Exception('Something went wrong.');
                }
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];

        $extension = new DockerComposeController($config, []);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessageRegExp('/Failed to start Docker Compose/');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should throw if running file cannot be written
     *
     * @test
     */
    public function should_throw_if_running_file_cannot_be_written(): void
    {
        $this->setFunctionReturn('is_file', function (string $file): bool {
            return $file === DockerComposeController::getRunningFile() ? false : is_file($file);
        }, true);
        $this->setFunctionReturn(
            'file_put_contents',
            function (string $file, string $contents): bool {
                if ($file === DockerComposeController::getRunningFile()) {
                    return false;
                }
                return file_put_contents($file, $contents);
            },
            true
        );
        $this->setClassMock(Process::class, $this->makeEmptyClass(Process::class, [
            '__construct' => static function ($command) {
                Assert::assertEquals([
                    'docker',
                    'compose',
                    '-f',
                    'docker-compose.yml',
                    'up',
                    '--wait'
                ], $command);
            },
            'mustRun' => '__itself',
            'getPid' => 2389
        ]));
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Failed to write Docker Compose running file.');

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should throw if stack stopping fails
     *
     * @test
     */
    public function should_throw_if_stack_stopping_fails(): void
    {
        $this->setFunctionReturn('is_file', function (string $file): bool {
            return $file === DockerComposeController::getRunningFile() ? true : is_file($file);
        }, true);
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function ($command) {
                    Assert::assertEquals([
                        'docker',
                        'compose',
                        '-f',
                        'docker-compose.yml',
                        'down'
                    ], $command);
                },
                'mustRun' => function () {
                    throw new Exception('Failed to stop Docker Compose.');
                }
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessageRegExp('/Failed to stop Docker Compose/');

        $extension->stop($this->output);
    }

    /**
     * It should throw if running file cannot be removed while stopping
     *
     * @test
     */
    public function should_throw_if_running_file_cannot_be_removed_while_stopping(): void
    {
        $this->setFunctionReturn('is_file', function (string $file): bool {
            return $file === DockerComposeController::getRunningFile() ? true : is_file($file);
        }, true);
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function ($command) {
                    Assert::assertEquals([
                        'docker',
                        'compose',
                        '-f',
                        'docker-compose.yml',
                        'down'
                    ], $command);
                },
                'mustRun' => '__itself'
            ])
        );
        $this->setFunctionReturn('unlink', function ($file) {
            if ($file === DockerComposeController::getRunningFile()) {
                return false;
            }
            return unlink($file);
        }, true);
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Failed to remove Docker Compose running file.');

        $extension->stop($this->output);
    }

    /**
     * It should produce information correctly
     *
     * @test
     */
    public function should_produce_information_correctly(): void
    {
        $runningFileExists = false;
        $this->setFunctionReturn('is_file', function (string $file) use (&$runningFileExists): bool {
            return $file === DockerComposeController::getRunningFile() ? $runningFileExists : is_file($file);
        }, true);
        $this->setFunctionReturn(
            'file_put_contents',
            function (string $file, string $contents) use (&$runningFileExists): bool {
                if ($file === DockerComposeController::getRunningFile()) {
                    $runningFileExists = true;
                    return true;
                }
                return file_put_contents($file, $contents);
            },
            true
        );
        $this->setFunctionReturn('unlink', function (string $file) use (&$runningFileExists): bool {
            if ($file === DockerComposeController::getRunningFile()) {
                $runningFileExists = false;
                return true;
            }
            return unlink($file);
        }, true);
        $step = 'not-running';
        $this->setClassMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function ($command) use (&$step) {
                    if ($step === 'not-running') {
                        $step = 'started-fetch-config';
                        Assert::assertEquals([
                            'docker',
                            'compose',
                            '-f',
                            'docker-compose.yml',
                            'up',
                            '--wait'
                        ], $command);
                        return;
                    }

                    if ($step === 'started-fetch-config') {
                        $step = 'started';
                        Assert::assertEquals([
                            'docker',
                            'compose',
                            '-f',
                            'docker-compose.yml',
                            'config'
                        ], $command);
                        return;
                    }

                    if ($step === 'started') {
                        $step = 'stopped';
                        Assert::assertEquals([
                            'docker',
                            'compose',
                            '-f',
                            'docker-compose.yml',
                            'down'
                        ], $command);
                        return;
                    }

                    throw new AssertionFailedError(
                        'Unexpected Process::__construct call for ' . print_r($command, true)
                    );
                },
                'mustRun' => '__itself',
                'getOutput' => static function () use (&$step) {
                    if ($step === 'started') {
                        return Yaml::dump(['services' => ['foo' => ['ports' => ['8088:80']]]]);
                    }
                    return '';
                },
                'stop' => 0,
                'getPid' => 2389
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertTrue($runningFileExists);

        $this->assertEquals(
            ['status' => 'up', 'config' => ['services' => ['foo' => ['ports' => [0 => '8088:80']]]]],
            $extension->getInfo()
        );

        $extension->stop($this->output);

        $this->assertEquals(['status' => 'down', 'config' => ''], $extension->getInfo());
        $this->assertFalse($runningFileExists);
    }
}
