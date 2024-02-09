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
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Composer;
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
        $this->uopzSetStaticMethodReturn(Composer::class, 'binDir', $bin);
        // Silence output.
        $this->output = new Output(['verbosity' => Output::VERBOSITY_QUIET]);
        $this->uopzSetMock(Output::class, $this->output);
    }

    /**
     * @beforeClass
     */
    public static function backupPidFile():void{
        $pidFile = DockerComposeController::getRunningFile();

        if (is_file($pidFile)) {
            rename($pidFile, $pidFile.'.bak');
        }
    }

    /**
     * @afterClass
     */
    public static function restorePidFile():void{
        $pidFile = DockerComposeController::getRunningFile();
        $pidFileBackup = $pidFile .'.bak';

        if (is_file($pidFileBackup)) {
            rename($pidFileBackup, $pidFile);
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
        file_put_contents(DockerComposeController::getRunningFile(), 'yes');
        $constructed = 0;
        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function (...$args) use (&$constructed) {
                    $constructed++;
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
     * It should up stack correctly
     *
     * @test
     */
    public function should_up_stack_correctly(): void
    {
        $constructCommands = [];
        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function ($command, ...$args) use (&$constructCommands) {
                    $constructCommands[] = $command;
                }
            ])
        );

        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);
        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertEquals(
            ['docker', 'compose', '-f', 'docker-compose.yml', 'up', '--wait'],
            $constructCommands[0]
        );
        $this->assertFileExists(DockerComposeController::getRunningFile());
    }

    /**
     * It should throw if config compose-file is not valid existing file
     *
     * @test
     */
    public function should_throw_if_config_compose_file_is_not_valid_existing_file(): void
    {
        $this->uopzSetMock(Process::class, $this->makeEmptyClass(Process::class, []));

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
        $this->uopzSetMock(Process::class, $this->makeEmptyClass(Process::class, []));

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
        $constructed = 0;
        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                '__construct' => static function () use (&$constructed) {
                    $constructed++;
                },
                'stop' => 0
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertEquals(1, $constructed);
        $this->assertFileExists(DockerComposeController::getRunningFile());

        $extension->stop($this->output);

        $this->assertFileNotExists(DockerComposeController::getRunningFile());

        $extension->stop($this->output);
    }

    /**
     * It should throw if docker compose start fails
     *
     * @test
     */
    public function should_throw_if_docker_compose_start_fails(): void
    {
        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                'mustRun' => static function () {
                    throw new Exception('something went wrong');
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
        $this->uopzSetMock(Process::class, $this->makeEmptyClass(Process::class, []));
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Failed to write Docker Compose running file.');
        $this->uopzSetFunctionReturn('file_put_contents', false);

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));
    }

    /**
     * It should throw if stack stopping fails
     *
     * @test
     */
    public function should_throw_if_stack_stopping_fails(): void
    {
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(DockerComposeController::getRunningFile());

        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                'mustRun' => static function () {
                    throw new Exception('something went wrong');
                }
            ])
        );

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
        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                'stop' => 0
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(DockerComposeController::getRunningFile());

        $this->uopzSetFunctionReturn('unlink', false);

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
        $this->uopzSetMock(
            Process::class,
            $this->makeEmptyClass(Process::class, [
                'getOutput' => static function () {
                    return Yaml::dump(['services' => ['foo' => ['ports' => ['8088:80']]]]);
                },
                'stop' => 0
            ])
        );
        $config = ['suites' => ['end2end'], 'compose-file' => 'docker-compose.yml'];
        $options = [];

        $extension = new DockerComposeController($config, $options);

        $mockSuite = $this->make(Suite::class, ['getName' => 'end2end']);

        $extension->onModuleInit($this->make(SuiteEvent::class, ['getSuite' => $mockSuite]));

        $this->assertFileExists(DockerComposeController::getRunningFile());

        $this->assertEquals(
            ['status' => 'up', 'config' => ['services' => ['foo' => ['ports' => [0 => '8088:80']]]]],
            $extension->getInfo()
        );

        $extension->stop($this->output);

        $this->assertEquals(['status' => 'down', 'config' => ''], $extension->getInfo());
    }
}
