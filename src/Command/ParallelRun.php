<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\Run;
use Codeception\Configuration;
use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Command\ParallelRun\DotAggregator;
use lucatume\WPBrowser\Command\ParallelRun\PortAllocator;
use lucatume\WPBrowser\Command\ParallelRun\ShardPlanner;
use lucatume\WPBrowser\Command\ParallelRun\WorkerEnv;
use lucatume\WPBrowser\Command\ParallelRun\WorkerResourceEnv;
use lucatume\WPBrowser\ManagedProcess\MysqlServer;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use PDO;
use PDOException;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @internal
 */
class ParallelRun extends Run implements CustomCommandInterface
{
    private const MYSQL_READY_TIMEOUT_SECONDS = 30.0;

    public static function getCommandName(): string
    {
        return 'parallel-run';
    }

    public function getDescription(): string
    {
        return 'Runs a single suite split across N worker subprocesses with aggregated dot output.';
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption(
            'workers',
            null,
            InputOption::VALUE_REQUIRED,
            'Number of parallel worker processes (integer, or "auto").',
            '1'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $workersOpt = $input->getOption('workers');
        $workers    = $this->resolveWorkers(is_scalar($workersOpt) ? (string)$workersOpt : '1');
        $suiteArg   = $input->getArgument('suite');
        $suite      = is_string($suiteArg) ? $suiteArg : '';

        if ($suite === '') {
            $output->writeln('<error>parallel-run requires a suite argument.</error>');
            return 1;
        }
        if ($workers <= 1) {
            return parent::execute($input, $output);
        }
        $testArg = $input->getArgument('test');
        $testPath = is_string($testArg) ? $testArg : '';

        global $argv;
        $codeceptBin = $argv[0] ?? 'vendor/bin/codecept';
        $cwd         = getcwd() ?: null;
        $passThrough = $this->buildPassThroughOptions($input);
        $reportDir   = sys_get_temp_dir() . '/wpbrowser-parallel-' . getmypid();
        if (!is_dir($reportDir) && !mkdir($reportDir, 0777, true) && !is_dir($reportDir)) {
            $output->writeln("<error>Could not create {$reportDir}</error>");
            return 1;
        }

        $aggregator = new DotAggregator($output);
        $output->writeln(sprintf('<info>Running suite "%s" across %d workers</info>', $suite, $workers));

        [$mode, $shardAssignments] = $this->resolveShardPlan($suite, $testPath, $workers, $cwd ?? '.');
        $output->writeln(sprintf('<info>Sharding mode: %s</info>', $mode));
        if ($mode !== ShardPlanner::MODE_SHARD && $shardAssignments !== []) {
            foreach ($shardAssignments as $i => $shard) {
                $output->writeln(sprintf(
                    '  shard %d: %d files, weight %.2f',
                    $i,
                    count($shard['files']),
                    $shard['weight']
                ));
            }
        }
        $output->writeln('');

        $workerPorts = $this->allocateWorkerPorts($workers);

        $this->setupWorkers($workers, $cwd ?? '.', $output);

        $start = microtime(true);

        $failed = false;
        $eventFiles = [];
        $eventOffsets = [];
        $logFiles = [];

        $resourceEnvs = WorkerResourceEnv::build($shardAssignments);

        try {
            /** @var array<int,Process> $running */
            $running = [];
            for ($i = 1; $i <= $workers; $i++) {
                if ($mode !== ShardPlanner::MODE_SHARD && ($shardAssignments[$i]['files'] ?? []) === []) {
                    continue;
                }
                $xmlPath   = sprintf('%s/shard-%d.xml', $reportDir, $i);
                $eventFile = sprintf('%s/events-%d.bin', $reportDir, $i);
                $logFile   = sprintf('%s/log-%d.txt', $reportDir, $i);
                touch($eventFile);
                touch($logFile);
                $eventFiles[$i]   = $eventFile;
                $eventOffsets[$i] = 0;
                $logFiles[$i]     = $logFile;
                $ports = $workerPorts[$i];
                $env   = WorkerEnv::build($i - 1, $_ENV, getcwd() . '/tests/.env', $ports);
                $env['WPBROWSER_PARALLEL_EVENT_FILE'] = $eventFile;
                $env['WPBROWSER_PARALLEL_LOG_FILE']   = $logFile;
                $env += $resourceEnvs[$i] ?? [
                    WorkerResourceEnv::ENV_NEEDS_SERVER       => '1',
                    WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER => '1',
                ];

                if ($mode === ShardPlanner::MODE_SHARD) {
                    $shardArgs = ['--shard', "$i/$workers"];
                    $suiteArgs = $testPath !== '' ? [$suite, $testPath] : [$suite];
                } else {
                    $groupName = 'wpb_parallel_' . $i;
                    $groupFile = sprintf('%s/shard-%d.group.txt', $reportDir, $i);
                    file_put_contents($groupFile, implode("\n", $shardAssignments[$i]['files']) . "\n");
                    $shardArgs = [
                    '--override', 'groups: {' . $groupName . ': ' . $groupFile . '}',
                    '--group', $groupName,
                    ];
                    $suiteArgs = [$suite];
                }

                $cmd = array_merge(
                    [$codeceptBin, 'codeception:run'],
                    $suiteArgs,
                    $shardArgs,
                    ['--ext', 'lucatume\\WPBrowser\\Extension\\ParallelWorkerReporter'],
                    ['--xml', $xmlPath],
                    ['--no-colors'],
                    ['--no-artifacts'],
                    ['--override', 'paths: output: var/_output/w' . ($i - 1)],
                    WorkerEnv::overridesForWorker($i - 1, $ports),
                    $passThrough
                );
                $p = new Process($cmd, $cwd, $env, null, null);
                $p->setTimeout(null);
                $p->start(static function (string $type, string $data) use ($aggregator, $i): void {
                    if ($type !== Process::OUT) {
                        $aggregator->ingest($i, 'err', $data);
                    }
                });
                $running[$i] = $p;
            }

            $remaining = $running;
            while ($remaining !== []) {
                foreach ($remaining as $i => $p) {
                    $this->drainEvents($eventFiles[$i], $eventOffsets, $i, $aggregator);
                    if (!$p->isRunning()) {
                        if (!$p->isSuccessful()) {
                            $failed = true;
                            $aggregator->recordCrash($i, $p->getExitCode() ?? -1);
                        }
                        unset($remaining[$i]);
                    }
                }
                if ($remaining !== []) {
                    usleep(50000);
                }
            }
            foreach ($eventFiles as $i => $file) {
                $this->drainEvents($file, $eventOffsets, $i, $aggregator);
                $aggregator->mergeXml(sprintf('%s/shard-%d.xml', $reportDir, $i));
            }

            foreach ($logFiles as $i => $logFile) {
                $content = trim((string)file_get_contents($logFile));
                if ($content !== '') {
                    $output->writeln('');
                    $output->writeln(sprintf('<comment>--- Worker %d ---</comment>', $i));
                    $output->writeln($content);
                }
            }

            $aggregator->flushSummary(microtime(true) - $start);

            $this->cleanupReportDir($reportDir);
        } finally {
            $this->teardownWorkers($workers, $cwd ?? '.', $output);
        }

        return ($failed || $aggregator->hasFailures()) ? 1 : 0;
    }

    /**
     * @return array<int, array<string,int>>
     */
    private function allocateWorkerPorts(int $workers): array
    {
        $preferred = [
            'WORDPRESS_LOCALHOST_PORT' => 2389,
            'CHROMEDRIVER_PORT'        => 2390,
        ];
        $reserved = [];
        $out = [];
        foreach ($preferred as $key => $base) {
            $ports = PortAllocator::allocate($workers, $base, $reserved);
            foreach ($ports as $idx => $port) {
                $workerIdx = $idx + 1;
                $out[$workerIdx][$key] = $port;
                $reserved[$port] = true;
            }
        }
        return $out;
    }

    private function resolveWorkers(string $raw): int
    {
        if ($raw === 'auto') {
            $nproc = (int)shell_exec('getconf _NPROCESSORS_ONLN 2>/dev/null') ?: 4;
            return max(1, min($nproc - 1, 8));
        }
        $n = (int)$raw;
        return $n < 1 ? 1 : $n;
    }

    /**
     * @return array<int,string>
     */
    private function buildPassThroughOptions(InputInterface $input): array
    {
        $suppress = ['workers', 'shard', 'xml', 'ext', 'no-colors', 'no-artifacts', 'override', 'group'];
        $out = [];
        foreach ($this->getDefinition()->getOptions() as $opt) {
            $name = $opt->getName();
            if (in_array($name, $suppress, true)) {
                continue;
            }
            $value = $input->getOption($name);
            if ($value === false || $value === null || $value === []) {
                continue;
            }
            if (is_bool($value)) {
                $out[] = "--{$name}";
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $v) {
                    if (!is_scalar($v)) {
                        continue;
                    }
                    $out[] = "--{$name}";
                    $out[] = (string)$v;
                }
                continue;
            }
            if (!is_scalar($value)) {
                continue;
            }
            $out[] = "--{$name}";
            $out[] = (string)$value;
        }
        return $out;
    }

    /**
     * @param array<int,int> $offsets
     */
    private function drainEvents(string $file, array &$offsets, int $worker, DotAggregator $aggregator): void
    {
        clearstatcache(true, $file);
        $size = @filesize($file);
        if ($size === false || $size <= $offsets[$worker]) {
            return;
        }
        $fh = @fopen($file, 'rb');
        if ($fh === false) {
            return;
        }
        fseek($fh, $offsets[$worker]);
        $chunk = (string)stream_get_contents($fh);
        fclose($fh);
        $offsets[$worker] += strlen($chunk);
        if ($chunk !== '') {
            $aggregator->ingest($worker, 'out', $chunk);
        }
    }

    /**
     * @return array{0: string, 1: array<int, array{files: string[], weight: float}>}
     */
    private function resolveShardPlan(string $suite, string $testPath, int $workers, string $cwd): array
    {
        if ($testPath !== '') {
            return [ShardPlanner::MODE_SHARD, []];
        }

        $planner = new ShardPlanner();
        $weights = $planner->fromReport($cwd . '/var/_output/report.xml');
        $mode    = ShardPlanner::MODE_SHARD;

        if ($weights !== []) {
            $mode = ShardPlanner::MODE_TIMINGS;
        } else {
            $suiteRoot = $this->resolveSuiteTestsRoot($suite);
            if ($suiteRoot !== null) {
                $weights = $planner->fromTags($suiteRoot);
                if ($weights !== []) {
                    $mode = ShardPlanner::MODE_TAGS;
                }
            }
        }

        if ($weights === []) {
            return [ShardPlanner::MODE_SHARD, []];
        }
        return [$mode, $planner->plan($weights, $workers)];
    }

    private function resolveSuiteTestsRoot(string $suite): ?string
    {
        try {
            $settings = Configuration::suiteSettings($suite, Configuration::config());
        } catch (\Throwable $exception) {
            return null;
        }
        $path = $settings['path'] ?? '';
        if (!is_string($path) || $path === '' || !is_dir($path)) {
            return null;
        }
        $real = realpath($path);
        return $real !== false ? $real : $path;
    }

    private function cleanupReportDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($dir);
    }

    private function setupWorkers(int $workers, string $cwd, OutputInterface $output): void
    {
        $this->ensureMysqlStarted($cwd, $output);
        $this->createWorkerDatabases($workers, $cwd, $output);
        $this->createWorkerOutputDirs($workers, $cwd, $output);
    }

    /**
     * Managed MySQL auto-started here lingers after the command exits so subsequent runs
     * reuse it via the probe path. Tear it down externally (e.g. `mysqladmin shutdown`)
     * or by letting MysqlServerController manage the lifecycle in a long-running suite.
     */
    private function ensureMysqlStarted(string $cwd, OutputInterface $output): void
    {
        $envFile = $cwd . '/tests/.env';
        $envVars = array_merge($_ENV, (array)Env::envFile($envFile));

        $host = $envVars['WORDPRESS_DB_HOST'] ?? '127.0.0.1:2391';
        $user = $envVars['WORDPRESS_DB_USER'] ?? 'root';
        $pass = $envVars['WORDPRESS_DB_PASSWORD'] ?? '';

        $parts = explode(':', (string)$host);
        $ip    = $parts[0] !== '' ? $parts[0] : '127.0.0.1';
        $port  = (int)($parts[1] ?? 3306);

        if ($this->probeMysql($ip, $port, (string)$user, (string)$pass)) {
            $output->writeln('<info>MySQL is running on ' . $host . '</info>');
            return;
        }

        $output->writeln('<info>MySQL not reachable at ' . $host . '; starting a managed instance...</info>');

        $baseDbName = (string)($envVars['WORDPRESS_DB_NAME'] ?? 'wordpress');
        $dataDir    = $cwd . '/var/_output/_mysql_server';
        if (!is_dir($dataDir) && !mkdir($dataDir, 0777, true) && !is_dir($dataDir)) {
            throw new RuntimeException("Failed to create MySQL data directory: {$dataDir}");
        }

        try {
            $mysql = new MysqlServer(
                $dataDir,
                $port,
                $baseDbName,
                $user !== '' ? (string)$user : 'root',
                is_string($pass) ? $pass : ''
            );
            $mysql->setOutput($output);
            $mysql->start();
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Could not start managed MySQL server on port {$port}: {$e->getMessage()}",
                0,
                $e
            );
        }

        $deadline = microtime(true) + self::MYSQL_READY_TIMEOUT_SECONDS;
        while (microtime(true) < $deadline) {
            // @phpstan-ignore-next-line MySQL state changes after start; false-positive from line 385 flow
            if ($this->probeMysql($ip, $port, (string)$user, (string)$pass)) {
                $output->writeln('<info>MySQL ready on ' . $host . '</info>');
                return;
            }
            usleep(250000);
        }

        throw new RuntimeException(sprintf(
            'Managed MySQL server on port %d did not become reachable within %d seconds.',
            $port,
            (int)self::MYSQL_READY_TIMEOUT_SECONDS
        ));
    }

    private function probeMysql(string $ip, int $port, string $user, string $pass): bool
    {
        try {
            new PDO("mysql:host={$ip};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    private function createWorkerDatabases(int $workers, string $cwd, OutputInterface $output): void
    {
        $envFile = $cwd . '/tests/.env';
        $envVars = array_merge($_ENV, (array)Env::envFile($envFile));

        $baseDbName = $envVars['WORDPRESS_DB_NAME'] ?? 'wordpress';
        $host = $envVars['WORDPRESS_DB_HOST'] ?? '127.0.0.1:2391';
        $user = $envVars['WORDPRESS_DB_USER'] ?? 'root';
        $pass = $envVars['WORDPRESS_DB_PASSWORD'] ?? '';

        for ($i = 0; $i < $workers; $i++) {
            $workerDbName = $baseDbName . '_w' . $i;
            try {
                $db = new MysqlDatabase($workerDbName, $user, $pass, $host);
                $db->drop();
                $db->create();
                $output->writeln("<info>Created database {$workerDbName}</info>");
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    "Failed to create worker database {$workerDbName}: {$e->getMessage()}"
                );
            }
        }
    }

    private function createWorkerOutputDirs(int $workers, string $cwd, OutputInterface $output): void
    {
        for ($i = 0; $i < $workers; $i++) {
            $dir = $cwd . '/var/_output/w' . $i;
            if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException("Failed to create worker output directory: {$dir}");
            }
        }
    }

    private function teardownWorkers(int $workers, string $cwd, OutputInterface $output): void
    {
        $this->killWorkerPhpServers($workers, $cwd, $output);
        $this->dropWorkerDatabases($workers, $cwd, $output);
        $this->removeWorkerDirs($workers, $cwd, $output);
    }

    private function killWorkerPhpServers(int $workers, string $cwd, OutputInterface $output): void
    {
        for ($i = 0; $i < $workers; $i++) {
            $pidFile = $cwd . '/var/_output/w' . $i . '/php-built-in-server.pid';
            if (!is_file($pidFile)) {
                continue;
            }
            $pid = (int)trim((string)file_get_contents($pidFile));
            if ($pid <= 0) {
                continue;
            }
            // Kill child processes first (PHP servers have workers)
            if (function_exists('shell_exec')) {
                $children = explode("\n", (string)@shell_exec("pgrep -P {$pid}") ?: '');
                foreach ($children as $child) {
                    $childPid = (int)trim($child);
                    if ($childPid > 0) {
                        @posix_kill($childPid, 15);
                    }
                }
            }
            @posix_kill($pid, 15);
            @unlink($pidFile);
        }
    }

    private function dropWorkerDatabases(int $workers, string $cwd, OutputInterface $output): void
    {
        $envFile = $cwd . '/tests/.env';
        $envVars = array_merge($_ENV, (array)Env::envFile($envFile));

        $baseDbName = $envVars['WORDPRESS_DB_NAME'] ?? 'wordpress';
        $host = $envVars['WORDPRESS_DB_HOST'] ?? '127.0.0.1:2391';
        $user = $envVars['WORDPRESS_DB_USER'] ?? 'root';
        $pass = $envVars['WORDPRESS_DB_PASSWORD'] ?? '';

        for ($i = 0; $i < $workers; $i++) {
            $workerDbName = $baseDbName . '_w' . $i;
            try {
                $db = new MysqlDatabase($workerDbName, $user, $pass, $host);
                $db->drop();
                $output->writeln("<info>Dropped database {$workerDbName}</info>");
            } catch (\Throwable $e) {
                $output->writeln("<warning>Failed to drop database {$workerDbName}: {$e->getMessage()}</warning>");
            }
        }
    }

    private function removeWorkerDirs(int $workers, string $cwd, OutputInterface $output): void
    {
        for ($i = 0; $i < $workers; $i++) {
            $dirs = [
                $cwd . '/var/_output/w' . $i,
                $cwd . '/var/tmp/w' . $i,
                $cwd . '/var/tmp/_cache/w' . $i,
            ];

            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $this->removeDir($dir);
                }
            }
        }
    }

    private function removeDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_dir($file)) {
                $this->removeDir($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }
}
