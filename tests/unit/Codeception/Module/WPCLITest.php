<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Stub\Expected;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use tad\WPBrowser\Process\Process;
use tad\WPBrowser\Process\ProcessFailedException;
use tad\WPBrowser\StubProphecy\StubProphecy;
use tad\WPBrowser\Traits\WithStubProphecy;

class WPCLITest extends \Codeception\Test\Unit
{
    use WithStubProphecy;

    protected $backupGlobals = false;
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * @var array
     */
    protected $config = [
        'throw' => true
    ];
    /**
     * A mock of the process handler.
     *
     * @var StubProphecy|Process
     */
    protected $process;

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $cli = $this->make_instance();

        $this->assertInstanceOf(WPCLI::class, $cli);
    }

    /**
     * @return WPCLI
     */
    private function make_instance()
    {
        $mockModuleContainer = $this->moduleContainer->reveal();
        $mockProcessAdapter  = $this->process->reveal();

        return new WPCLI($mockModuleContainer, $this->config, $mockProcessAdapter);
    }

    /**
     * @test
     * it should throw if path is not folder at run time
     */
    public function it_should_throw_if_path_is_not_folder_at_run_time()
    {
        $this->config = ['path' => '/some/path/to/null'];

        $this->expectException(ModuleConfigException::class);

        $this->make_instance()->cli(['core','version']);
    }

    /**
     * @test
     * it should call the proces with proper parameters
     */
    public function it_should_call_the_process_with_proper_parameters()
    {
        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn('1.2.3');
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $this->process->withCommand(
            $this->buildExpectedCommand([ 'core', 'version' ]),
            $this->root->url() . '/wp'
        )->willReturn($mockProcess->reveal());

        $cliStatus = $this->make_instance()->cli('core version');

        $this->assertEquals(0, $cliStatus);
    }

    public function optionalOptionsWithArguments()
    {
        return [
            ['ssh', 'some-ssh'],
            ['http', 'some-http'],
            ['url', 'some-url'],
            ['user', 'some-user'],
            ['skip-plugins', 'some-plugin, another-plugin'],
            ['skip-themes', 'some-theme, another-theme'],
            ['skip-packages', 'some-package, another-package'],
            ['require', 'some-file']
        ];
    }

    /**
     * @test
     * it should allow setting additional wp-cli options in the config file
     * @dataProvider optionalOptionsWithArguments
     */
    public function it_should_allow_setting_additional_wp_cli_options_in_the_config_file($option, $optionValue)
    {
        $this->config[$option] = $optionValue;

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn('1.2.3');
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand(
            $this->buildExpectedCommand([ "--{$option}={$optionValue}", 'core', 'version' ]),
            $path
        )->willReturn($mockProcess->reveal());

        $this->make_instance()->cli('core version');
    }

    public function skippedOptions()
    {
        return [
            ['debug'],
            ['color'],
            ['prompt'],
            ['quiet']
        ];
    }

    /**
     * @test
     * it should skip some options by default
     * @dataProvider skippedOptions
     */
    public function it_should_skip_some_options_by_default($option)
    {
        $this->config[$option] = true;

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn('1.2.3');
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([ 'core', 'version' ]), $path)
                      ->willReturn($mockProcess->reveal());

        $this->make_instance()->cli('core version');
    }

    /**
     * @test
     * it should allow overriding options with inline command
     * @dataProvider optionalOptionsWithArguments
     */
    public function it_should_allow_overriding_options_with_inline_command($option, $optionValue)
    {
        $this->config[$option] = $optionValue;
        $overrideValue = 'another-' . $option . '-value';

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn('1.2.3');
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand(
            $this->buildExpectedCommand([ 'core', 'version', "--{$option}={$overrideValue}" ]),
            $path
        )->willReturn($mockProcess->reveal());

        $this->make_instance()->cli("core version --{$option}={$overrideValue}");
    }

    /**
     * @test
     * it should cast wp-cli errors to exceptions if specified in config
     */
    public function it_should_cast_wp_cli_errors_to_exceptions_if_specified_in_config()
    {
        $this->config['throw'] = true;

        $error = md5(time());

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn($error);
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(-1);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand(
            $this->buildExpectedCommand(['core','version']),
            $path
        ) ->willReturn($mockProcess->reveal());

        $this->expectException(ModuleException::class);

        $pattern = '/' . preg_quote($error, '/') . '/';
        if (method_exists($this, 'expectExceptionMessageMatches')) {
            $this->expectExceptionMessageMatches($pattern);
        } else {
            $this->expectExceptionMessageRegExp($pattern);
        }

        $this->make_instance()->cli('core version');
    }

    /**
     * @test
     * it should not throw any exception if specified in config
     */
    public function it_should_not_throw_any_exception_if_specified_in_config()
    {
        $this->config['throw'] = false;

        $error = md5(time());

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn($error);
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(-1);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand(
            $this->buildExpectedCommand(['core','version']),
            $path
        )->willReturn($mockProcess->reveal());

        $this->make_instance()->cli('core version');
    }

    public function cliReturnValues()
    {
        return [
            ['1 2 3 4 5', [1, 2, 3, 4, 5]],
            ['', []],
            ["Post 1\nPost 2\nPost 3", ['Post 1', 'Post 2', 'Post 3']],
            ["Post 1\n Post 2\n Post 3", ['Post 1', 'Post 2', 'Post 3']],
            ["Post 1 \n Post 2 \n Post 3", ['Post 1', 'Post 2', 'Post 3']],
            ["Post 1 \nPost 2 \nPost 3", ['Post 1', 'Post 2', 'Post 3']],
        ];
    }

    /**
     * @test
     * it should not cast output to any format
     * @dataProvider cliReturnValues
     */
    public function it_should_not_cast_output_to_any_format($raw, $expected)
    {
        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn($raw);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand(
            $this->buildExpectedCommand([
                'post',
                'list',
                '--format=ids'
            ]),
            $path
        )->willReturn($mockProcess->reveal());

        $ids = $this->make_instance()->cliToArray('post list --format=ids');

        $this->assertEquals($expected, $ids);
    }

    /**
     * @test
     * it should allow defining a split callback function
     */
    public function it_should_allow_defining_a_split_callback_function()
    {
        $expected = [1, 2, 3];
        $splitCallback = static function () use ($expected) {
            return $expected;
        };

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn('23 12');
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand(
            $this->buildExpectedCommand([
                'post',
                'list',
                '--format=ids'
            ]),
            $path
        )->willReturn($mockProcess->reveal());

        $ids = $this->make_instance()->cliToArray('post list --format=ids', $splitCallback);

        $this->assertEquals($expected, $ids);
    }

    /**
     * @test
     * it should throw if split callback function does not return an array
     */
    public function it_should_throw_if_split_callback_function_does_not_return_an_array()
    {
        $splitCallback = static function () {
            return 'foo';
        };

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'getOutput'   => '23 12',
                'getExitCode' => 0
            ]
        );
        $path        = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([

            'post',
            'list',
            '--format=ids'
        ]), $path)
                      ->willReturn($mockProcess);

        $this->expectException(ModuleException::class);

        $this->make_instance()->cliToArray('post list --format=ids', $splitCallback);
    }

    /**
     * @test
     * it should handle the case where the command output is an empty array
     */
    public function it_should_handle_the_case_where_the_command_output_is_an_empty_array()
    {
        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn([]);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            'post',
            'list',
            '--format=ids'
        ]), $path)->willReturn($mockProcess->reveal());

        $this->assertEquals([], $this->make_instance()->cliToArray('post list --format=ids'));
    }

    /**
     * @test
     * it should handle the case where the command output is null
     */
    public function it_should_handle_the_case_where_the_command_output_is_null()
    {
        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([

            'post',
            'list',
            '--format=ids'
        ]), $path) ->willReturn($mockProcess->reveal());

        $this->assertEquals([], $this->make_instance()->cliToArray('post list --format=ids'));
    }

    /**
     * It should allow setting a timeout in the configuration
     *
     * @test
     */
    public function should_allow_setting_a_timeout_in_the_configuration()
    {
        $this->config['timeout'] = 23;

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(23)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            'post',
            'list',
            '--format=ids'
        ]), $path)
            ->willReturn($mockProcess->reveal());

        $this->make_instance()->cliToArray('post list --format=ids');
    }

    /**
     * It should set the process timeout to null if set to nullable value
     *
     * @test
     * @dataProvider nullTimeoutValues
     */
    public function should_set_the_process_timeout_to_null_if_set_to_nullable_value($timeoutValue)
    {
        $this->config['timeout'] = $timeoutValue;

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(null)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            'post',
            'list',
            '--format=ids'
        ]), $path)
            ->willReturn($mockProcess->reveal());

        $this->make_instance()->cliToArray('post list --format=ids');
    }

    /**
     * It should throw if timeout value is not valid
     *
     * @test
     */
    public function should_throw_if_timeout_value_is_not_valid()
    {
        $this->config['timeout'] = 'foo-bar';

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    public function nullTimeoutValues()
    {
        return [
            'null' => [null],
            'false' => [false],
            'zero' => [0],
            'zero_point_zero' => [0.0],
        ];
    }

    protected function _before()
    {
        $this->moduleContainer = $this->stubProphecy(ModuleContainer::class);
        $this->root = vfsStream::setup('root');
        $wpDir = vfsStream::newDirectory('wp');
        $this->root->addChild($wpDir);
        $this->config = ['path' => $this->root->url() . '/wp'];
        $this->process = $this->stubProphecy(Process::class);
    }

    /**
     * It should support and allow-root configuration parameter
     *
     * @test
     */
    public function should_support_and_allow_root_configuration_parameter()
    {
        $this->config['allow-root'] = true;

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            '--allow-root',
            'core',
            'version',
        ]), $path) ->willReturn($mockProcess->reveal());

        $this->make_instance()->cli(['core','version']);
    }

    /**
     * It should forward options from the configuration to the wp-cli command
     *
     * @test
     */
    public function should_forward_options_from_the_configuration_to_the_wp_cli_command()
    {
        $this->config['some-option'] = 'some-value';

        $mockProcess = $this->stubProphecy(Process::class);
        $mockProcess->getError()->willReturn('');
        $mockProcess->getOutput()->willReturn(null);
        $mockProcess->setTimeout(WPCLI::DEFAULT_TIMEOUT)->shouldBeCalled();
        $mockProcess->mustRun()->shouldBeCalled()->willReturn($mockProcess->itself());
        $mockProcess->getExitCode()->willReturn(0);
        $mockProcess->inheritEnvironmentVariables(true)->shouldBeCalled();
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            '--some-option=some-value',
            'core',
            'version'
        ]), $path) ->willReturn($mockProcess->reveal());

        $this->make_instance()->cli(['core','version']);
    }

    /**
     * It should allow getting a command output as a string
     *
     * @test
     */
    public function should_allow_getting_a_command_output_as_a_string()
    {
        $adminEmail = 'luca@theaveragedev.com';

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'getStatus'      => 0,
                'getError' => null,
                'getOutput'      => $adminEmail,
                'getExitCode'    => 0,
            ]
        );
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            'option',
            'get',
            'admin_email'
        ]), $path) ->willReturn($mockProcess);

        $this->assertEquals($adminEmail, $this->make_instance()->cliToString([ 'option','get','admin_email' ]));
    }

    /**
     * It should handle exceptions thrown by the process by throwing
     *
     * @test
     */
    public function should_handle_exceptions_thrown_by_the_process_by_throwing()
    {
        $this->config['throw'] = true;

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'mustRun' => function () {
                    $process = $this->makeEmpty(
                        Process::class,
                        [
                            'isSuccessful'        => false,
                            'getCommandLine'      => 'invalid',
                            'getExitCode'         => 1,
                            'getError'      => 'error!',
                            'getExitCodeText'     => 'error!',
                            'getWorkingDirectory' => __DIR__,
                        ]
                    );
                    throw new ProcessFailedException($process);
                },
                'getError'      => 'error!',
            ]
        );
        $this->process->withCommand($this->buildExpectedCommand([
            'invalid'
        ]), $this->root->url() . '/wp') ->willReturn($mockProcess);

        $this->expectException(ModuleException::class);

        $this->make_instance()->cliToString([ 'invalid' ]);
    }

    /**
     * It should handle exceptions thrown by the process
     *
     * @test
     */
    public function should_handle_exceptions_thrown_by_the_process()
    {
        $this->config['throw'] = false;

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'mustRun' => function () {
                    $process = $this->makeEmpty(
                        Process::class,
                        [
                            'isSuccessful'        => false,
                            'getCommandLine'      => 'invalid',
                            'getExitCode'         => 1,
                            'getError'      => 'error!',
                            'getExitCodeText'     => 'meh',
                            'getWorkingDirectory' => __DIR__,
                        ]
                    );
                    throw new ProcessFailedException($process);
                },
                'getError'      => 'error!',
                'getExitCode' => 1
            ]
        );
        $this->process->withCommand($this->buildExpectedCommand([

            'invalid'
        ]), $this->root->url() . '/wp') ->willReturn($mockProcess);

        $this->assertEquals('error!', $this->make_instance()->cliToString([ 'invalid' ]));
    }

    /**
     * It should support the WP_CLI_STRICT_ARGS_MODE env argument
     *
     * @test
     */
    public function should_support_the_wp_cli_strict_args_mode_env_argument()
    {
        $this->config['env']['strict-args'] = true;

        $output = 'Success: Added widget to sidebar.';
        $verifyEnvCall = function (array $env) {
            $this->assertArrayHasKey('WP_CLI_STRICT_ARGS_MODE', $env);
            $this->assertEquals('1', $env['WP_CLI_STRICT_ARGS_MODE']);
        };
        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'setEnv' => Expected::once($verifyEnvCall),
                'getError' => '',
                'getExitCode' => 0,
                'getOutput' => $output,
            ]
        );
        $path = $this->root->url() . '/wp';
        $this->process->withCommand($this->buildExpectedCommand([
            'widget',
            'add',
            'rss',
            'sidebar',
            '--title=My feedx',
            '--url="https://wordpress.org/news/feed/"'
        ]), $this->root->url() . '/wp')->willReturn($mockProcess);

        $this->assertEquals($output, $this->make_instance()->cliToString([
            'widget',
            'add',
            'rss',
            'sidebar',
            '--title=My feedx',
            '--url="https://wordpress.org/news/feed/"'
        ]));
    }

    public function envParametersDataProvider()
    {
        return [
            'cache-dir' => ['cache-dir', '/tmp/wp-cli-cache', 'WP_CLI_CACHE_DIR', '/tmp/wp-cli-cache'],
            'config-path' => ['config-path', '/app/public', 'WP_CLI_CONFIG_PATH', '/app/public'],
            'custom-shell' => ['custom-shell', '/bin/zsh', 'WP_CLI_CUSTOM_SHELL', '/bin/zsh'],
            'disable-auto-update' => ['disable-auto-check-update', true, 'WP_CLI_DISABLE_AUTO_CHECK_UPDATE', '1'],
            'packages-dir' => ['packages-dir', '/wp-cli/packages', 'WP_CLI_PACKAGES_DIR', '/wp-cli/packages'],
            'php' => ['php', '/usr/local/bin/php/7.2/php', 'WP_CLI_PHP', '/usr/local/bin/php/7.2/php'],
            'php-args' => ['php-args', 'foo=bar some=23', 'WP_CLI_PHP_ARGS', 'foo=bar some=23'],
        ];
    }
    /**
     * It should correctly parse other env parameters
     *
     * @test
     * @dataProvider envParametersDataProvider
     */
    public function should_correctly_parse_other_env_parameters($envKey, $envValue, $expectedEnvName, $expectedEnvValue)
    {
        $this->config['env'][$envKey] = $envValue;

        $verifyEnvCall = function (array $env) use ($expectedEnvName, $expectedEnvValue) {
            $this->assertArrayHasKey($expectedEnvName, $env);
            $this->assertEquals($expectedEnvValue, $env[$expectedEnvName]);
        };
        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'setEnv' => Expected::once($verifyEnvCall),
                'getError' => '',
                'getExitCode' => 0,
                'getOutput' => '5.2.2',
            ]
        );
        $this->process->withCommand($this->buildExpectedCommand([
            'core'  ,'version'
        ]), $this->root->url() . '/wp')->willReturn($mockProcess);

        $this->assertEquals('5.2.2', $this->make_instance()->cliToString([ 'core' ,'version' ]));
    }

    /**
     * It should not throw on 0 exit status code
     *
     * @test
     */
    public function should_not_throw_on_0_exit_status_code()
    {
        $this->config['throw'] = true;

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'getExitCode' => 0,
                'getOutput' => 'stdout',
                'getError' => 'stderr',
            ]
        );
        $command = $this->buildExpectedCommand([ 'test' ]);
        $this->process->withCommand($command, $this->root->url() . '/wp')->willReturn($mockProcess);

        $this->assertEquals('stdout', $this->make_instance()->cliToString([ 'test' ]));
    }

    /**
     * It should output stderr on 0 exit status code and wrong stdout
     *
     * @test
     */
    public function should_output_stderr_on_0_exit_status_code_and_wrong_stdout()
    {
        $this->config['throw'] = true;

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'getExitCode' => 0,
                'getOutput' => '',
                'getError' => 'stderr'
            ]
        );
        $this->process->withCommand($this->buildExpectedCommand(['test']), $this->root->url() . '/wp')->willReturn($mockProcess);

        $this->assertEquals('stderr', $this->make_instance()->cliToString([ 'test' ]));
    }

    /**
     * It should throw if exit code not 0 and stderr empty
     *
     * @test
     */
    public function should_throw_if_exit_code_not_0_and_stderr_empty()
    {
        $this->config['throw'] = true;

        $mockProcess = $this->makeEmpty(
            Process::class,
            [
                'getExitCode' => -1,
                'getOutput' => 'stdout',
                'getError' => 'stderr'
            ]
        );
        $this->process->withCommand($this->buildExpectedCommand(['test']), $this->root->url() . '/wp')->willReturn($mockProcess);

        $this->expectException(ModuleException::class);

        $this->make_instance()->cliToString(['test']);
    }

    protected function buildExpectedCommand(array $arr)
    {
        return array_merge([
            PHP_BINARY,
            codecept_root_dir('vendor/wp-cli/wp-cli/php/boot-fs.php'),
            '--path=' . $this->root->url() . '/wp'
        ], $arr);
    }
}
