<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\Extension\ServiceExtensionInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DevInfo extends Command implements CustomCommandInterface
{
    use ServiceExtensionsTrait;

    public static function getCommandName(): string
    {
        return 'dev:info';
    }

    public function getDescription(): string
    {
        return 'Displays information about the testing environment services.';
    }

    /**
     * @throws ConfigurationException
     * @throws ExtensionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serviceExtensions = $this->getServiceExtensions();

        if (count($serviceExtensions) === 0) {
            $output->writeln('No services to start.');
            return 0;
        }

        $info = [];
        foreach ($serviceExtensions as $extensionClass) {
            $extension = $this->buildServiceExtension($extensionClass);
            $info[$extension->getPrettyName()] = $extension->getInfo();
        }

        $output->writeln(Yaml::dump($info, 8));

        return 0;
    }
}
