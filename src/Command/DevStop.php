<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ExtensionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevStop extends Command implements CustomCommandInterface
{
    use ServiceExtensionsTrait;

    public static function getCommandName(): string
    {
        return 'dev:stop';
    }

    public function getDescription(): string
    {
        return 'Stops the testing environment services.';
    }

    /**
     * @throws ConfigurationException
     * @throws ExtensionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $serviceExtensions = $this->getServiceExtensions();

        if (count($serviceExtensions) === 0) {
            $output->writeln('No services to stop.');
            return 0;
        }

        array_map(
            function (string $extensionClass) use ($output) {
                $extension = $this->buildServiceExtension($extensionClass);
                $extension->stop($output);
            },
            $serviceExtensions
        );

        return 0;
    }
}
