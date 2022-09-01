<?php
/**
 * An exception thrown while issuing a wp-cli command.
 *
 * @package lucatume\WPBrowser\Exceptions
 */

namespace lucatume\WPBrowser\Exceptions;

use Symfony\Component\Process\Process;

/**
 * Class WpCliException
 *
 * @package lucatume\WPBrowser\Exceptions
 */
class WpCliException extends \Exception
{
    /**
     * Builds and returns an exception to indicate a type of variable cannot be set.
     *
     * @param string        $type           The type the command is trying to set.
     * @param array<string> $supportedTypes The supported value types..
     *
     * @return WpCliException The built exception.
     */
    public static function becauseTypeCannotBeSet($type, array $supportedTypes): self
    {
        return new self(sprintf(
            'Cannot set this type of value [%s]; supported types are [%s])',
            $type,
            implode(', ', $supportedTypes)
        ));
    }

    /**
     * Builds and returns an exception to indicate the WP_CLI\Configurator class cannot be found..\
     *
     * @return WpCliException The built exception.
     */
    public static function becauseConfiguratorClassCannotBeFound(): self
    {
        return new self('Could not find the path to embedded WPCLI Configurator class');
    }

    /**
     * Builds and returns an exception to indicate a command failed.
     *
     * @param Process $commandProcess The process that ran the command that failed.
     *
     * @return WpCliException The built exception.
     */
    public static function becauseACommandFailed(Process $commandProcess): self
    {
        return new self(sprintf(
            "Command failed with status %s.\nOutput: %s\nError: %s",
            $commandProcess->getExitCode(),
            $commandProcess->getOutput(),
            $commandProcess->getErrorOutput()
        ));
    }

    /**
     * Builds and returns an exception to indicate a command was invoked but wp-cli root dir was not set up first.
     *
     * @return WpCliException The built exception.
     */
    public static function becauseCommandRequiresSetUp(): self
    {
        return new self(
            'This command requires wp-cli to be set up for a specific directory:'
            . ' did you call the `setUpWpCli` method first?'
        );
    }

    /**
     * Builds and returns an exception to indicate the wp-cli `\Server_Command` class was not be found..
     *
     * @return WpCliException The built exception.
     */
    public static function becauseServerCommandClassWasNotFound(): self
    {
        return new self(
            'The `\Server_Command` class could not be loaded or does not exist:'
            . ' did you add the `wp-cli/server-command` package as a Composer requirement?' .
            'Did you run `composer dump-autoload` to solve autoload file issues?'
        );
    }

    /**
     * Builds and returns an exception to indicate the wp-cli server router file was not be found.
     *
     * @param string $routerFile The path to the presumed router file location.
     *
     * @return WpCliException The built exception.
     */
    public static function becauseRouterFileWasNotFound($routerFile): self
    {
        return new self(
            "wp-cli server router file was not found [$routerFile]: did you add the `wp-cli/server-command`" .
            ' package to Composer dependencies?'
        );
    }
}
