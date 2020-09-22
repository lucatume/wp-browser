<?php
/**
 * Provides methods to debug WordPress filters using Codeception debug functions.
 *
 * @since   TBD
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

/**
 * Trait WithWordPressFilters
 *
 * @package tad\WPBrowser\Traits
 */
trait WithWordPressFilters
{
    /**
     * A callback function to format the arguments debug output.
     *
     * @var callable|null
     */
    private $wpFiltersFormatCallback;

    /**
     * Starts the debug of all WordPress filters and actions.
     *
     * The method hook on `all` filters and actions to debug their value.
     *
     * @example
     * ```php
     * // Start debugging all WordPress filters and action final and initial values.
     * $this->startWpFiltersDebug();
     *
     * // Run some code firing filters and debug them.
     *
     * // Stop debugging all WordPress filters and action final and initial values.
     * $this->stopWpFiltersDebug();
     * ```
     *
     * @param callable|null $format A callback function to format the arguments debug output; the callback will receive
     *                              the array of arguments as input.
     *
     * @return void
     */
    public function startWpFiltersDebug(callable $format = null)
    {
        if (!function_exists('add_filter')) {
            throw new \RuntimeException('Function "add_filter" is not defined; is WordPress loaded?');
        }
        if (!function_exists('add_action')) {
            throw new \RuntimeException('Function "add_action" is not defined; is WordPress loaded?');
        }
        $this->wpFiltersFormatCallback = $format;

        if (!defined('PHP_INT_MIN')) {
            // The `PHP_INT_MIN` constant is is defined on PHP 7.0, define it here if not defined.
            define('PHP_INT_MIN', ~PHP_INT_MAX);
        }
        add_filter('all', [$this, 'debugWpFilterInitial'], PHP_INT_MIN, 99);
        add_action('all', [$this, 'debugWpActionInitial'], PHP_INT_MIN, 99);
        add_filter('all', [$this, 'debugWpFilterFinal'], PHP_INT_MAX, 99);
        add_action('all', [$this, 'debugWpActionFinal'], PHP_INT_MAX, 99);
    }

    /**
     * Stops the debug of all WordPress filters and actions.
     *
     * @example
     * ```php
     * // Start debugging all WordPress filters and action final and initial values.
     * $this->startWpFiltersDebug();
     *
     * // Run some code firing filters and debug them.
     *
     * // Stop debugging all WordPress filters and action final and initial values.
     * $this->stopWpFiltersDebug();
     * ```
     *
     * @return void
     */
    public function stopWpFiltersDebug()
    {
        if (!function_exists('remove_filter')) {
            throw new \RuntimeException('Function "remove_filter" is not defined; is WordPress loaded?');
        }
        if (!function_exists('remove_action')) {
            throw new \RuntimeException('Function "remove_action" is not defined; is WordPress loaded?');
        }

        if (!defined('PHP_INT_MIN')) {
            // The `PHP_INT_MIN` constant is is defined on PHP 7.0, define it here if not defined.
            define('PHP_INT_MIN', ~PHP_INT_MAX);
        }

        remove_filter('all', [$this, 'debugWpFilterInitial'], PHP_INT_MIN);
        remove_action('all', [$this, 'debugWpActionInitial'], PHP_INT_MIN);
        remove_filter('all', [$this, 'debugWpFilterFinal'], PHP_INT_MAX);
        remove_action('all', [$this, 'debugWpActionFinal'], PHP_INT_MAX);
        $this->wpFiltersFormatCallback = null;
    }

    /**
     * Debugs a single WordPress filter initial call using Codeception debug functions.
     *
     * The output will show following the selected output verbosity (`--debug` and `-vvv` CLI options).
     *
     * @example
     * ```php
     * // Start debugging all WordPress filters initial value.
     * add_filter('all', [$this,'debugWpFilterInitial']);
     *
     * // Run some code firing filters and debug them.
     *
     * // Stop debugging all WordPress filters initial value.
     * remove_filter('all', [$this,'debugWpFilterInitial']);
     * ```
     *
     * @param mixed ...$args The filter call arguments.
     *
     * @return mixed The filter input value, unchanged.
     */
    public function debugWpFilterInitial(...$args)
    {
        $tag = array_shift($args);
        global $wp_actions;
        if (isset($wp_actions[$tag])) {
            return reset($args);
        }
        codecept_debug(sprintf('[Filter (initial): %s] %s', $tag, $this->formatWpFilterArgs($tag, $args)));

        return reset($args);
    }

    /**
     * Formats the filters and action arguments cutting them down.
     *
     * @param string       $tag  The filter tag.
     * @param array<mixed> $args The list of arguments.
     * @return string The formatted arguments output.
     */
    protected function formatWpFilterArgs($tag, $args)
    {
        if (is_callable($this->wpFiltersFormatCallback)) {
            return call_user_func($this->wpFiltersFormatCallback, $tag, ...$args);
        }

        $output = json_encode($args);

        if ($output === false) {
            return 'n/a';
        }

        if (strlen($output) > 120) {
            $output = substr($output, 0, 120) . '…';
        }

        return $output;
    }

    /**
     * Debugs a single WordPress filter final call using Codeception debug functions.
     *
     * The output will show following the selected output verbosity (`--debug` and `-vvv` CLI options).
     *
     * @example
     * ```php
     * // Start debugging all WordPress filters final value.
     * add_filter('all', [$this,'debugWpFilterFinal']);
     *
     * // Run some code firing filters and debug them.
     *
     * // Stop debugging all WordPress filters final value.
     * remove_filter('all', [$this,'debugWpFilterFinal']);
     * ```
     *
     * @param mixed ...$args The filter call arguments.
     *
     * @return mixed The filter input value, unchanged.
     */
    public function debugWpFilterFinal(...$args)
    {
        $tag = array_shift($args);
        global $wp_actions;
        if (isset($wp_actions[$tag])) {
            return reset($args);
        }
        codecept_debug(sprintf('[Filter (final): %s] %s', $tag, $this->formatWpFilterArgs($tag, $args)));

        return reset($args);
    }

    /**
     * Debugs a single WordPress action initial call using Codeception debug functions.
     *
     * The output will show following the selected output verbosity (`--debug` and `-vvv` CLI options).
     *
     * @example
     * ```php
     * // Start debugging all WordPress actions initial value.
     * add_action('all', [$this,'debugWpActionInitial']);
     *
     * // Run some code firing actions and debug them.
     *
     * // Stop debugging all WordPress actions initial value.
     * remove_action('all', [$this,'debugWpActionInitial']);
     * ```
     *
     * @param mixed ...$args The action call arguments.
     *
     * @return void
     */
    public function debugWpActionInitial(...$args)
    {
        $tag = array_shift($args);
        global $wp_actions;
        if (!isset($wp_actions[$tag])) {
            return;
        }
        codecept_debug(sprintf('[Action (initial): %s] %s', $tag, $this->formatWpFilterArgs($tag, $args)));
    }

    /**
     * Debugs a single WordPress action final call using Codeception debug functions.
     *
     * The output will show following the selected output verbosity (`--debug` and `-vvv` CLI options).
     *
     * @example
     * ```php
     * // Start debugging all WordPress actions final value.
     * add_action('all', [$this,'debugWpActionFinal']);
     *
     * // Run some code firing actions and debug them.
     *
     * // Stop debugging all WordPress actions final value.
     * remove_action('all', [$this,'debugWpActionFinal']);
     * ```
     *
     * @param mixed ...$args The action call arguments.
     *
     * @return void
     */
    public function debugWpActionFinal(...$args)
    {
        $tag = array_shift($args);
        global $wp_actions;
        if (!isset($wp_actions[$tag])) {
            return;
        }
        codecept_debug(sprintf('[Action (final): %s] %s', $tag, $this->formatWpFilterArgs($tag, $args)));
    }
}
