<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Extension\ServiceExtension;

trait ServiceExtensionsTrait
{

    /**
     * @param class-string $serviceExtension
     *
     * @return ServiceExtension
     * @throws ConfigurationException
     */
    protected function buildServiceExtension(string $serviceExtension): ServiceExtension
    {
        $config = Configuration::config()['extensions']['config'][$serviceExtension] ?? [];
        return new $serviceExtension($config, []);
    }

    /**
     * @return array<class-string>
     * @throws ConfigurationException
     */
    protected function getServiceExtensions(): array
    {
        $config = Configuration::config();
        $enabledExtensions = $config['extensions']['enabled'] ?? [];
        return array_filter($enabledExtensions,
            static fn(string $extension) => is_a($extension, ServiceExtension::class, true),
        );
    }
}
