<?php

namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DockerCompose
{
    /**
     * @var string
     */
    private $composeFile;
    public function __construct(string $composeFile)
    {
        $this->composeFile = $composeFile;
        if (!is_file($this->composeFile)) {
            throw new InvalidArgumentException("Docker Compose file {$this->composeFile} does not exist.");
        }
    }

    public function up(string $service): void
    {
        $this->mustRun(['up', '-d', $service]);
    }

    /**
     * @param array<string> $command
     *
     * @throws ProcessFailedException
     */
    private function mustRun(array $command): void
    {
        $process = new Process(array_merge(['docker', 'compose', '-f', $this->composeFile], $command));
        $process->mustRun();
    }

    public function waitForHealthy(string $service, int $timeout = 10): void
    {
        $dockerServiceId = $this->getDockerServiceId($service);

        while ($timeout > 0) {
            $process = new Process(['docker', 'inspect', '--format', '{{json .State.Health}}', $dockerServiceId]);
            $process->run();
            if ($process->isSuccessful() && strpos($process->getOutput(), '"Status":"healthy"') !== false) {
                // Sleep an additional 250ms to allow the service to become fully healthy.
                usleep(250000);
                return;
            }
            sleep(1);
            $timeout--;
        }

        throw new RuntimeException("Service {$service} did not become healthy in time.");
    }

    private function getDockerServiceId(string $service): string
    {
        $process = new Process(['docker', 'compose', '-f', $this->composeFile, 'ps', '-q', $service]);
        $process->mustRun();
        return trim($process->getOutput());
    }

    public function down(): void
    {
        $this->mustRun(['down']);
    }
}
