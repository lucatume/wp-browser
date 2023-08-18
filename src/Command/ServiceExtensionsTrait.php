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

        $serviceExtensions = [];
        foreach ($enabledExtensions as $key => $value) {
            if (is_string($key) && is_a($key, ServiceExtension::class, true)) {
                $serviceExtensions[] = $key;
                continue;
            }

            if (is_array($value)) {
                // Configured inline.
                $extensionClass = array_key_first($value);

                if (!is_string($extensionClass)) {
                    continue;
                }

                if (!is_a($extensionClass, ServiceExtension::class, true)) {
                    continue;
                }

                $serviceExtensions[] = $extensionClass;
                continue;
            }

            if (is_string($value) && is_a($value, ServiceExtension::class, true)) {
                $serviceExtensions[] = $value;
            }
        }

        return $serviceExtensions;
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

        $extensions = $codeceptionConfig['extensions'];

        // Look up the `enabled` section.
        if (isset($extensions['enabled']) && is_array($extensions['enabled'])) {
            foreach ($extensions['enabled'] as $key => $value) {
                if (
                    $key === $serviceExtension
                    && is_a($key, ServiceExtension::class, true)
                    && isset($value[0]) && is_array($value[0])
                ) {
                    // Configured inline.
                    return $value[0];
                }

                if (is_array($value)) {
                    $extensionClass = array_key_first($value);

                    if ($extensionClass !== $serviceExtension) {
                        continue;
                    }

                    if ($value[$extensionClass] === null) {
                        continue;
                    }

                    return $value[$extensionClass];
                }
            }
        }

        // Lookup the `config` section.
        if (isset($extensions['config'])
            && is_array($extensions['config'])
            && isset($extensions['config'][$serviceExtension])
            && is_array($extensions['config'][$serviceExtension])
        ) {
            return $extensions['config'][$serviceExtension];
        }

        return [];
    }
}
