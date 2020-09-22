<?php
/**
 * Functions related to wp-browser inner workings.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

use Codeception\Lib\ModuleContainer;

/**
 * Returns the wp-browser package root directory.
 *
 * @param string $path An optional path to append to the root directory absolute path.
 *
 * @return string The absolute path to the package root directory or to a path from it.
 */
function rootDir($path = '')
{
    $root = dirname(dirname(dirname(__DIR__)));

    return $path ? $root . DIRECTORY_SEPARATOR . ltrim($path, '\\/') : $root;
}

/**
 * Gets the absolute path to the `vendorDir` dir optionally appending a path.
 *
 * @param string $path An optional, relative path to append to the vendorDir directory path.
 *
 * @return string The absolute path to the file.
 */
function vendorDir($path = '')
{
    static $vendorDir;

    if (! $vendorDir) {
        $root = rootDir();
        if (file_exists($root . '/vendor')) {
            // We're in the wp-browser package itself context.
            $vendorDir = $root . '/vendor';
        } else {
            $vendorDir = dirname(dirname($root));
        }
    }

    return empty($path) ?
        $vendorDir
        : $vendorDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
}

/**
 * Returns the absolute path to the package `includes` directory.
 *
 * @param string $path An optional path to append to the includes directory absolute path.
 *
 * @return string The absolute path to the package `includes` directory.
 */
function includesDir($path = '')
{
    $includesDir = rootDir('/src/includes');

    return empty($path) ?
        $includesDir
        : $includesDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
}

/**
 * Checks the requirements of a wp-browser module to make sure the required Codeception modules are present.
 *
 * @param string        $module          The name of the module name this check is for, this will be used to build the
 *                                       error message if the module requiremnents are not satisfied.
 * @param array<string> $requiredModules The list of Codeception modules, or components, required by the wp-browser
 *                                       module
 *
 * @return void
 *
 * @throws \Codeception\Exception\ConfigurationException If one or more requirements for a wp-browser module are not
 *                                                       satisfied.
 */
function requireCodeceptionModules($module, array $requiredModules = [])
{
    if (! property_exists(ModuleContainer::class, 'packages')) {
        return;
    }

    $additionalPackages = [
        '\\Codeception\\Lib\\Framework' => 'codeception/lib-innerbrowser'
    ];

    if (property_exists(ModuleContainer::class, 'packages')) {
        $packages = array_merge(ModuleContainer::$packages, $additionalPackages);
    } else {
        $packages = $additionalPackages;
    }

    $missing  = [];

    foreach ($requiredModules as $moduleName) {
        if (! (class_exists(ModuleContainer::MODULE_NAMESPACE . $moduleName) || class_exists($moduleName))) {
            $modulePackage          = isset($packages[ $moduleName ]) ? $packages[ $moduleName ] : 'unknown package';
            $missing[ $moduleName ] = $modulePackage;
            continue;
        }
    }

    if (!count($missing)) {
        return;
    }
        $missingModulesNames  = array_keys($missing);
        $missingModulesString = andList($missingModulesNames);

        $message = sprintf(
            'The %1$s module requires the %2$s Codeception module%3$s or component%3$s.' . PHP_EOL .
            'Use Composer to install the corresponding package%3$s:' . PHP_EOL .
            '"composer require %4$s --dev"',
            $module,
            $missingModulesString,
            count($missing) > 1 ? 's' : '',
            implode(' ', $missing)
        );

        throw new \Codeception\Exception\ConfigurationException($message);
}

/**
 * Identifies the current running suite provided a debug backtrace.
 *
 * @return string The suite name
 *
 * @throws \RuntimeException If the suite cannot be identified from the debug backtrace.
 */
function identifySuiteFromTrace()
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
