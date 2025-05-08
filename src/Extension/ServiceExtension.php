<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use lucatume\WPBrowser\Utils\Arr;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ServiceExtension extends Extension
{
    /**
     * @var array<string,array{0: string, 1: int}>
     */
    public static array $events = [
        Events::MODULE_INIT => ['onModuleInit', 0],
    ];

    /**
     * @throws ExtensionException
     */
    public function onModuleInit(SuiteEvent $event): void
    {
        $config = $this->config;

        if (isset($config['suites']) && !(
                is_array($config['suites']) && Arr::containsOnly($config['suites'], 'string'))
        ) {
            throw new ExtensionException($this, 'The "suites" configuration option must be an array.');
        }
        /** @var string[] $suites */
        $suites = $config['suites'] ?? [];

        $suiteName = $event->getSuite()?->getName();
        $start = !isset($this->config['suites']) || in_array($suiteName, $suites, true);

        if (!$start) {
            return;
        }

        $this->start($this->output);
    }

    /**
     * @throws ExtensionException
     */
    abstract public function start(OutputInterface $output): void;

    /**
     * @throws ExtensionException
     */
    abstract public function stop(OutputInterface $output): void;

    abstract public function getPrettyName(): string;

    /**
     * @return array<string,mixed>
     */
    abstract public function getInfo(): array;
}
