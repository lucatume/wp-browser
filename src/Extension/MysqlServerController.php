<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Extension;

use Codeception\Exception\ExtensionException;
use lucatume\WPBrowser\ManagedProcess\MysqlServer;
use lucatume\WPBrowser\Utils\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

class MysqlServerController extends ServiceExtension
{
    use PidBasedController;

    public const PID_FILE_NAME = 'mysql-server.pid';
    /**
     * @var \lucatume\WPBrowser\ManagedProcess\MysqlServer
     */
    private $mysqlServer;

    public function start(OutputInterface $output): void
    {
        $pidFile = $this->getPidFile();

        if (is_file($pidFile)) {
            $output->writeln('MySQL server already running.');

            return;
        }

        $port = $this->getPort();
        $database = $this->getDatabase();
        $user = $this->getUser();
        $password = $this->getPassword();
        $binary = $this->getBinary();
        $shareDir = $this->getShareDir($binary);

        $output->write("Starting MySQL server on port $port ...");
        try {
            $this->mysqlServer = new MysqlServer(
                codecept_output_dir('_mysql_server'),
                $port,
                $database,
                $user,
                $password,
                $binary,
                $shareDir
            );
            $this->mysqlServer->setOutput($output);
            $this->mysqlServer->start();
        } catch (\Exception $e) {
            throw new ExtensionException($this, "Error while starting MySQL server. {$e->getMessage()}", $e);
        }
        $output->write(' ok', true);
    }

    public function getPidFile(): string
    {
        return codecept_output_dir(self::PID_FILE_NAME);
    }

    private function getDatabase(): string
    {
        /** @var array{database?: string} $config */
        $config = $this->config;

        if (isset($config['database']) && !(is_string($config['database']) && !empty($config['database']))) {
            throw new ExtensionException(
                $this,
                'The "database" configuration option must be a string.'
            );
        }

        return $config['database'] ?? 'wordpress';
    }

    private function getUser(): string
    {
        /** @var array{user?: string} $config */
        $config = $this->config;

        if (isset($config['user']) && !(is_string($config['user']) && !empty($config['user']))) {
            throw new ExtensionException(
                $this,
                'The "user" configuration option must be a string.'
            );
        }

        return $config['user'] ?? 'wordpress';
    }

    private function getPassword(): string
    {
        /** @var array{password?: string} $config */
        $config = $this->config;

        if (isset($config['password']) && !is_string($config['password'])) {
            throw new ExtensionException(
                $this,
                'The "password" configuration option must be a string.'
            );
        }

        return $config['password'] ?? 'wordpress';
    }

    /**
     * @throws ExtensionException
     */
    public function getPort(): int
    {
        $config = $this->config;
        if (isset($config['port'])
            && !(
                is_numeric($config['port'])
                && (int)$config['port'] == $config['port']
                && $config['port'] > 0
            )) {
            throw new ExtensionException(
                $this,
                'The "port" configuration option must be an integer greater than 0.'
            );
        }

        /** @var array{port?: number} $config */
        return (int)($config['port'] ?? 8906);
    }

    public function stop(OutputInterface $output): void
    {
        $pidFile = $this->getPidFile();
        $mysqlServerPid = (int)file_get_contents($pidFile);

        if (!$mysqlServerPid) {
            $output->writeln('MySQL server not running.');
            return;
        }

        $output->write("Stopping MySQL server with PID $mysqlServerPid ...", false);
        $this->kill($mysqlServerPid);
        $this->removePidFile($pidFile);
        $output->write(' ok', true);
    }

    public function getPrettyName(): string
    {
        return 'MySQL Community Server';
    }

    /**
     * @return array{
     *     running: string,
     *     pidFile: string,
     *     port: int
     * }
     * @throws ExtensionException
     */
    public function getInfo(): array
    {
        $isRunning = is_file($this->getPidFile());

        $info = [
            'running' => $isRunning ? 'yes' : 'no',
            'pidFile' => Filesystem::relativePath(codecept_root_dir(), $this->getPidFile()),
            'host' => '127.0.0.1',
            'port' => $this->getPort(),
            'user' => $this->getUser(),
            'password' => $this->getPassword(),
            'root user' => 'root',
            'root password' => $this->getUser() === 'root' ? $this->getPassword() : ''
        ];

        if ($isRunning) {
            $info['mysql command'] = $this->getCliConnectionCommandline();
            $info['mysql root command'] = $this->getRootCliConnectionCommandline();
        }

        return $info;
    }

    private function getCliConnectionCommandline(): string
    {
        if ($this->getPassword() === '') {
            return "mysql -h 127.0.0.1 -P {$this->getPort()} -u {$this->getUser()}";
        }

        return "mysql -h 127.0.0.1 -P {$this->getPort()} -u {$this->getUser()} -p '{$this->getPassword()}'";
    }

    private function getRootCliConnectionCommandline(): string
    {
        $rootPassword = $this->getUser() === 'root' ? $this->getPassword() : '';
        if ($rootPassword === '') {
            return "mysql -h 127.0.0.1 -P {$this->getPort()} -u root";
        }

        return "mysql -h 127.0.0.1 -P {$this->getPort()} -u root -p '{$rootPassword}'";
    }

    private function getBinary(): ?string
    {
        $config = $this->config;
        if (isset($config['binary']) && !(is_string($config['binary']) && is_executable($config['binary']))) {
            throw new ExtensionException(
                $this,
                'The "binary" configuration option must be an executable file.'
            );
        }

        /** @var array{binary?: string} $config */
        return ($config['binary'] ?? null);
    }

    private function getShareDir(?string $binary): ?string
    {
        /** @var array{shareDir?: string} $config */
        $config = $this->config;
        if (isset($config['shareDir']) && !(is_string($config['shareDir']) && is_dir($config['shareDir']))) {
            throw new ExtensionException(
                $this,
                'The "shareDir" configuration option must be a directory.'
            );
        }

        $shareDir = $config['shareDir'] ?? null;

        if ($binary && $shareDir === null) {
            throw new ExtensionException(
                $this,
                'The "shareDir" configuration option must be set when using a custom binary.'
            );
        }

        return $shareDir;
    }
}
