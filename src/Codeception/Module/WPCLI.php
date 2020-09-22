<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Adapters\PHPUnit\Framework\Assert;
use tad\WPBrowser\Adapters\Process;
use tad\WPBrowser\Exceptions\WpCliException;
use tad\WPBrowser\Traits\WithWpCli;
use function tad\WPBrowser\buildCommandline;
use function tad\WPBrowser\requireCodeceptionModules;

//phpcs:disable
requireCodeceptionModules('WPCLI', [ 'Cli' ]);
//phpcs:enable

/**
 * Class WPCLI
 *
 * Wraps calls to the wp-cli tool.
 *
 * @package Codeception\Module
 */
class WPCLI extends Module
{
    use WithWpCli;

    const DEFAULT_TIMEOUT = 60;

    /**
     * An array of keys that will not be passed from the configuration to the wp-cli command.
     *
     * @var array<string,mixed>
     */
    protected static $blockedKeys = [
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
    protected $requiredFields = ['path'];

    /**
     * The module pretty name in debug.
     *
     * @var string
     */
    protected $prettyName = 'WPCLI';

    /**
     * The wp-cli boot path.
     * @var string
     */
    protected $bootPath;

    /**
     * The default wp-cli options.
     *
     * @var array<string>
     */
    protected $options = ['ssh', 'http', 'url', 'user', 'skip-plugins', 'skip-themes', 'skip-packages', 'require'];

    /**
     * An array of configuration variables and their default values.
     *
     * @var array<string,mixed>
     */
    protected $config = [
        'throw' => true,
        'timeout' => 60,
    ];

    /**
     * The process timeout.
     *
     * @var int|float|null
     */
    protected $timeout;
    /**
     * @var string
     */
    protected $lastOutput;
    /**
     * @var int
     */
    protected $lastResultCode;

    /**
     * WPCLI constructor.
     *
     * @param ModuleContainer $moduleContainer The module container containing this module.
     * @param array<string,mixed>|null      $config          The module configuration.
     * @param Process|null    $process         The process adapter.
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null, Process $process = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->wpCliProcess = $process ?: new Process();
    }

    /**
     * Executes a wp-cli command targeting the test WordPress installation.
     *
     * @param string|array<string> $userCommand The string of command and parameters as it would be passed to wp-cli
     *                                          minus `wp`.
     *                                          For back-compatibility purposes you can still pass the commandline as a
     *                                          string, but the array format is the preferred and supported method.
     *
     * @return int|string The command exit value; `0` usually means success.
     *
     *
     * @throws ModuleException If the status evaluates to non-zero and the `throw` configuration
     *                                                parameter is set to `true`.
     * @throws ModuleConfigException If a required wp-cli file cannot be found or the WordPress path does not exist
     *                               at runtime.
     *
     * @example
     * ```php
     * // Activate a plugin via wp-cli in the test WordPress site.
     * $I->cli(['plugin', 'activate', 'my-plugin']);
     * // Change a user password.
     * $I->cli(['user', 'update', 'luca', '--user_pass=newpassword']);
     * ```
     */
    public function cli($userCommand = 'core version')
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
     * @throws ModuleException If there's an issue while running the command.
     */
    protected function run($userCommand)
    {
        $this->validatePath();

        $userCommand = buildCommandline($userCommand);

        /**
         * Set an environment variable to let client code know the request is coming from the host machine.
         * Set the value to a string to make it so that Symfony\Process will pick it up while populating the env.
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

        $output = $process->getOutput() ?: $process->getErrorOutput();
        $status = $process->getExitCode();

        // If the process returns `null`, then it's not terminated.
        if ($status === null) {
            throw new ModuleException(
                $this,
                'Command process did not terminate; commandline: ' . $process->getCommandLine()
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
     * @return void
     *
     * @throws ModuleConfigException If the configuration path is not a directory.
     */
    protected function validatePath()
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
     * @return void
     */
    protected function debugSection($title, $message)
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
    protected function getConfigOptions($userCommand = null)
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
     * @return array<string,mixed> An associative array of environment variables..
     */
    protected function buildProcessEnv()
    {
        return array_filter([
            'WP_CLI_CACHE_DIR' => isset($this->config['env']['cache-dir']) ? $this->config['env']['cache-dir'] : false,
            'WP_CLI_CONFIG_PATH' => isset($this->config['env']['config-path']) ?
                $this->config['env']['config-path']
                : false,
            'WP_CLI_CUSTOM_SHELL' => isset($this->config['env']['custom-shell'])
                ? $this->config['env']['custom-shell']
                : false,
            'WP_CLI_DISABLE_AUTO_CHECK_UPDATE' => empty($this->config['env']['disable-auto-check-update']) ? '0' : '1',
            'WP_CLI_PACKAGES_DIR' => isset($this->config['env']['packages-dir']) ?
                $this->config['env']['packages-dir']
                : false,
            'WP_CLI_PHP' => isset($this->config['env']['php']) ? $this->config['env']['php'] : false,
            'WP_CLI_PHP_ARGS' => isset($this->config['env']['php-args']) ? $this->config['env']['php-args'] : false,
            'WP_CLI_STRICT_ARGS_MODE' => !empty($this->config['env']['strict-args']) ? '1' : false,
        ]);
    }

    /**
     * Evaluates the exit status of the command.
     *
     * @param string $output The process output.
     * @param int    $status The process status code.
     *
     * @return void
     *
     * @throws ModuleException If the exit status is lt 0 and the module configuration is set to throw.
     */
    protected function evaluateStatus($output, $status)
    {
        if ((int)$status !== 0 && !empty($this->config['throw'])) {
            $message = "wp-cli terminated with status [{$status}] and output [{$output}]\n\nWPCLI module is configured "
                . 'to throw an exception when wp-cli terminates with an error status; '
                . 'set the `throw` parameter to `false` to avoid this.';

            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.
     *
     * @param string|array<string> $userCommand   The string of command and parameters as it would be passed to wp-cli
     *                                            minus `wp`. For back-compatibility purposes you can still pass the
     *                                            commandline as a string, but the array format is the preferred and
     *                                            supported method.
     * @param callable             $splitCallback An optional callback function to split the results array.
     *
     * @return array<string> An array containing the output of wp-cli split into single elements.
     *
     * @throws \Codeception\Exception\ModuleException If the $splitCallback function does not return an array.
     * @throws ModuleConfigException If the path to the WordPress installation does not exist.
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
     */
    public function cliToArray($userCommand = 'post list --format=ids', callable $splitCallback = null)
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
     * @param string|array<string> $userCommand The string of command and parameters as it would be passed to wp-cli
     *                                          minus `wp`.
     *                                          For back-compatibility purposes you can still pass the commandline as a
     *                                          string, but the array format is the preferred and supported method.
     *
     * @return int|string The command output, if any.
     *
     * @throws ModuleConfigException If the path to the WordPress installation does not exist.
     * @throws ModuleException If there's an exception while running the command and the module is configured to throw.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $adminEmail = $I->cliToString('option get admin_email');
     * // Get the list of active plugins in JSON format, two ways.
     * $activePlugins = $I->cliToString(['plugin', 'list','--status=active', '--format=json']);
     * $activePlugins = $I->cliToString(['option', 'get', 'active_plugins' ,'--format=json']);
     * ```
     */
    public function cliToString($userCommand)
    {
        $return = $this->run($userCommand);

        return $return[0];
    }

    /**
     * Checks that output from last command contains text.
     *
     * @param string $text The text to assert is in the output.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeInShellOutput('admin@example.org');
     * ```
     *
     * @return void
     */
    public function seeInShellOutput($text)
    {
        \Codeception\PHPUnit\TestCase::assertStringContainsString($text, $this->lastOutput);
    }

    /**
     * Checks that output from last command doesn't contain text.
     *
     * @param string $text The text to assert is not in the output.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('plugin list --status=active');
     * $I->dontSeeInShellOutput('my-inactive/plugin.php');
     * ```
     *
     * @return void
     */
    public function dontSeeInShellOutput($text)
    {
        Assert::assertStringNotContainsString($text, $this->lastOutput);
    }

    /**
     * Checks that output from the last command matches a given regular expression.
     *
     * @param string $regex The regex pattern, including delimiters, to assert the output matches against.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeShellOutputMatches('/^\S+@\S+$/');
     * ```
     *
     * @return void
     */
    public function seeShellOutputMatches($regex)
    {
        \PHPUnit\Framework\Assert::assertRegExp($regex, $this->lastOutput);
    }

    /**
     * Checks the result code from the last command.
     *
     * @param int $code The desired result code.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeResultCodeIs(0);
     * ```
     *
     * @return void
     */
    public function seeResultCodeIs($code)
    {
        $this->assertEquals($this->lastResultCode, $code, "result code is $code");
    }

    /**
     * Checks the result code from the last command.
     *
     * @param int $code The result code the command should not have exited with.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('invalid command');
     * $I->seeResultCodeIsNot(0);
     * ```
     *
     * @return void
     */
    public function seeResultCodeIsNot($code)
    {
        $this->assertNotEquals($this->lastResultCode, $code, "result code is $code");
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function validateConfig()
    {
        parent::validateConfig();
        $this->validateTimeout();
    }

    /**
     * Validates the configuration timeout.
     *
     * @return void
     *
     * @throws ModuleConfigException If the configuration timeout is not valid.
     */
    protected function validateTimeout()
    {
        $timeout = static::DEFAULT_TIMEOUT;

        if (array_key_exists('timeout', $this->config)) {
            $timeout = empty($this->config['timeout']) ? null : $this->config['timeout'];
        }

        if (!($timeout === null || is_numeric($timeout))) {
            throw new ModuleConfigException($this, "Timeout [{$this->config['timeout']}] is not valid.");
        }

        $this->timeout = is_string($timeout) ? (float)$timeout : $timeout;
    }
}
