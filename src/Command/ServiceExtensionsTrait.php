<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
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
        if (!is_a($serviceExtension, ServiceExtension::class, true)) {
            $message = "The class {$serviceExtension} does not implement the "
                . ServiceExtension::class . " interface.";
            throw new InvalidArgumentException($message);
        }
        $config = $this->getServiceExtensionConfig($serviceExtension);
        return new $serviceExtension($config, []);
    }

    /**
     * @return array<class-string>
     * @throws ConfigurationException
     */
    protected function getServiceExtensions(): array
    {
        $config = Configuration::config();

        if (!(isset($config['extensions']) && is_array($config['extensions']))) {
            return [];
        }

        $extensions = $config['extensions'];

        if (!(isset($extensions['enabled']) && is_array($extensions['enabled']))) {
            return [];
        }

        $enabledExtensions = $config['extensions']['enabled'];

        if (!is_array($enabledExtensions)) {
            return [];
        }

        return array_filter(
            $enabledExtensions,
            static fn($extension) => is_string($extension) && is_a($extension, ServiceExtension::class, true),
        );
    }

    /**
     * @return array<string,mixed>
     * @throws ConfigurationException
     */
    protected function getServiceExtensionConfig(string $serviceExtension): array
    {
        $codeceptionConfig = Configuration::config();

        if (!(isset($codeceptionConfig['extensions']) && is_array($codeceptionConfig['extensions']))) {
            return [];
        }

        if (!(
            isset($codeceptionConfig['extensions']['config'])
            && is_array($codeceptionConfig['extensions']['config']))
        ) {
            return [];
        }

        if (!(
            isset($codeceptionConfig['extensions']['config'][$serviceExtension])
            && is_array($codeceptionConfig['extensions']['config'][$serviceExtension]))
        ) {
            return [];
        }

        $extensionConfig = $codeceptionConfig['extensions']['config'][$serviceExtension];

        if (!is_array($extensionConfig)) {
            return [];
        }

        return $extensionConfig;
    }
}
