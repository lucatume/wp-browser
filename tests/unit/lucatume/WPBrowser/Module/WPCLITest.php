<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\CliProcess;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use PHPUnit\Framework\AssertionFailedError;
use stdClass;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class WPCLITest extends Unit
{
    use SnapshotAssertions;
    use TmpFilesCleanup;

    protected $backupGlobals = false;
    private static ?Installation $installation = null;

    public function _before(): void
    {
        if (self::$installation instanceof Installation) {
            return;
        }

        // Scaffold, configure and install a test WordPress installation.
        $wpRootDir = FS::tmpDir('wpcli_test_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new Db($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        self::$installation = Installation::scaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'http://wp.local',
                'admin',
                'secret',
                'admin@admin',
                'WPCLI Module Test Site'
            );
    }

    private function module(array $config): WPCLI
    {
        $moduleContainer = new ModuleContainer(new Di(), []);
        return new WPCLI($moduleContainer, $config);
    }

    /**
     * It should throw if path does not exist
     *
     * @test
     */
    public function should_throw_if_path_does_not_exist(): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->module(['path' => '/some/path/to/null']);
    }

    public function notPositiveIntegerTimeoutValues(): array
    {
        return [
            'negative' => [-1],
            'array' => [[1]],
            'object' => [new stdClass()],
        ];
    }

    /**
     * It should throw if timeout value is not positive integer
     *
     * @test
     * @dataProvider notPositiveIntegerTimeoutValues
     */
    public function should_throw_if_timeout_value_is_not_positive_integer($timeoutValue): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->module([
            'timeout' => $timeoutValue,
            'path' => self::$installation->getWpRootDir()
        ]);
    }

    public function stringConfigKeysProvider(): array
    {
        return [
            'cache-dir' => ['cache-dir'],
            'config-path' => ['config-path'],
            'custom-shell' => ['custom-shell'],
            'packages-dir' => ['packages-dir'],
        ];
    }

    /**
     * It should throw if expected string keys are not string keys
     *
     * @test
     * @dataProvider stringConfigKeysProvider
     */
    public function should_throw_if_expected_string_keys_are_not_string_keys(string $configKey): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->module([
            'path' => self::$installation->getWpRootDir(),
            $configKey => 23,
        ]);
    }

    /**
     * It should allow running wp-cli array commands
     *
     * @test
     */
    public function should_allow_running_wp_cli_array_commands(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir()
        ]);

        $this->assertEquals(0, $wpcli->cli(['core', 'is-installed']));
        $this->assertEquals(null, $wpcli->grabLastShellOutput());
        $this->assertEquals(null, $wpcli->grabLastShellErrorOutput());
        $wpcli->seeResultCodeIs(0);
        $wpcli->seeResultCodeIsNot(1);
    }

    /**
     * It should throw if trying to grab last shell output before running command
     *
     * @test
     */
    public function should_throw_if_trying_to_grab_last_shell_output_before_running_command(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir()
        ]);

        $this->expectException(ModuleException::class);

        $wpcli->grabLastShellOutput();
    }

    /**
     * It should throw if trying to grab last shell error output before running command
     *
     * @test
     */
    public function should_throw_if_trying_to_grab_last_shell_error_output_before_running_command(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir()
        ]);

        $this->expectException(ModuleException::class);

        $wpcli->grabLastShellErrorOutput();
    }

    /**
     * It should throw if trying to seeResultCodeIs before any command ran
     *
     * @test
     */
    public function should_throw_if_trying_to_see_result_code_is_before_any_command_ran(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir()
        ]);

        $this->expectException(ModuleException::class);

        $wpcli->seeResultCodeIs(0);
    }

    /**
     * It should throw if trying to seeResultCodeIsNot before any command ran
     *
     * @test
     */
    public function should_throw_if_trying_to_see_result_code_is_not_before_any_command_ran(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir()
        ]);

        $this->expectException(ModuleException::class);

        $wpcli->seeResultCodeIsNot(1);
    }

    /**
     * It should throw if throw configuration parameter is true and command fails
     *
     * @test
     */
    public function should_throw_if_throw_configuration_parameter_is_true_and_command_fails(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'throw' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $this->expectException(ModuleException::class);

        $wpcli->cli(['core', 'foo-bar']);
    }

    /**
     * It should not throw and return exit code if throw configuration parameter is false and command fails
     *
     * @test
     */
    public function should_not_throw_and_return_exit_code_if_throw_configuration_parameter_is_false_and_command_fails(
    ): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'throw' => false
        ]);

        $wpcli->cli(['core', 'foo-bar']);

        $wpcli->seeResultCodeIs(1);
    }

    /**
     * It should throw if command goes over timeout
     *
     * @test
     */
    public function should_throw_if_command_goes_over_timeout(): void
    {
        // The is-installed check is a slow one that should go over time.
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'timeout' => 0.01
        ]);

        $this->expectException(ModuleException::class);

        $wpcli->cli(['core', 'is-installed']);

        $wpcli->seeResultCodeIs(1);
    }

    /**
     * It should allow running commands with debug or not
     *
     * @test
     */
    public function should_allow_running_commands_with_debug_or_not(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'debug' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $this->assertEquals("6.1.1\n", $wpcli->grabLastShellOutput());

        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'debug' => false
        ]);

        $wpcli->cli(['core', 'version']);

        $this->assertMatchesStringSnapshot($wpcli->grabLastShellOutput());
    }

    /**
     * It should throw if trying to grab last wp-cli process before any ran
     *
     * @test
     */
    public function should_throw_if_trying_to_grab_last_wp_cli_process_before_any_ran(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir()
        ]);

        $this->expectException(ModuleException::class);

        $wpcli->grabLastCliProcess();
    }

    /**
     * It should allow configuring wp-cli env vars using configuration parameters
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_env_vars_using_configuration_parameters(): void
    {
        $tmpDir = FS::tmpDir('wp-cli', [
            'cache' => [],
            'wp-cli-config.yml' => 'color: true'
        ]);
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'cache-dir' => $tmpDir . '/cache',
            'config-path' => $tmpDir . '/wp-cli-config.yml',
            'custom-shell' => '/bin/sh',
            'packages-dir' => __DIR__,
        ]);

        $wpcli->cli(['core', 'version']);

        $processEnv = $wpcli->grabLastCliProcess()->getEnv();

        foreach ([
                     'WP_CLI_CACHE_DIR' => $tmpDir . '/cache',
                     'WP_CLI_CONFIG_PATH' => $tmpDir . '/wp-cli-config.yml',
                     'WP_CLI_CUSTOM_SHELL' => '/bin/sh',
                     'WP_CLI_PACKAGES_DIR' => __DIR__,
                 ] as $envVar => $expectedValue) {
            $this->assertEquals($expectedValue, $processEnv[$envVar]);
        }
    }

    /**
     * It should allow configuring wp-cli to use a specific user
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_use_a_specific_user(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'user' => 23
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--user=23',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    public function strictArgsInlineValuesProvider(): array
    {
        return [
            'url' => ['url', 'https://example.com', '--url=https://subdomain.example.com'],
            'user' => ['user', 23, '--user=89'],
            'skip-plugins' => ['skip-plugins', false, '--skip-plugins'],
            'skip-themes' => ['skip-themes', false, '--skip-themes'],
            'skip-packages' => ['skip-packages', false, '--skip-packages'],
            'require' => ['require', [], '--require=foo.php'],
            'require multiple' => ['require', [], '--require=foo.php', '--require=bar.php'],
            'exec' => ['exec', false, '--exec="echo \'hi\';"'],
            'exec multi' => ['exec', false, '--exec="echo \'hi\';"', '--exec="echo \'hello\';"'],
            'context' => ['context', 'development', '--context=foo'],
            'color overridden by --no-color' => ['color', true, '--no-color'],
            'no-color overridden by --color' => ['no-color', false, '--color'],
            'debug' => ['debug', false, '--debug'],
            'quiet' => ['quiet', false, '--quiet'],
        ];
    }

    /**
     * It should allow overriding strict args in command line
     *
     * @test
     * @dataProvider strictArgsInlineValuesProvider
     */
    public function should_allow_overriding_strict_args_in_command_line(
        string $strictArg,
        mixed $configValue,
        string $inlineValue
    ): void {
        $wpcli = $this->module([
            'throw' => false,
            'path' => self::$installation->getWpRootDir(),
            $strictArg => $configValue
        ]);

        $wpcli->cli([$inlineValue, 'core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                $inlineValue,
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to skip plugins
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_skip_plugins(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'skip-plugins' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--skip-plugins',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to skip themes
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_skip_themes(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'skip-themes' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--skip-themes',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to skip packages
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_skip_packages(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'skip-packages' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--skip-packages',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should throw if exec configuration parameter is not an array
     *
     * @test
     */
    public function should_throw_if_exec_configuration_parameter_is_not_an_array(): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->module([
            'path' => self::$installation->getWpRootDir(),
            'exec' => 23
        ]);
    }

    /**
     * It should throw if require configuration parameter is not an array
     *
     * @test
     */
    public function should_throw_if_require_configuration_parameter_is_not_an_array(): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->module([
            'path' => self::$installation->getWpRootDir(),
            'require' => 23
        ]);
    }

    /**
     * It should throw if exec configuration parameter is not an array of strings
     *
     * @test
     */
    public function should_throw_if_exec_configuration_parameter_is_not_an_array_of_strings(): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->module([
            'path' => self::$installation->getWpRootDir(),
            'exec' => ['echo "Hello World!"', 23]
        ]);
    }

    /**
     * It should throw if require configuration parameter is not an array of strings
     *
     * @test
     */
    public function should_throw_if_require_configuration_parameter_is_not_an_array_of_strings(): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->module([
            'path' => self::$installation->getWpRootDir(),
            'require' => ['php-file-1.php', 23]
        ]);
    }

    /**
     * It should allow configuring wp-cli to require one PHP file before execution
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_require_one_php_file_before_execution(): void
    {
        $tmpDir = FS::tmpDir('wp-cli', [
            'php-file-1.php' => '<?php echo "Hello World!";',
        ]);
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'require' => $tmpDir . '/php-file-1.php'
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--require=' . $tmpDir . '/php-file-1.php',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to require multiple PHP files before execution
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_require_multiple_php_files_before_execution(): void
    {
        $tmpDir = FS::tmpDir('wp-cli', [
            'php-file-1.php' => '<?php echo "Hello World!";',
            'php-file-2.php' => '<?php echo "Hello there!";',
        ]);
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'require' => [
                $tmpDir . '/php-file-1.php',
                $tmpDir . '/php-file-2.php',
            ]
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--require=' . $tmpDir . '/php-file-1.php',
                '--require=' . $tmpDir . '/php-file-2.php',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to execute PHP code before execution
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_execute_php_code_before_execution(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'exec' => 'echo "Hello World!";'
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--exec=echo "Hello World!";',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to execute multiple PHP code snippets before execution
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_execute_multiple_php_code_snippets_before_execution(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'exec' => [
                'echo "Hello World!";',
                'echo "Hello there!";',
            ]
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--exec=echo "Hello World!";',
                '--exec=echo "Hello there!";',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should throw if context configuration parameter is not a string
     *
     * @test
     */
    public function should_throw_if_context_configuration_parameter_is_not_a_string(): void
    {
        $this->expectException(ModuleConfigException::class);

        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'context' => 2389,
        ]);
    }

    /**
     * It should allow configuring wp-cli context, debug and quiet options
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_context_debug_and_quiet_options(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'debug' => true,
            'quiet' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--debug',
                '--quiet',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to run with color
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_run_with_color(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'color' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--color',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should allow configuring wp-cli to run without color
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_to_run_without_color(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'no-color' => true
        ]);

        $wpcli->cli(['core', 'version']);

        $commandLine = $wpcli->grabLastCliProcess()->getCommandLine();
        $wpCliPhar = CliProcess::getWpCliPharPathname();

        $expected = implode(' ',
            array_map('escapeshellarg', [
                PHP_BINARY,
                $wpCliPhar,
                '--no-color',
                'core',
                'version'
            ]));
        $this->assertEquals($expected, $commandLine);
    }

    /**
     * It should inherit env from current session when env not specified
     *
     * @test
     */
    public function should_inherit_env_from_current_session_when_env_not_specified(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $wpcli->cli(['eval', 'print_r($_ENV[\'HOME\']);', '--skip-wordpress']);

        $this->assertEquals($_ENV['HOME'], $wpcli->grabLastShellOutput());
    }

    /**
     * It should allow configuring wp-cli env to augment and override current env
     *
     * @test
     */
    public function should_allow_configuring_wp_cli_env_to_augment_and_override_current_env(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'env' => [
                'HOME' => '/home/username',
                'WP_CLI_CONFIG_PATH' => '/home/username/.wp-cli/config.yml',
            ]
        ]);

        $wpcli->cli(['eval', 'print_r($_ENV[\'HOME\']);', '--skip-wordpress']);

        $this->assertEquals('/home/username', $wpcli->grabLastShellOutput());

        $wpcli->cli(['eval', 'print_r($_ENV[\'WP_CLI_CONFIG_PATH\']);', '--skip-wordpress']);

        $this->assertEquals('/home/username/.wp-cli/config.yml', $wpcli->grabLastShellOutput());
    }

    /**
     * It should support overriding config env with runtime env
     *
     * @test
     */
    public function should_support_overriding_config_env_with_runtime_env(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
            'env' => [
                'HOME' => '/home/username',
            ]
        ]);

        $wpcli->cli(['eval', 'print_r($_ENV[\'HOME\']);', '--skip-wordpress'], [
            'HOME' => '/home/anotheruser'
        ]);

        $this->assertEquals('/home/anotheruser', $wpcli->grabLastShellOutput());
    }

    /**
     * It should allow getting command output in array format with default split callback
     *
     * @test
     */
    public function should_allow_getting_command_output_in_array_format_with_default_split_callback(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $output = $wpcli->cliToArray(['eval', 'echo "Hello\nWorld";', '--skip-wordpress']);

        $this->assertEquals(['Hello', 'World'], $output);
    }

    /**
     * It should allow getting command output in array format with user-defined split callback
     *
     * @test
     */
    public function should_allow_getting_command_output_in_array_format_with_user_defined_split_callback(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $output = $wpcli->cliToArray(['eval', 'echo "Hello###World";', '--skip-wordpress'], function ($output) {
            return explode("###", $output);
        });

        $this->assertEquals(['Hello', 'World'], $output);
    }

    /**
     * It should allow getting command output in string format
     *
     * @test
     */
    public function should_allow_getting_command_output_in_string_format(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $output = $wpcli->cliToString(['eval', 'echo "Hello\nWorld";', '--skip-wordpress']);

        $this->assertEquals("Hello\nWorld", $output);
    }

    /**
     * It should throw if trying to see in shell output and string not there
     *
     * @test
     */
    public function should_throw_if_trying_to_see_in_shell_output_and_string_not_there(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $this->expectException(AssertionFailedError::class);

        $wpcli->cli(['eval', 'echo "Hello World";', '--skip-wordpress']);

        $wpcli->seeInShellOutput('Hello there');
    }

    /**
     * It should throw if trying to see string not in output and string is in output
     *
     * @test
     */
    public function should_throw_if_trying_to_see_string_not_in_output_and_string_is_in_output(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $this->expectException(AssertionFailedError::class);

        $wpcli->cli(['eval', 'echo "Hello World";', '--skip-wordpress']);

        $wpcli->dontSeeInShellOutput('Hello');
    }

    /**
     * It should allow asserting on shell output
     *
     * @test
     */
    public function should_allow_asserting_on_shell_output(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $wpcli->cli(['eval', 'echo "Hello\nWorld";', '--skip-wordpress']);

        $wpcli->seeInShellOutput('Hello');
        $wpcli->seeInShellOutput('World');
        $wpcli->dontSeeInShellOutput('Hello World');
    }

    /**
     * It should throw if trying to assert shell output matches pattern and does not match
     *
     * @test
     */
    public function should_throw_if_trying_to_assert_shell_output_matches_pattern_and_does_not_match(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $this->expectException(AssertionFailedError::class);

        $wpcli->cli(['eval', 'echo "Hello\nWorld";', '--skip-wordpress']);

        $wpcli->seeShellOutputMatches('/^Hello World$/');
    }

    /**
     * It should throw if trying to assert shell output does not match pattern and it matches
     *
     * @test
     */
    public function should_throw_if_trying_to_assert_shell_output_does_not_match_pattern_and_it_matches(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $this->expectException(AssertionFailedError::class);

        $wpcli->cli(['eval', 'echo "Hello There";', '--skip-wordpress']);

        $wpcli->dontSeeShellOutputMatches('/^Hello There$/');
    }

    /**
     * It should allow asserting shell ouptut matches regex pattern
     *
     * @test
     */
    public function should_allow_asserting_shell_ouptut_matches_regex_pattern(): void
    {
        $wpcli = $this->module([
            'path' => self::$installation->getWpRootDir(),
        ]);

        $wpcli->cli(['eval', 'echo "Hello\nWorld";', '--skip-wordpress']);

        $wpcli->seeShellOutputMatches('/^Hello/');
        $wpcli->seeShellOutputMatches('/World$/');
        $wpcli->dontSeeShellOutputMatches('/^Hello World$/');
        $wpcli->dontSeeShellOutputMatches('/There/');
    }
}
