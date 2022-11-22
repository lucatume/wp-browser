<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\StubProphecy\Arg;
use lucatume\WPBrowser\Tests\Traits\WithUopz;
use lucatume\WPBrowser\Traits\WithStubProphecy;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;
use UnitTester;

class WPCLITest extends Unit
{
    use WithStubProphecy;
    use WithUopz;

    protected $backupGlobals = false;
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /**
     * @var array
     */
    protected array $config = [
        'path' => __DIR__,
        'throw' => true
    ];

    private function assertProcCallArgsAndReturn(array $expectedArgs, string $runCommand = 'exit 0'): void
    {
        $this->uopzSetStaticMethodReturn(
            Process::class,
            'fromShellCommandLine',
            static function () use ($expectedArgs, $runCommand) {
                $cwd = getcwd();
                $callArgs = func_get_args();
                foreach ($callArgs as &$callArg) {
                    $callArg = is_string($callArg) ? str_replace($cwd, '', $callArg) : $callArg;
                }
                unset($callArg);
                Assert::assertEquals($expectedArgs, $callArgs);

                return Process::fromShellCommandline($runCommand);
            },
            true);
    }

    private function module(): WPCLI
    {
        $moduleContainer = new ModuleContainer(new Di(), []);
        return new WPCLI($moduleContainer, $this->config);
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $cli = $this->module();

        $this->assertInstanceOf(WPCLI::class, $cli);
    }

    /**
     * @test
     * it should throw if path is not folder at run time
     */
    public function it_should_throw_if_path_is_not_folder_at_run_time()
    {
        $this->config = ['path' => '/some/path/to/null'];

        $this->expectException(ModuleConfigException::class);

        $this->module()->cli(['core', 'version']);
    }

    /**
     * @test
     * it should call the proces with proper parameters
     */
    public function it_should_call_the_process_with_proper_parameters()
    {
        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module core version',
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ]
        );

        $cliStatus = $this->module()->cli('core version');

        $this->assertEquals(0, $cliStatus);
    }

    public function optionalOptionsWithArguments(): array
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
        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module --$option=$optionValue core version",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ]
        );

        $this->module()->cli('core version');
    }

    public function skippedOptions(): array
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

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module core version",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ]
        );

        $this->module()->cli('core version');
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

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module core version --$option='$overrideValue'",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ]
        );

        $this->module()->cli("core version --{$option}='{$overrideValue}'");
    }

    /**
     * @test
     * it should cast wp-cli errors to exceptions if specified in config
     */
    public function it_should_cast_wp_cli_errors_to_exceptions_if_specified_in_config()
    {
        $this->config['throw'] = true;

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module core version",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'echo "for reasons" 1>&2; exit 1'
        );

        $this->expectException(ModuleException::class);

        $pattern = '/for reasons/';
        $this->expectExceptionMessageMatches($pattern);

        $this->module()->cli('core version');
    }

    /**
     * @test
     * it should not throw any exception if specified in config
     */
    public function it_should_not_throw_any_exception_if_specified_in_config()
    {
        $this->config['throw'] = false;

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module core version",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'echo "for reasons" 1>&2; exit 1'
        );

        $this->module()->cli('core version');
    }

    public function cliReturnValues(): array
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
        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo -n '$raw'"
        );

        $ids = $this->module()->cliToArray('post list --format=ids');

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

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo -n '23 12'"
        );

        $ids = $this->module()->cliToArray('post list --format=ids', $splitCallback);

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

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo -n '23 12'"
        );

        $this->expectException(ModuleException::class);

        $this->module()->cliToArray('post list --format=ids', $splitCallback);
    }

    /**
     * @test
     * it should handle the case where the command output is an empty array
     */
    public function it_should_handle_the_case_where_the_command_output_is_an_empty_array()
    {
        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo ''"
        );

        $this->assertEquals([], $this->module()->cliToArray('post list --format=ids'));
    }

    /**
     * @test
     * it should handle the case where the command output is null
     */
    public function it_should_handle_the_case_where_the_command_output_is_null()
    {
        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "exit 0"
        );

        $this->assertEquals([], $this->module()->cliToArray('post list --format=ids'));
    }

    /**
     * It should allow setting a timeout in the configuration
     *
     * @test
     */
    public function should_allow_setting_a_timeout_in_the_configuration()
    {
        $this->config['timeout'] = 23;

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                23
            ],
            "exit 0"
        );

        $this->module()->cliToArray('post list --format=ids');
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

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module post list --format=ids",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                $timeoutValue
            ],
            "exit 0"
        );

        $this->module()->cliToArray('post list --format=ids');
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

        $this->module();
    }

    public function nullTimeoutValues(): array
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
    }

    /**
     * It should support and allow-root configuration parameter
     *
     * @test
     */
    public function should_support_and_allow_root_configuration_parameter()
    {
        $this->config['allow-root'] = true;

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module --allow-root core version",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "exit 0"
        );

        $this->module()->cli('core version');
    }

    /**
     * It should forward options from the configuration to the wp-cli command
     *
     * @test
     */
    public function should_forward_options_from_the_configuration_to_the_wp_cli_command()
    {
        $this->config['some-option'] = 'some-value';

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module --some-option=some-value core version",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "exit 0"
        );

        $this->module()->cli(['core', 'version']);
    }

    /**
     * It should allow getting a command output as a string
     *
     * @test
     */
    public function should_allow_getting_a_command_output_as_a_string()
    {
        $adminEmail = 'luca@theaveragedev.com';

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module option get admin_email",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo -n $adminEmail"
        );

        $this->assertEquals($adminEmail, $this->module()->cliToString(['option', 'get', 'admin_email']));
    }

    /**
     * It should handle exceptions thrown by the process by throwing
     *
     * @test
     */
    public function should_handle_exceptions_thrown_by_the_process_by_throwing()
    {
        $this->config['throw'] = true;

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module invalid",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'echo -n "error!" 1>&2; exit 1'
        );

        $this->expectException(ModuleException::class);

        $this->module()->cliToString(['invalid']);
    }

    /**
     * It should handle exceptions thrown by the process
     *
     * @test
     */
    public function should_handle_exceptions_thrown_by_the_process()
    {
        $this->config['throw'] = false;

        $this->assertProcCallArgsAndReturn(
            [
                "/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module invalid",
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'echo -n "error!" 1>&2; exit 1'
        );

        $this->assertEquals('error!', $this->module()->cliToString(['invalid']));
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
        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module widget add rss sidebar --title=My feedx --url="https://wordpress.org/news/feed/"',
                '/tests/unit/lucatume/WPBrowser/Module',
                ['WP_CLI_STRICT_ARGS_MODE' =>  '1'],
                null,
                60
            ],
            "echo -n '$output'"
        );

        $this->assertEquals($output,
            $this->module()->cliToString([
                'widget',
                'add',
                'rss',
                'sidebar',
                '--title=My feedx',
                '--url="https://wordpress.org/news/feed/"'
            ]));
    }

    public function envParametersDataProvider(): array
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

        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module core version',
                '/tests/unit/lucatume/WPBrowser/Module',
                [$expectedEnvName =>  $expectedEnvValue],
                null,
                60
            ],
            "echo -n '5.2.2'"
        );

        $this->assertEquals('5.2.2', $this->module()->cliToString(['core', 'version']));
    }

    /**
     * It should not throw on 0 exit status code
     *
     * @test
     */
    public function should_not_throw_on_0_exit_status_code()
    {
        $this->config['throw'] = true;

        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module test',
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo -n 'stdout'"
        );

        $this->assertEquals('stdout', $this->module()->cliToString(['test']));
    }

    /**
     * It should output stderr on 0 exit status code and wrong stdout
     *
     * @test
     */
    public function should_output_stderr_on_0_exit_status_code_and_wrong_stdout()
    {
        $this->config['throw'] = true;

        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module test',
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'echo -n "stderr" 1>&2; exit 0'
        );

        $this->assertEquals('stderr', $this->module()->cliToString(['test']));
    }

    /**
     * It should throw if exit code not 0 and stderr empty
     *
     * @test
     */
    public function should_throw_if_exit_code_not_0_and_stderr_empty()
    {
        $this->config['throw'] = true;

        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module test',
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'exit 1'
        );

        $this->expectException(ModuleException::class);

        $this->module()->cliToString(['test']);
    }

    /**
     * It should throw if trying to grab output from last command when no command ever ran
     *
     * @test
     */
    public function should_throw_if_trying_to_grab_output_from_last_command_when_no_command_ever_ran()
    {
        $this->expectException(ModuleException::class);

        $this->module()->grabLastShellOutput();
    }

    /**
     * It should return empty string when grabbing output of last command that produced no output
     *
     * @test
     */
    public function should_return_empty_string_when_grabbing_output_of_last_command_that_produced_no_output()
    {
        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module test',
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            'exit 0'
        );

        $cli = $this->module();
        $cli->cli(['test']);
        $lastOutput = $cli->grabLastShellOutput();

        $this->assertEquals('', $lastOutput);
    }

    /**
     * It should allow grabbing the output of the last ran command
     *
     * @test
     */
    public function should_allow_grabbing_the_output_of_the_last_ran_command()
    {
        $this->assertProcCallArgsAndReturn(
            [
                '/usr/local/bin/php /vendor/wp-cli/wp-cli/php/boot-fs.php --path=/tests/unit/lucatume/WPBrowser/Module test',
                '/tests/unit/lucatume/WPBrowser/Module',
                [],
                null,
                60
            ],
            "echo -n 'some output from the command'"
        );

        $cli = $this->module();
        $cli->cli(['test']);
        $lastOutput = $cli->grabLastShellOutput();

        $this->assertEquals('some output from the command', $lastOutput);
    }

}
