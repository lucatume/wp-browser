<?php
/**
 * Wraps calls to the wp-cli tool.
 *
 * @package Codeception\Module
 */

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\PHPUnit\TestCase;
use JsonException;
use tad\WPBrowser\Adapters\PHPUnit\Framework\Assert;
use tad\WPBrowser\Exceptions\WpCliException;
use tad\WPBrowser\Process\Process;
use tad\WPBrowser\Traits\WithWpCli;
use function tad\WPBrowser\buildCommandline;
use function tad\WPBrowser\requireCodeceptionModules;

//phpcs:disable
requireCodeceptionModules('WPCLI', ['Cli']);
//phpcs:enable

/**
 * Class WPCLI
 *
 * @package Codeception\Module
 */
class WPCLI extends Module
{
    use WithWpCli;

    public const DEFAULT_TIMEOUT = 60;

    /**
     * An array of keys that will not be passed from the configuration to the wp-cli command.
     *
     * @var array<string,mixed>
     */
    protected static array $blockedKeys = [
        'throw' => true,
        'timeout' => true,
        'debug' => true,
        'color' => true,
        'prompt' => true,
        'quiet' => true,
        'env' => [
            'strict-args' => false
        ]
    ];

    /**
     * A list of the module required fields.
     *
     * @var array<string>
     */
    protected array $requiredFields = ['path'];

    /**
     * The module pretty name in debug.
     *
     * @var string
     */
    protected string $prettyName = 'WPCLI';

    /**
     * The default wp-cli options.
     *
     * @var array<string>
     */
    protected array $options = [
        'ssh',
        'http',
        'url',
        'user',
        'skip-plugins',
        'skip-themes',
        'skip-packages',
        'require'
    ];

    /**
     * An array of configuration variables and their default values.
     *
     * @var array<string,mixed>
     */
    protected array $config = [
        'throw' => true,
        'timeout' => 60,
    ];

    /**
     * The process timeout.
     *
     * @var int|null
     */
    protected ?int $timeout;
    /**
     * @var string|null
     */
    protected ?string $lastOutput;
    /**
     * @var int|null
     */
    protected ?int $lastResultCode;

    /**
     * WPCLI constructor.
     *
     * @param ModuleContainer $moduleContainer The module container containing this module.
     * @param array<string,mixed>|null $config The module configuration.
     * @param Process|null $process The process adapter.
     *
     * @throws WpCliException If there's an issue setting up the module using the current configuration.
     */
    public function __construct(ModuleContainer $moduleContainer, ?array $config = null, Process $process = null)
    {
        parent::__construct($moduleContainer, $config);
        $wpRootFolder = $config['path'];
        $this->setUpWpCli($wpRootFolder, $process);
    }

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
     * @param string|array<string> $userCommand The string of command and parameters as it would be passed to wp-cli
     *                                          minus `wp`.
     *                                          For back-compatibility purposes you can still pass the commandline as a
     *                                          string, but the array format is the preferred and supported method.
     *
     * @return int|string The command exit value; `0` usually means success.
     *
     *
     * @throws ModuleConfigException If a required wp-cli file cannot be found or the WordPress path does not exist
     *                               at runtime.
     *
     * @throws ModuleException If the status evaluates to non-zero and the `throw` configuration
     *                                                parameter is set to `true`.
     */
    public function cli(array|string $userCommand = 'core version'): int|string
    {
        $return = $this->run($userCommand);

        return $return[1];
    }

    /**
     * Runs a wp-cli command and returns its output and status.
     *
     * @param string|array<string> $userCommand The user command, in the format supported by the Symfony Process class.
     *
     * @return array<string|int> The command process output and status.
     *
     * @throws ModuleConfigException If the wp-cli path is wrong.
     * @throws ModuleException If there's an issue while running the command or encoding the output.
     * @throws JsonException If there's an issue encoding the output.
     */
    protected function run(array|string $userCommand): array
    {
        $this->validatePath();

        $userCommand = buildCommandline($userCommand);

        /**
         * Set an environment variable to let client code know the request is coming from the host machine.
         * Set the value to a string to make it so that the process will pick it up while populating the env.
         */
        putenv('WPBROWSER_HOST_REQUEST="1"');
        $_ENV['WPBROWSER_HOST_REQUEST'] = '1';

        $this->debugSection('command', $userCommand);

        $command = array_merge($this->getConfigOptions($userCommand), (array)$userCommand);
        $env = $this->buildProcessEnv();

        $this->debugSection('command with configuration options', $command);
        $this->debugSection('command with environment', $env);

        try {
            $process = $this->executeWpCliCommand($command, $this->timeout, $env);
        } catch (WpCliException $e) {
            if (!empty($this->config['throw'])) {
                throw new ModuleException($this, $e->getMessage());
            }

            $this->debugSection('command exception', $e->getMessage());

            $this->lastOutput = '';
            $this->lastResultCode = 1;

            return ['', 1];
        }

        $output = $process->getOutput() ?: $process->getError();
        $status = $process->getExitCode();

        // If the process returns `null`, then it's not terminated.
        if ($status === null) {
            throw new ModuleException(
                $this,
                'Command process did not terminate; commandline: ' . (string)$process->getExecCommand()
            );
        }

        $this->debugSection('output', $output);
        $this->debugSection(' status', $status);

        $this->evaluateStatus($output, $status);

        $this->lastOutput = $output;
        $this->lastResultCode = $status;

        return [$output, $status];
    }

    /**
     * Validates the configuration path to make sure it's a directory.
     *
     *
     * @throws ModuleConfigException If the configuration path is not a directory.
     */
    protected function validatePath(): void
    {
        if (!is_dir($this->config['path'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'Specified path [' . $this->config['path'] . '] is not a directory.'
            );
        }

        $this->wpCliWpRootDir = realpath($this->config['path']) ?: $this->config['path'];
    }

    /**
     * {@inheritDoc}
     *
     * @param string $title The section title.
     * @param string|array<string>|mixed $message The message to debug.
     *
     *
     * @throws JsonException If there's an issue while encoding the message.
     */
    protected function debugSection(string $title, mixed $message): void
    {
        parent::debugSection($this->prettyName . ' ' . $title, $message);
    }

    /**
     * Returns an associative array of wp-cli options parsed from the config array.
     *
     * Users can set additional options that will be passed to the wp-cli command; here is where they are parsed.
     *
     * @param string|array<string>|null $userCommand The user command to parse for inline options.
     *
     * @return array<string,mixed> An associative array of options, parsed from the current config.
     */
    protected function getConfigOptions(array|string $userCommand = null): array
    {
        $inlineOptions = $this->parseWpCliInlineOptions((array)$userCommand);
        $configOptions = array_diff_key($this->config, static::$blockedKeys, $inlineOptions);
        unset($configOptions['path']);

        if (empty($configOptions)) {
            return [];
        }

        return $this->wpCliOptions($configOptions);
    }

    /**
     * Builds the process environment from the configuration options.
     *
     * @return array{WP_CLI_CACHE_DIR?: mixed, WP_CLI_CONFIG_PATH?: mixed, WP_CLI_CUSTOM_SHELL?: mixed, WP_CLI_DISABLE_AUTO_CHECK_UPDATE?: string, WP_CLI_PACKAGES_DIR?: mixed, WP_CLI_PHP?: mixed, WP_CLI_PHP_ARGS?: mixed, WP_CLI_STRICT_ARGS_MODE?: string} An associative array of environment variables..
     */
    protected function buildProcessEnv(): array
    {
        return array_filter([
            'WP_CLI_CACHE_DIR' => $this->config['env']['cache-dir'] ?? false,
            'WP_CLI_CONFIG_PATH' => $this->config['env']['config-path'] ?? false,
            'WP_CLI_CUSTOM_SHELL' => $this->config['env']['custom-shell'] ?? false,
            'WP_CLI_DISABLE_AUTO_CHECK_UPDATE' => empty($this->config['env']['disable-auto-check-update']) ? '0' : '1',
            'WP_CLI_PACKAGES_DIR' => $this->config['env']['packages-dir'] ?? false,
            'WP_CLI_PHP' => $this->config['env']['php'] ?? false,
            'WP_CLI_PHP_ARGS' => $this->config['env']['php-args'] ?? false,
            'WP_CLI_STRICT_ARGS_MODE' => !empty($this->config['env']['strict-args']) ? '1' : false,
        ]);
    }

    /**
     * Evaluates the exit status of the command.
     *
     * @param string $output The process output.
     * @param int $status The process status code.
     *
     *
     * @throws ModuleException If the exit status is lt 0 and the module configuration is set to throw.
     */
    protected function evaluateStatus(string $output, int $status): void
    {
        if ((int)$status !== 0 && !empty($this->config['throw'])) {
            $message = "wp-cli terminated with status [{$status}] and output [{$output}]\n\nWPCLI module is configured "
                . 'to throw an exception when wp-cli terminates with an error status; '
                . 'set the `throw` parameter to `false` to avoid this.';

            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Returns the shell output of the last command.
     *
     * @return string|null The output produced by the last shell command, if any.
     *
     * @throws ModuleException If no prior command ran.
     */
    public function grabLastShellOutput(): ?string
    {
        if (!isset($this->lastOutput)) {
            throw new ModuleException($this, 'No output is set yet. Did you forget to run a command?');
        }

        return $this->lastOutput;
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
     * @param string|array<string> $userCommand The string of command and parameters as it would be passed to wp-cli
     *                                            minus `wp`. For back-compatibility purposes you can still pass the
     *                                            commandline as a string, but the array format is the preferred and
     *                                            supported method.
     * @param callable|null $splitCallback An optional callback function to split the results array.
     *
     * @return array<string> An array containing the output of wp-cli split into single elements.
     *
     * @throws ModuleConfigException If the path to the WordPress installation does not exist.
     * @throws ModuleException If the $splitCallback function does not return an array.
     */
    public function cliToArray(array|string $userCommand = 'post list --format=ids', callable $splitCallback = null): array
    {
        $output = (string)$this->cliToString($userCommand);

        if (empty($output)) {
            return [];
        }

        $hasSplitCallback = null !== $splitCallback && is_callable($splitCallback);
        $originalOutput = $output;

        if (is_callable($splitCallback)) {
            $output = $splitCallback($output, $userCommand, $this);
        } else {
            $output = !preg_match('/[\\n]+/', $output) ?
                preg_split('/\\s+/', $output)
                : preg_split('/\\s*\\n+\\s*/', $output);
        }

        if (!is_array($output) && $hasSplitCallback) {
            throw new ModuleException(
                __CLASS__,
                "Split callback must return an array, it returned: \n" . print_r(
                    $output,
                    true
                ) . "\nfor original output:\n" . print_r(
                    $originalOutput,
                    true
                )
            );
        }

        return empty($output) ? [] : array_map('trim', array_filter($output));
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
     * @param string|array<string> $userCommand The string of command and parameters as it would be passed to wp-cli
     *                                          minus `wp`.
     *                                          For back-compatibility purposes you can still pass the commandline as a
     *                                          string, but the array format is the preferred and supported method.
     *
     * @return int|string The command output, if any.
     *
     * @throws ModuleException If there's an exception while running the command and the module is configured to throw.
     *
     * @throws ModuleConfigException If the path to the WordPress installation does not exist.
     */
    public function cliToString(array|string $userCommand): int|string
    {
        $return = $this->run($userCommand);

        return $return[0];
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
     */
    public function seeInShellOutput(string $text): void
    {
        TestCase::assertStringContainsString($text, $this->lastOutput);
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
     */
    public function dontSeeInShellOutput(string $text): void
    {
        Assert::assertStringNotContainsString($text, (string)$this->lastOutput);
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
     */
    public function seeShellOutputMatches(string $regex): void
    {
        \PHPUnit\Framework\Assert::assertRegExp($regex, (string)$this->lastOutput);
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
     */
    public function seeResultCodeIs(int $code): void
    {
        $this->assertEquals($this->lastResultCode, $code, "result code is $code");
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
     */
    public function seeResultCodeIsNot(int $code): void
    {
        $this->assertNotEquals($this->lastResultCode, $code, "result code is $code");
    }

    /**
     * {@inheritDoc}
     */
    protected function validateConfig():void
    {
        parent::validateConfig();
        $this->validateTimeout();
    }

    /**
     * Validates the configuration timeout.
     *
     *
     * @throws ModuleConfigException If the configuration timeout is not valid.
     */
    protected function validateTimeout(): void
    {
        $timeout = static::DEFAULT_TIMEOUT;

        if (array_key_exists('timeout', $this->config)) {
            $timeout = empty($this->config['timeout']) ? null : $this->config['timeout'];
        }

        if (!($timeout === null || is_numeric($timeout))) {
            throw new ModuleConfigException($this, "Timeout [{$this->config['timeout']}] is not valid.");
        }

        $this->timeout = (int)$timeout;
    }
}
