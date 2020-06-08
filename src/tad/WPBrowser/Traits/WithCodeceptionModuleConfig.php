<?php
/**
 * Provides methods to interact and parse Codeception configuration.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

use function tad\WPBrowser\identifySuiteFromTrace;
use function tad\WPBrowser\vendorDir;

/**
 * Trait WithLateModuleConfig
 *
 * @since   TBD
 *
 * @package tad\WPBrowser\Traits
 */
trait WithCodeceptionModuleConfig
{

    /**
     * Reads a module configuration at run-time, outside of the usual Codeception flow.
     *
     * @return array<string,mixed> The module configuration, in the format used by Codeception.
     */
    protected static function _getModuleConfig($moduleInstance)
    {
        // Some Codeception functions will not be auto-loaded when this method runs in isolation, load them now.
        require_once vendorDir('codeception/codeception/autoload.php');

        $suite       = identifySuiteFromTrace();
        $suiteConfig = $moduleInstance->getSuiteConfig($suite);
        $module      = str_replace('Codeception\\Module\\', '', get_class($moduleInstance));
        if (! isset($suiteConfig['modules']['config'][ $module ])) {
            throw new \RuntimeException(
                "{$module} module configuration not found in '{$suite}' suite configuration."
            );
        }

        return $suiteConfig['modules']['config'][ $module ];
    }
}
