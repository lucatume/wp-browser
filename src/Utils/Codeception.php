<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use Codeception\Codecept;
use Codeception\Exception\ConfigurationException;
use Codeception\Lib\ModuleContainer;
use Codeception\Lib\Framework;

class Codeception
{
    private static ?string $runningSuite = null;

    /**
     * @throws ConfigurationException If the required modules are missing.
     */
    public static function checkModuleRequirements(string $module, array $requiredModules): void
    {
        $packages = array_merge(ModuleContainer::$packages, [
            Framework::class => 'codeception/lib-innerbrowser'
        ]);

        $ns = ModuleContainer::MODULE_NAMESPACE;
        $missing = array_filter($requiredModules,
            static fn(string $required): bool => !(class_exists($ns . $required) || class_exists($required)));

        if (!count($missing)) {
            return;
        }

        $missingModulePackages = array_map(static fn(string $moduleName) => $packages[$moduleName] ?? $moduleName,
            $missing);

        $message = sprintf(
            'The %1$s module requires the %2$s Codeception module%3$s or component%3$s.' . PHP_EOL .
            'Use Composer to install the corresponding package%3$s:' . PHP_EOL .
            '"composer require %4$s --dev"',
            $module,
            Strings::andList($missing),
            count($missing) > 1 ? 's' : '',
            implode(' ', $missingModulePackages)
        );

        throw new ConfigurationException($message);
    }

    public static function identifySuite(): string
    {
        if (self::$runningSuite === null) {
            $reverseBacktraceHead = array_reverse(
                array_slice(
                    array_reverse(
                        debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)
                    ), 0, 20)
            );

            foreach ($reverseBacktraceHead as $backtraceEntry) {
                $object = $backtraceEntry['object'] ?? null;
                if ($object instanceof \Codeception\Suite) {
                    self::$runningSuite = Property::readPrivate($object, 'name');
                    break;
                }
            }
        }

        return self::$runningSuite;
    }

    public static function bin(): string
    {
        return Composer::binDir('/codecept');
    }
}
