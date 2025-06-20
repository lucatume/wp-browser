<?php

namespace lucatume\WPBrowser\ManagedProcess;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\MachineInformation;
use PDOException;
use PharData;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MysqlServer implements ManagedProcessInterface
{
    /**
     * @var int
     */
    private $port = self::PORT_DEFAULT;
    /**
     * @var string
     */
    private $database = 'wordpress';
    /**
     * @var string
     */
    private $user = 'wordpress';
    /**
     * @var string
     */
    private $password = 'wordpress';
    use ManagedProcessTrait;

    public const PID_FILE_NAME = 'mysql-server.pid';
    public const PORT_DEFAULT = 8906;
    public const ERR_OS_NOT_SUPPORTED = 1;
    public const ERR_ARCH_NOT_SUPPORTED = 2;
    public const ERR_WINDOWS_ARM64_NOT_SUPPORTED = 3;
    public const ERR_MYSQL_DIR_NOT_CREATED = 10;
    public const ERR_MYSQL_ARCHIVE_EXTRACTION_FAILED = 11;
    public const ERR_CUSTOM_BINARY_EXTRACTED_PATH = 12;
    public const ERR_CUSTOM_BINARY_SHARE_DIR_PATH = 13;
    public const ERR_MYSQL_ARCHIVE_DOWNLOAD_FAILED = 15;
    public const ERR_MYSQL_SERVER_START_FAILED = 16;
    public const ERR_MYSQL_DATA_DIR_NOT_CREATED = 17;
    public const ERR_MYSQL_SERVER_NEVER_BECAME_AVAILABLE = 18;
    /**
     * @var string
     */
    private $directory;
    /**
     * @var string|null
     */
    private $binary;
    /**
     * @var string
     */
    private $pidFile;
    /**
     * @var \lucatume\WPBrowser\Utils\MachineInformation
     */
    private $machineInformation;
    /**
     * @var bool
     */
    private $usingCustomBinary = false;
    /**
     * @var string|null
     */
    private $customShareDir;
    /**
     * @var string
     */
    private $prettyName = 'MySQL Server';
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface|null
     */
    private $output;
    /**
     * @var float
     */
    private $startWaitTime = 10;

    /**
     * @throws RuntimeException
     */
    public function __construct(?string $directory = null, int $port = self::PORT_DEFAULT, string $database = 'wordpress', string $user = 'wordpress', string $password = 'wordpress', ?string $binary = null, ?string $shareDir = null)
    {
        $this->port = $port;
        $this->database = $database;
        $this->user = $user;
        $this->password = $password;
        if ($binary) {
            $binary = FS::normalizePath($binary);
            $this->usingCustomBinary = true;
        }
        if ($binary !== null && !is_executable($binary)) {
            throw new RuntimeException(
                "MySQL Server binary $binary does not exist.",
                ManagedProcessInterface::ERR_BINARY_NOT_FOUND
            );
        }
        if ($this->usingCustomBinary) {
            if (!($shareDir && is_dir($shareDir))) {
                throw new RuntimeException(
                    "MySQL Server share directory $shareDir does not exist.",
                    self::ERR_CUSTOM_BINARY_SHARE_DIR_PATH
                );
            }

            $this->customShareDir = $shareDir;
        }
        $this->directory = $directory ?? (FS::cacheDir() . '/mysql-server');
        if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new RuntimeException(
                "Could not create directory for MySQL Server at $this->directory",
                self::ERR_MYSQL_DIR_NOT_CREATED
            );
        }
        $this->binary = $binary;
        $this->machineInformation = new MachineInformation();
        $this->pidFile = self::getPidFile();
    }

    public function setMachineInformation(MachineInformation $machineInformation): void
    {
        $this->machineInformation = $machineInformation;
    }

    public function setStartWaitTime(float $param): void
    {
        $this->startWaitTime = $param;
    }

    public function getDataDir(bool $normalize = false): string
    {
        $isWin = $this->machineInformation->isWindows();
        $dataDir = $this->directory . '/data';
        return $isWin && !$normalize ?
            str_replace('/', '\\', $dataDir)
            : $dataDir;
    }

    public function getPidFilePath(bool $normalize = false): string
    {
        $isWin = $this->machineInformation->isWindows();
        return $isWin && !$normalize ?
            str_replace('/', '\\', $this->pidFile)
            : $this->pidFile;
    }

    /**
     * @return array<string>
     */
    private function getInitializeCommand(bool $normalize = false): array
    {
        $dataDir = $this->getDataDir($normalize);
        return [
            $this->getBinary($normalize),
            '--no-defaults',
            '--initialize-insecure',
            '--innodb-flush-method=nosync',
            '--datadir=' . $dataDir,
            '--pid-file=' . $this->getPidFilePath($normalize)
        ];
    }

    public function initializeServer(): void
    {
        if (is_dir($this->getDataDir(true))) {
            return;
        }

        ($nullsafeVariable1 = $this->output) ? $nullsafeVariable1->writeln("Initializing MySQL Server ...", OutputInterface::VERBOSITY_DEBUG) : null;
        $process = new Process($this->getInitializeCommand());
        $process->mustRun();
        ($nullsafeVariable2 = $this->output) ? $nullsafeVariable2->writeln('MySQL Server initialized.', OutputInterface::VERBOSITY_DEBUG) : null;
    }

    public function getExtractedPath(bool $normalize = false): string
    {
        if ($this->usingCustomBinary) {
            throw new RuntimeException(
                "Extracted path not available when using a custom binary.",
                MysqlServer::ERR_CUSTOM_BINARY_EXTRACTED_PATH
            );
        }

        $mysqlServerArchivePath = $this->getArchivePath($normalize);
        $isWin = $this->machineInformation->isWindows();
        $normalizedMysqlServerArchivePath = $isWin && !$normalize ?
            str_replace('\\', '/', $mysqlServerArchivePath)
            : $mysqlServerArchivePath;
        switch ($this->machineInformation->getOperatingSystem()) {
            case MachineInformation::OS_DARWIN:
                $archiveExtension = '.tar.gz';
                break;
            case MachineInformation::OS_WINDOWS:
                $archiveExtension = '.zip';
                break;
            default:
                $archiveExtension = '.tar.xz';
                break;
        }
        $extractedPath = dirname($normalizedMysqlServerArchivePath) . '/' . basename(
            $normalizedMysqlServerArchivePath,
            $archiveExtension
        );

        return !$normalize && $this->machineInformation->isWindows() ?
            str_replace('/', '\\', $extractedPath)
            : $extractedPath;
    }

    public function getShareDir(bool $normalize = false): string
    {
        if ($this->customShareDir) {
            return $normalize ? FS::normalizePath($this->customShareDir) : $this->customShareDir;
        }

        $shareDir = $this->getExtractedPath(true) . '/share';
        return !$normalize && $this->machineInformation->isWindows() ?
            str_replace('/', '\\', $shareDir)
            : $shareDir;
    }

    public function getSocketPath(bool $normalize = false): string
    {
        $path = $this->directory . '/mysql.sock';
        return !$normalize && $this->machineInformation->isWindows() ?
            str_replace('/', '\\', $path)
            : $path;
    }

    /**
     * @return array<string>
     */
    private function getStartCommand(int $port, bool $normalize = false): array
    {
        return [
            $this->getBinaryPath($normalize),
            '--datadir=' . $this->getDataDir(),
            '--skip-mysqlx',
            '--default-time-zone=+00:00',
            '--innodb-flush-method=nosync',
            '--innodb-flush-log-at-trx-commit=0',
            '--innodb-doublewrite=0',
            '--bind-address=localhost',
            '--lc-messages-dir=' . $this->getShareDir($normalize),
            '--socket=' . $this->getSocketPath($normalize),
            '--log-error=' . $this->getErrorLogPath($normalize),
            '--port=' . $port,
            '--pid-file=' . $this->getPidFilePath($normalize)
        ];
    }

    private function startServer(int $port): Process
    {
        $this->initializeServer();
        $dataDir = $this->getDataDir(true);
        if (!is_dir($dataDir) && !(mkdir($dataDir, 0755, true) && is_dir($dataDir))) {
            throw new RuntimeException(
                "Could not create directory for MySQL Server data at $dataDir",
                self::ERR_MYSQL_DATA_DIR_NOT_CREATED
            );
        }
        $startCommand = $this->getStartCommand($port);
        $process = new Process($startCommand);
        $process->createNewConsole();
        try {
            $process->start();
            $startTime = microtime(true);
            $pdo = $this->getRootPDOOrNot();
            $sleepTime = $this->startWaitTime / 10;
            $sleepTimeInMicroseconds = min((int)($sleepTime * 1000000), 1000000);
            while (!$pdo && (microtime(true) - $startTime) < $this->startWaitTime) {
                usleep($sleepTimeInMicroseconds);
                $pdo = $this->getRootPDOOrNot();
            }
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Could not start MySQL Server at $this->directory\n" . $e->getMessage(),
                self::ERR_MYSQL_SERVER_START_FAILED,
                $e
            );
        }

        if ($pdo === null) {
            throw new RuntimeException(
                "MySQL Server was started but never became available.\n" . $process->getOutput() . "\n" .
                $process->getErrorOutput(),
                self::ERR_MYSQL_SERVER_NEVER_BECAME_AVAILABLE
            );
        }

        return $process;
    }

    private function getRootPDOOrNot(): ?\PDO
    {
        try {
            return $this->getRootPDO();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getRootPassword(): string
    {
        return $this->getUser() === 'root' ? $this->password : '';
    }

    /**
     * @throws PDOException
     */
    public function getRootPDO(): \PDO
    {
        try {
            return new \PDO(
                "mysql:host=127.0.0.1;port={$this->port}",
                'root',
                $this->getRootPassword(),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $e) {
            // Connection with the set password failed, the server might not have been initialized yet
            // and still use the default, insecure, empty root password.
            return new \PDO(
                "mysql:host=127.0.0.1;port={$this->port}",
                'root',
                '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
    }

    public function setDatabaseName(string $databaseName): void
    {
        $this->database = $databaseName;
    }

    public function setUserName(string $username): void
    {
        $this->user = $username;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    private function createDefaultData(): void
    {
        $pdo = $this->getRootPDO();
        $user = $this->getUser();
        $password = $this->getPassword();
        if ($user === 'root' && $password !== '') {
            $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY '{$this->getPassword()}'");
        }
        $databaseName = $this->getDatabase();
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$databaseName`");
        if ($user !== 'root') {
            $pdo->exec("CREATE USER IF NOT EXISTS '$user'@'%' IDENTIFIED BY '$password'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `$databaseName`.* TO '$user'@'%'");
        }
        $pdo->exec("FLUSH PRIVILEGES");
    }

    private function doStart(): void
    {
        $this->process = $this->startServer($this->port ?? self::PORT_DEFAULT);
        $this->createDefaultData();
    }

    public function getBinaryPath(bool $normalize = false): string
    {
        if ($this->binary !== null) {
            return !$normalize && $this->machineInformation->isWindows() ?
                str_replace('/', '\\', $this->binary)
                : $this->binary;
        }

        $isWin = $this->machineInformation->isWindows();
        $binaryPath = implode('/', [
            $this->getExtractedPath(true),
            'bin',
            ($isWin ? 'mysqld.exe' : 'mysqld')
        ]);

        return !$normalize && $isWin ?
            str_replace('/', '\\', $binaryPath)
            : $binaryPath;
    }

    public function getBinary(bool $normalize = false): string
    {
        if ($this->binary !== null) {
            return !$normalize && $this->machineInformation->isWindows() ?
                str_replace('/', '\\', $this->binary)
                : $this->binary;
        }

        $mysqlServerArchivePath = $this->getArchivePath(true);
        $mysqlServerBinaryPath = $this->getBinaryPath(true);

        if (is_file($mysqlServerBinaryPath)) {
            return !$normalize && $this->machineInformation->isWindows() ?
                str_replace('/', '\\', $mysqlServerBinaryPath)
                : $mysqlServerBinaryPath;
        }

        if (!is_file($mysqlServerArchivePath)) {
            $this->downloadMysqlServerArchive();
        }

        if (!is_file($mysqlServerBinaryPath)) {
            $this->extractMysqlServerArchive();
        }

        if (!is_file($mysqlServerBinaryPath)) {
            throw new RuntimeException(
                "Could not find MySQL Server binary at $mysqlServerBinaryPath",
                self::ERR_BINARY_NOT_FOUND
            );
        }

        if (!$normalize && $this->machineInformation->isWindows()) {
            $mysqlServerBinaryPath = str_replace('/', '\\', $mysqlServerBinaryPath);
        }

        return $mysqlServerBinaryPath;
    }

    public function getArchiveUrl(): string
    {
        $operatingSystem = $this->machineInformation->getOperatingSystem();
        if (!in_array($operatingSystem, [
            MachineInformation::OS_DARWIN,
            MachineInformation::OS_LINUX,
            MachineInformation::OS_WINDOWS
        ], true)) {
            throw new RuntimeException(
                "Unsupported OS for MySQL Server binary.",
                self::ERR_OS_NOT_SUPPORTED
            );
        };

        $architecture = $this->machineInformation->getArchitecture();
        if (!in_array($architecture, [MachineInformation::ARCH_X86_64, MachineInformation::ARCH_ARM64], true)) {
            throw new RuntimeException(
                "Unsupported architecture for MySQL Server binary.",
                self::ERR_ARCH_NOT_SUPPORTED
            );
        }

        if ($operatingSystem === MachineInformation::OS_WINDOWS && $architecture === MachineInformation::ARCH_ARM64) {
            throw new RuntimeException("Windows ARM64 is not (yet) supported by MySQL Server.\n" .
            "Use MySQL through the DockerComposeController extension.\n" .
            "See: https://wpbrowser.wptestkit.dev/extensions/DockerComposeController/\n" .
            "See: https://hub.docker.com/_/mysql", self::ERR_WINDOWS_ARM64_NOT_SUPPORTED);
        }

        if ($operatingSystem === MachineInformation::OS_DARWIN) {
            return $architecture === 'arm64' ?
                'https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-macos14-arm64.tar.gz'
                : 'https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-macos14-x86_64.tar.gz';
        }

        if ($operatingSystem === MachineInformation::OS_LINUX) {
            return $architecture === 'arm64' ?
                'https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-linux-glibc2.17-aarch64-minimal.tar.xz'
                : 'https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-linux-glibc2.17-x86_64-minimal.tar.xz';
        }

        return 'https://dev.mysql.com/get/Downloads/MySQL-8.4/mysql-8.4.3-winx64.zip';
    }

    public function getArchivePath(bool $normalize = false): string
    {
        $path = $this->directory . '/' . basename($this->getArchiveUrl());
        return $this->machineInformation->isWindows() && !$normalize ?
            str_replace('/', '\\', $path)
            : $path;
    }

    private function downloadMysqlServerArchive(): void
    {
        $archiveUrl = $this->getArchiveUrl();
        $archivePath = $this->getArchivePath(true);

        try {
            ($nullsafeVariable3 = $this->output) ? $nullsafeVariable3->writeln("Downloading MySQL Server archive from $archiveUrl ...", OutputInterface::VERBOSITY_DEBUG) : null;
            Download::fileFromUrl($archiveUrl, $archivePath);
            ($nullsafeVariable4 = $this->output) ? $nullsafeVariable4->writeln('Downloaded MySQL Server archive.', OutputInterface::VERBOSITY_DEBUG) : null;
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Could not download MySQL Server archive from $archiveUrl to $archivePath: " . $e->getMessage(),
                self::ERR_MYSQL_ARCHIVE_DOWNLOAD_FAILED,
                $e
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    private function extractArchiveWithPhar(string $archivePath, string $directory): void
    {
        $memoryLimit = ini_set('memory_limit', '1G');
        try {
            $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
            $extracted = (new PharData($archivePath, $flags, null, 0))->extractTo($directory, null, true);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Could not extract MySQL Server archive from $archivePath to "
                . $directory .
                "\n" . $e->getMessage(),
                self::ERR_MYSQL_ARCHIVE_EXTRACTION_FAILED
            );
        } finally {
            ini_set('memory_limit', (string)$memoryLimit);
        }
    }

    /**
     * @throws ProcessFailedException
     */
    private function extractArchiveWithTarCommand(string $archivePath, string $directory): void
    {
        $extension = pathinfo($archivePath, PATHINFO_EXTENSION);
        $flags = $extension === 'xz' ? '-xf' : '-xzf';
        $process = new Process(['tar', $flags, $archivePath, '-C', $directory]);
        $process->mustRun();
    }

    private function extractMysqlServerArchive(): void
    {
        $mysqlServerArchivePath = $this->getArchivePath(true);

        ($nullsafeVariable5 = $this->output) ? $nullsafeVariable5->writeln("Extracting MySQL Server archive from $mysqlServerArchivePath ...", OutputInterface::VERBOSITY_DEBUG) : null;
        $directory = $this->directory;
        try {
            if ($this->machineInformation->isWindows()) {
                $this->extractArchiveWithPhar($mysqlServerArchivePath, $directory);
            } else {
                $this->extractArchiveWithTarCommand($mysqlServerArchivePath, $directory);
            }
        } catch (\Throwable $e) {
            throw new RuntimeException(
                "Could not extract MySQL Server archive from $mysqlServerArchivePath to "
                . $directory .
                "\n" . $e->getMessage(),
                self::ERR_MYSQL_ARCHIVE_EXTRACTION_FAILED
            );
        }
        ($nullsafeVariable6 = $this->output) ? $nullsafeVariable6->writeln('Extracted MySQL Server archive.', OutputInterface::VERBOSITY_DEBUG) : null;
    }

    public function isUsingCustomBinary(): bool
    {
        return $this->usingCustomBinary;
    }

    public function setOutput(?OutputInterface $output = null): void
    {
        $this->output = $output;
    }

    public function getDirectory(bool $normalize = false): string
    {
        return !$normalize && $this->machineInformation->isWindows() ?
            str_replace('/', '\\', $this->directory)
            : $this->directory;
    }

    public function getErrorLogPath(bool $normalize = false): string
    {
        $path = $this->getDataDir(false) . '/error.log';
        return !$normalize && $this->machineInformation->isWindows() ?
            str_replace('/', '\\', $path)
            : $path;
    }
}
