<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class DockerComposeController extends ServiceExtension
{
    private const PID_FILENAME = 'docker-compose.running';

    /**
     * @throws ExtensionException
     */
    public function start(OutputInterface $output): void
    {
        $runningFile = self::getRunningFile();

        if (is_file($runningFile)) {
            $output->writeln('Docker Compose stack already up.');
            return;
        }

        $config = $this->config;
        $command = $this->getCommand($config);

        $output->write("Starting compose stack ...", false);
        $process = new Process([...$command, 'up', '--wait']);
        try {
            $process->mustRun(function () use ($output) {
                $output->write('.', false);
            });
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            throw new ExtensionException(
                $this,
                'Failed to stop Docker Compose: ' . $e->getMessage()
            );
        }
        $runningFile = self::getRunningFile();
        if (is_file($runningFile) && !unlink($runningFile)) {
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

    /**
     * @return array{
     *     status: string,
     *     config: string|mixed[]
     * }
     * @throws ExtensionException
     */
    public function getInfo(): array
    {
        $runningFile = self::getRunningFile();
        if (is_file($runningFile)) {
            // Run the docker compose config command and return the output.
            $process = new Process([
                ...$this->getCommand($this->config),
                'config'
            ]);
            $dockerComposeConfig = $process->mustRun()->getOutput();

            return [
                'status' => 'up',
                'config' => Yaml::parse($dockerComposeConfig)
            ];
        }

        return [
            'status' => 'down',
            'config' => '',
        ];
    }

    public static function getRunningFile(): string
    {
        return codecept_output_dir(self::PID_FILENAME);
    }

    /**
     * @param array<string,mixed> $config
     *
     * @return string[]
     * @throws ExtensionException
     */
    protected function getCommand(array $config): array
    {
        $composeFile = $this->getComposeFile($config);
        $envFile = $this->getEnvFile($config);

        if ($envFile) {
            $command = ['docker', 'compose', '-f', $composeFile, '--env-file', $envFile];
        } else {
            $command = ['docker', 'compose', '-f', $composeFile];
        }
        return $command;
    }
}
