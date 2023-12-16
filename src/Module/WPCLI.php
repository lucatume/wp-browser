<?php
/**
 * Wraps calls to the wp-cli tool.
 *
 * @package Codeception\Module
 */

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\WordPress\CliProcess;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Class WPCLI
 *
 * @package Coteception\Module
 */
class WPCLI extends Module
{
    public const DEFAULT_TIMEOUT = 60;

    /**
     * @var array<string,bool>
     */
    private static array $globalArgs = [
        // 'path' => false, // Already part of the required configuration.
        'url' => false,
        'user' => false,
        'skip-plugins' => true,
        'skip-themes' => true,
        'skip-packages' => true,
        'require' => false,
        'exec' => false,
        'context' => false,
        'color' => true,
        'no-color' => true,
        'debug' => true,
        'quiet' => true
    ];
    /**
     * @var array<string>
     */
    private static array $arrayArgs = ['require', 'exec'];

    /**
     * A list of the module required fields.
     *
     * @var array<string>
     */
    protected array $requiredFields = ['path'];

    /**
     * An array of configuration variables and their default values.
     *
     * @var array{
     *    url?: string,
     *    user?: string|int,
     *    skip-plugins?: bool,
     *    skip-themes?: bool,
     *    skip-packages?: bool,
     *    require?: string|string[],
     *    exec?: string|string[],
     *    context?: string,
     *    color?: bool,
     *    no-color?: bool,
     *    debug?: bool,
     *    quiet?: bool,
     *    throw: bool,
     *    timeout: float,
     *    cache-dir?: string,
     *    config-path?: string,
     *    custom-shell?: string,
     *    packages-dir?: string
     * }
     */
    protected array $config = [
        'throw' => true,
        'timeout' => 60,
    ];

    private ?CliProcess $lastCliProcess = null;

    /**
     * Executes a wp-cli command targeting the test WordPress installation.
     *
     * @example
     * ```php
     * // Activate a plugin via wp-cli in the test WordPress site.
     * $I->cli(['plugin', 'activate', 'my-plugin']);
     * // Change a user password.
     * $I->cli(['user', 'update', 'luca', '--user_pass=newpassword']);
     * ```
     *
     * @param string|array<string> $command        The command to execute.
     * @param array<string,string|false>|null $env An array of environment variables to pass to the command.
     * @param mixed|null $input                    The input to pass to the command, a stream resource or a \Traversable
     *                                             instance.
     *
     * @return int The command exit value; `0` usually means success.
     *
     * @throws ModuleException If the command execution fails and the `throw` configuration option is set to `true`.
     */
    public function cli(string|array $command = ['core', 'version'], ?array $env = null, mixed $input = null): int
    {
        /**
         * The config is now validated and the values are defined.
         *
         * @var array{
         *    path: string,
         *    url?: string,
         *    user?: string|int,
         *    skip-plugins?: bool,
         *    skip-themes?: bool,
         *    skip-packages?: bool,
         *    require?: string|string[],
         *    exec?: string|string[],
         *    context?: string,
         *    color?: bool,
         *    no-color?: bool,
         *    debug?: bool,
         *    quiet?: bool,
         *    throw: bool,
         *    timeout: float,
         *    cache-dir?: string,
         *    config-path?: string,
         *    custom-shell?: string,
         *    packages-dir?: string
         * } $config
         */
        $config = $this->config;
        $env = array_replace($this->getDefaultEnv(), (array)($config['env'] ?? []), (array)$env);

        if (is_string($command)) {
            $command = explode(' ', $command);
        }

        $command = $this->addStrictOptionsFromConfig($command);

        $cliProcess = new CliProcess($command, $config['path'], $env, $input, $config['timeout']);

        $this->debugSection('WPCLI command', $cliProcess->getCommandLine());

        try {
            $cliProcess->run();
        } catch (ProcessTimedOutException) {
            throw new ModuleException(
                __CLASS__,
                sprintf(
                    'The command "%s" timed out after %d seconds.',
                    $cliProcess->getCommandLine(),
                    $config['timeout']
                )
            );
        }

        $this->lastCliProcess = $cliProcess;

        $this->debugSection('WPCLI STDOUT', $cliProcess->getOutput());
        $this->debugSection('WPCLI STDERR', $cliProcess->getErrorOutput());

        /** @var int $exitCode The process terminated at this stage. */
        $exitCode = $cliProcess->getExitCode();

        $this->debugSection('WPCLI exit code', print_r($exitCode, true));

        if ($config['throw'] && $exitCode !== 0) {
            throw new ModuleException(
                __CLASS__,
                sprintf(
                    'The command "%s" failed with exit code %d.',
                    $cliProcess->getCommandLine(),
                    $exitCode
                )
            );
        }

        return $exitCode;
    }

    /**
     * @throws ModuleException
     */
    public function grabLastCliProcess(): CliProcess
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet; cannot grab last cli process.'
            );
        }
        return $this->lastCliProcess;
    }

    /**
     * Returns the shell output of the last command.
     *
     * @return string The output produced by the last shell command.
     *
     * @throws ModuleException If no prior command ran.
     */
    public function grabLastShellOutput(): string
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet; cannot grab output.'
            );
        }

        return $this->lastCliProcess->getOutput();
    }

    /**
     * Returns the shell error output of the last command.
     *
     * @return string The error output produced by the last shell command, if any.
     *
     * @throws ModuleException If no prior command ran.
     */
    public function grabLastShellErrorOutput(): string
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet; cannot grab error output.'
            );
        }

        return $this->lastCliProcess->getErrorOutput();
    }


    /**
     * Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.
     *
     * @example
     * ```php
     * // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
     * $inactiveThemes = $I->cliToArray(['theme', 'list', '--status=inactive', '--field=name']);
     * // Get the list of installed plugins and only keep the ones starting with "foo".
     * $fooPlugins = $I->cliToArray(['plugin', 'list', '--field=name'], function($output){
     *      return array_filter(explode(PHP_EOL, $output), function($name){
     *              return strpos(trim($name), 'foo') === 0;
     *      });
     * });
     * ```
     *
     * @param array<string> $command               The string of command and parameters as it would be passed to wp-cli
     *                                             minus `wp`. For back-compatibility purposes you can still pass the
     *                                             commandline as a string, but the array format is the preferred and
     *                                             supported method.
     * @param callable|null $splitCallback         An optional callback function to split the results array.
     * @param array<string,string|false>|null $env An array of environment variables to pass to the command.
     * @param mixed $input                         The input to pass to the command, a stream resource or a \Traversable
     *                                             instance.
     *
     * @return array<string> An array containing the output of wp-cli split into single elements.
     *
     * @throws ModuleException
     */
    public function cliToArray(
        array $command,
        callable $splitCallback = null,
        ?array $env = null,
        mixed $input = null
    ): array {
        $this->cli($command, $env, $input);

        /** @var CliProcess $cliProcess Set by the previous method. */
        $cliProcess = $this->lastCliProcess;

        $output = $cliProcess->getOutput();

        if ($splitCallback) {
            return $splitCallback($output);
        }

        return explode(PHP_EOL, $output);
    }

    /**
     * Returns the output of a wp-cli command as a string.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $adminEmail = $I->cliToString('option get admin_email');
     * // Get the list of active plugins in JSON format, two ways.
     * $activePlugins = $I->cliToString(['plugin', 'list','--status=active', '--format=json']);
     * $activePlugins = $I->cliToString(['option', 'get', 'active_plugins' ,'--format=json']);
     * ```
     *
     * @param array<string> $command               The string of command and parameters as it would be passed to wp-cli
     *                                             minus `wp`.
     *                                             For back-compatibility purposes you can still pass the commandline
     *                                             as a string, but the array format is the preferred and supported
     *                                             method.
     * @param array<string,string|false>|null $env An array of environment variables to pass to the command.
     * @param mixed $input                         The input to pass to the command, a stream resource or a
     *                                             \Traversable
     *
     * @return string The output of the command.
     *
     * @throws ModuleException
     */
    public function cliToString(array $command, ?array $env = null, mixed $input = null): string
    {
        $this->cli($command, $env, $input);

        /** @var CliProcess $cliProcess Set by the previous method. */
        $cliProcess = $this->lastCliProcess;

        return $cliProcess->getOutput();
    }

    /**
     * Checks that output from last command contains text.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeInShellOutput('admin@example.org');
     * ```
     *
     * @param string $text The text to assert is in the output.
     *
     * @throws ModuleException
     */
    public function seeInShellOutput(string $text): void
    {
        $this->assertStringContainsString($text, $this->grabLastShellOutput());
    }

    /**
     * Checks that output from last command doesn't contain text.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('plugin list --status=active');
     * $I->dontSeeInShellOutput('my-inactive/plugin.php');
     * ```
     *
     * @param string $text The text to assert is not in the output.
     *
     * @throws ModuleException
     */
    public function dontSeeInShellOutput(string $text): void
    {
        $this->assertStringNotContainsString($text, $this->grabLastShellOutput());
    }

    /**
     * Checks that output from the last command matches a given regular expression.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeShellOutputMatches('/^\S+@\S+$/');
     * ```
     *
     * @param string $regex The regex pattern, including delimiters, to assert the output matches against.
     *
     * @throws ModuleException
     */
    public function seeShellOutputMatches(string $regex): void
    {
        $this->assertRegExp($regex, $this->grabLastShellOutput());
    }

    /**
     * Checks that output from the last command doesn't match a given regular expression.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get siteurl');
     * $I->dontSeeShellOutputMatches('/^http/');
     * ```
     *
     * @param string $regex The regex pattern, including delimiters, to assert the output doesn't match against.
     *
     * @throws ModuleException
     */
    public function dontSeeShellOutputMatches(string $regex): void
    {
        $this->assertNotRegExp($regex, $this->grabLastShellOutput());
    }

    /**
     * Checks the result code from the last command.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeResultCodeIs(0);
     * ```
     *
     * @param int $code The desired result code.
     *
     * @throws ModuleException
     */
    public function seeResultCodeIs(int $code): void
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet: cannot check its exit code.'
            );
        }

        $actual = $this->lastCliProcess->getExitCode();
        $this->assertEquals(
            $code,
            $actual,
            sprintf(
                'The command exited with code %d, expected code %d.',
                $actual,
                $code
            )
        );
    }

    /**
     * Checks the result code from the last command.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('invalid command');
     * $I->seeResultCodeIsNot(0);
     * ```
     *
     * @param int $code The result code the command should not have exited with.
     *
     * @throws ModuleException If no command has run yet.
     */
    public function seeResultCodeIsNot(int $code): void
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet: cannot check its exit code.'
            );
        }

        $actual = $this->lastCliProcess->getExitCode();
        $this->assertNotEquals(
            $code,
            $actual,
            sprintf(
                'The command exited with code %d, but it should not have.',
                $actual
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function validateConfig(): void
    {
        parent::validateConfig();
        $this->validatePath();

        $this->config['throw'] = (bool)$this->config['throw'];
        $this->config['debug'] = (bool)($this->config['debug'] ?? false);
        $this->validateTimeout();

        foreach (['cache-dir', 'config-path', 'custom-shell', 'packages-dir'] as $stringKey) {
            if (empty($this->config[$stringKey])) {
                unset($this->config[$stringKey]);
            } elseif (!is_string($this->config[$stringKey])) {
                throw new ModuleConfigException($this, ucfirst($stringKey) . ' must be a string.');
            }
        }

        foreach (['skip-plugins', 'skip-themes', 'skip-packages', 'debug', 'quiet', 'color', 'no-color'] as $boolKey) {
            if (empty($this->config[$boolKey])) {
                unset($this->config[$boolKey]);
            } else {
                $this->config[$boolKey] = (bool)$this->config[$boolKey];
            }
        }

        if (empty($this->config['context'])) {
            unset($this->config['context']);
        } elseif (!is_string($this->config['context'])) {
            throw new ModuleConfigException($this, 'Context must be a string.');
        }

        foreach (['require', 'exec'] as $arrayKey) {
            if (empty($this->config[$arrayKey])) {
                unset($this->config[$arrayKey]);
            } else {
                $this->config[$arrayKey] = (array)$this->config[$arrayKey];
                if (!Arr::containsOnly($this->config[$arrayKey], 'string')) {
                    throw new ModuleConfigException($this, ucfirst($arrayKey) . ' must be an array.');
                }
            }
        }
    }

    /**
     * @throws ModuleConfigException
     */
    private function validateTimeout(): void
    {
        $timeout = $this->config['timeout'] ?? static::DEFAULT_TIMEOUT;

        if (!(is_numeric($timeout) && $timeout > 0)) {
            throw new ModuleConfigException($this, message: 'Timeout [' . print_r($timeout, true) . '] is not valid.');
        }

        $this->config['timeout'] = (float)$timeout;
    }

    /**
     * @throws ModuleConfigException
     */
    private function validatePath(): void
    {
        /**
         * The path config parameter is set at this point.
         *
         * @var array{path: string} $config
         */
        $config = $this->config;
        if (!(
            $config['path']
            && is_dir($config['path'])
            && is_file($config['path'] . '/wp-load.php')
            && is_file($config['path'] . '/wp-settings.php')
            && is_file($config['path'] . '/wp-includes/version.php')
        )) {
            throw new ModuleConfigException(
                __CLASS__,
                'Specified path [' . $config['path']
                . '] is not a directory containing a WordPress installation.'
            );
        }

        $this->config['path'] = Filesystem::realpath($config['path']);
    }

    /**
     * @return array{
     *     WP_CLI_CACHE_DIR: string,
     *     WP_CLI_CONFIG_PATH?: string,
     *     WP_CLI_CUSTOM_SHELL?: string,
     *     WP_CLI_PACKAGES_DIR?: string,
     *     WP_CLI_DISABLE_AUTO_CHECK_UPDATE: true,
     *     WP_CLI_STRICT_ARGS_MODE: true
     * }
     */
    private function getDefaultEnv(): array
    {
        $env = [];

        /** @var array{
         *     cache-dir?: non-empty-string,
         *     config-path?: non-empty-string,
         *     custom-shell?: non-empty-string,
         *     packages-dir?: non-empty-string
         * } $config Validated config.
         */
        $config = $this->config;
        $cacheDir = $config['cache-dir'] ?? (Filesystem::cacheDir() . '/wp-cli');
        $env['WP_CLI_CACHE_DIR'] = Filesystem::mkdirp($cacheDir);

        if (isset($config['config-path'])) {
            $env['WP_CLI_CONFIG_PATH'] = $config['config-path'];
        }

        if (isset($config['custom-shell'])) {
            $env['WP_CLI_CUSTOM_SHELL'] = $config['custom-shell'];
        }

        if (isset($config['packages-dir'])) {
            $env['WP_CLI_PACKAGES_DIR'] = $config['packages-dir'];
        }

        // Disable update checks by default.
        $env['WP_CLI_DISABLE_AUTO_CHECK_UPDATE'] = true;

        // Args should be treated as strict by default.
        $env['WP_CLI_STRICT_ARGS_MODE'] = true;

        return $env;
    }

    /**
     * @param array<string> $command
     *
     * @return array<string>
     */
    private function addStrictOptionsFromConfig(array $command): array
    {
        $prepend = [];
        /** @var false|int $commandPos */
        $commandPos = Arr::searchWithCallback(static function (string $arg): bool {
            return !str_starts_with($arg, '--');
        }, $command);
        if ($commandPos !== false) {
            $inlineStrictArgs = array_slice($command, 0, $commandPos);
            $inlineStrictArgsStrings = implode(' ', $inlineStrictArgs);
        } else {
            $inlineStrictArgsStrings = implode(' ', $command);
        }

        $notOverriddenGlobalArgs = array_filter(
            self::$globalArgs,
            static function (string $globalArg) use (
                $inlineStrictArgsStrings
            ) {
                if (str_starts_with($globalArg, 'no-')) {
                    $globalArg = substr($globalArg, 3);
                }
                return empty(preg_match('/--(no-)*' . $globalArg . '(=|\\s+|$)/', $inlineStrictArgsStrings));
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($notOverriddenGlobalArgs as $globalArg => $isFlag) {
            if (str_contains($inlineStrictArgsStrings, '--' . $globalArg)) {
                // Overridden by inline value.
                continue;
            }
            if (empty($this->config[$globalArg])) {
                continue;
            }
            if ($isFlag) {
                $prepend[] = '--' . $globalArg;
            } elseif (in_array($globalArg, self::$arrayArgs, true)) {
                /** @var array<string|int> $globalArgValues */
                $globalArgValues = $this->config[$globalArg];
                foreach ($globalArgValues as $arg) {
                    $prepend[] = '--' . $globalArg . '=' . $arg;
                }
            } else {
                /** @var string|int $globalArgValue */
                $globalArgValue = $this->config[$globalArg];
                $prepend[] = '--' . $globalArg . '=' . $globalArgValue;
            }
        }

        if (count($prepend)) {
            array_unshift($command, ...$prepend);
        }

        return $command;
    }

    /**
     * Changes the path to the WordPress installation that WPCLI should use.
     *
     * This is the equivalent of the `--path` option.
     *
     * @example
     * ```php
     * // Operate on the installation specified in the `path` config parameter.
     * $I->cli(['core','version']);
     * // Change to another installation and run a command there.
     * $I->changeWpcliPath('var/wordpress-installation-two');
     * $I->cli(['core','version']);
     * ```
     *
     * @param string $path The new path to use.
     *
     * @throws ModuleConfigException|ModuleConfigException
     *
     */
    public function changeWpcliPath(string $path): void
    {
        $this->config['path'] = $path;
        $this->_reconfigure($this->config);
    }
}
