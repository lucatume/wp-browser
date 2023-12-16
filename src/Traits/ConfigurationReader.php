<?php

namespace lucatume\WPBrowser\Traits;

trait ConfigurationReader
{
    /**
     * @param array<int|string,string|array<string,array<string,mixed>>>|array<string,mixed> $config
     * @param string[] $modules
     *
     * @return array<string,array<string,mixed>>
     */
    protected function getConfigsForModules(array $config, array $modules): array
    {
        /** @noinspection IssetConstructsCanBeMergedInspection */
        /** @noinspection PhpIssetCanCheckNestedAccessDirectlyInspection */
        $enabledModules = isset($config['modules'])
        && is_array($config['modules'])
        && isset($config['modules']['enabled'])
        && is_array($config['modules']['enabled']) ?
            $config['modules']['enabled']
            : [];
        $enabledModuleConfigs = array_reduce(
            $enabledModules,
            static function (array $carry, $module) use ($modules): array {
                if (is_array($module)) {
                    $firstKey = array_key_first($module);
                    if (in_array($firstKey, $modules, true)) {
                        $carry[$firstKey] = $module[$firstKey];
                    }
                }
                return $carry;
            },
            []
        );

        /** @noinspection IssetConstructsCanBeMergedInspection */
        /** @noinspection PhpIssetCanCheckNestedAccessDirectlyInspection */
        $configModules = isset($config['modules'])
        && is_array($config['modules'])
        && isset($config['modules']['config'])
        && is_array($config['modules']['config']) ?
            $config['modules']['config']
            : [];
        $configModuleConfigs = array_filter(
            $configModules,
            static function ($value, $key) use ($modules): bool {
                return in_array($key, $modules, true);
            },
            ARRAY_FILTER_USE_BOTH
        );

        return array_merge($enabledModuleConfigs, $configModuleConfigs);
    }
}
