<?php
/**
 * Functions related to wp-browser inner workings.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser;

/**
 * Identifies the current running suite provided a debug backtrace.
 *
 * @return string The suite name
 *
 * @throws \RuntimeException If the suite cannot be identified from the debug backtrace.
 */
function identifySuiteFromTrace(): string
{
    $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    foreach (array_reverse($debugBacktrace) as $traceEntry) {
        if (! ( isset($traceEntry['file']) && file_exists($traceEntry['file']) )) {
            continue;
        }
        $pathFrags = array_filter(explode('/', $traceEntry['file']));
        $path      = '';
        do {
            $suite = array_shift($pathFrags);

            if (!is_string($suite)) {
                throw new \RuntimeException('Suite cannot be identified from the debug backtrace.');
            }

            $path  .= '/' . $suite;

            if (file_exists("{$path}.suite.dist.yml") || file_exists("{$path}.suite.yml")) {
                return $suite;
            }
        } while (count($pathFrags));
    }

    throw new \RuntimeException('Suite cannot be identified from the debug backtrace.');
}
