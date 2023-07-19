<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\ManagedProcess\PhpBuiltInServer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DockerComposeController extends ServiceExtension
{
    private const PID_FILENAME = 'docker-compose.running';

    /**
     * @throws ExtensionException
     */
    public function start(OutputInterface $output): void
    {
        $runningFile = codecept_output_dir(self::PID_FILENAME);

        if (is_file($runningFile)) {
            $output->writeln('Docker Compose stack already up.');
            return;
        }

        $config = $this->config;
        $composeFile = $this->getComposeFile($config);
        $envFile = $this->getEnvFile($config);

        if ($envFile) {
            $command = ['docker', 'compose', '-f', $composeFile, '--env-file', $envFile, 'up', '--wait'];
        } else {
            $command = ['docker', 'compose', '-f', $composeFile, 'up', '--wait'];
        }

        $output->write("Starting compose stack ...", false);
        $process = new Process($command);
        try {
            $process->mustRun(function () use ($output) {
                $output->write('.', false);
            });
        } catch (ProcessFailedException $e) {
            throw new ExtensionException(
                $this,
                'Failed to start Docker Compose services: ' . $e->getMessage()
            );
        }
        $output->write(' ok', true);

        if (file_put_contents($runningFile, $process->getPid()) === false) {
            throw new ExtensionException(
                $this,
                'Failed to write Docker Compose running file.'
            );
        }
    }

    /**
     * @throws ExtensionException
     */
    public function stop(OutputInterface $output): void
    {
        $config = $this->config;
        $composeFile = $this->getComposeFile($config);
        $envFile = $this->getEnvFile($config);

        if ($envFile) {
            $command = ['docker', 'compose', '-f', $composeFile, '--env-file', $envFile, 'down'];
        } else {
            $command = ['docker', 'compose', '-f', $composeFile, 'down'];
        }

        $process = new Process($command);
        try {
            $output->write("Stopping compose stack ...", false);
            $process->mustRun(function () use ($output) {
                $output->write('.', false);
            });
            $output->write(' ok', true);
        } catch (ProcessFailedException $e) {
            throw new ExtensionException(
                $this,
                'Failed to stop Docker Compose: ' . $e->getMessage()
            );
        }
        $runningFile = codecept_output_dir(self::PID_FILENAME);
        if (!(is_file($runningFile) && unlink($runningFile))) {
            throw new ExtensionException(
                $this,
                'Failed to remove Docker Compose running file.'
            );
        }
    }

    /**
     * @param array<string,mixed> $config
     *
     * @throws ExtensionException
     */
    protected function getComposeFile(array $config): string
    {
        if (!(
            isset($config['compose-file'])
            && is_string($config['compose-file'])
            && is_file($config['compose-file']))
        ) {
            throw new ExtensionException(
                $this,
                'The "compose-file" configuration option must be a valid file.'
            );
        }

        return $config['compose-file'];
    }

    /**
     * @param array<string,mixed> $config
     *
     * @throws ExtensionException
     */
    private function getEnvFile(array $config): ?string
    {
        if (!isset($config['env-file'])) {
            return null;
        }

        if (!(is_string($config['env-file']) && is_file($config['env-file']))) {
            throw new ExtensionException(
                $this,
                'The "env-file" configuration option must be a valid file.'
            );
        }

        return $config['env-file'];
    }

    public function getPrettyName(): string
    {
        return 'Docker Compose Stack';
    }
}
