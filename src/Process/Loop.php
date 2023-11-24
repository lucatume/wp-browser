<?php

namespace lucatume\WPBrowser\Process;

use Closure;
use Codeception\Exception\ConfigurationException;
use Codeception\Util\Debug;
use lucatume\WPBrowser\Process\Worker\Exited;
use lucatume\WPBrowser\Process\Worker\Result;
use lucatume\WPBrowser\Process\Worker\Running;
use lucatume\WPBrowser\Process\Worker\Worker;
use lucatume\WPBrowser\Process\Worker\WorkerInterface;
use Throwable;

class Loop
{
    use MemoryUsage;

    private int $peakParallelism = 0;
    /**
     * @var array<string,Result>
     */
    private array $results = [];
    /**
     * @var array<string,Running>
     */
    private array $started = [];
    /**
     * @var array<string,Running>
     */
    private array $running = [];
    /**
     * @var array<string,Exited>
     */
    private array $exited = [];

    /**
     * @var array<string,Worker>
     */
    private array $workers = [];

    private bool $fastFailureFlagRaised = false;
    private bool $useFilePayloads = false;

    /**
     * @param array<int|string,Worker|callable> $workers
     * @param array{
     *     requireFiles?: array<string>,
     *     cwd?: string
     * } $options
     */
    public function __construct(
        array $workers = [],
        private int $parallelism = 1,
        private bool $fastFailure = false,
        private float $timeout = 30,
        array $options = []
    ) {
        if (Debug::isEnabled() || getenv('WPBROWSER_LOOP_DEBUG')) {
            $this->timeout = 10 ** 10;
        }
        $this->addWorkers($workers, $options);
    }

    /**
     * @param array{
     *     rethrow?: bool,
     *     requireFiles?: array<string>,
     *     cwd?: string,
     * } $options
     *
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     */
    public static function executeClosure(Closure $closure, int $timeout = 30, array $options = []): Result
    {
        $loop = (new self([$closure], 1, true, $timeout, $options))->run();
        $results = $loop->getResults();
        $result = $results[0];
        $returnValue = $result->getReturnValue();

        if (!empty($options['rethrow']) && $returnValue instanceof Throwable) {
            throw $returnValue;
        }

        return $result;
    }

    /**
     * @param array{
     *     rethrow?: bool,
     *     requireFiles?: array<string>,
     *     cwd?: string,
     * } $options
     *
     * @throws WorkerException
     * @throws Throwable
     * @throws ProcessException
     */
    public static function executeClosureOrFail(
        Closure $toInstallWordPressNetwork,
        int $timeout = 30,
        array $options = []
    ): Result {
        $options['rethrow'] = true;
        return self::executeClosure($toInstallWordPressNetwork, $timeout, $options);
    }

    /**
     * @param array<int|string,Worker|callable> $workers
     * @param array{
     *     requireFiles?: array<string>,
     *     cwd?: string
     * } $options
     */
    public function addWorkers(array $workers, array $options = []): Loop
    {
        $builtWorkers = array_map([$this, 'ensureWorker'], array_keys($workers), $workers);

        if (isset($options['requireFiles'])) {
            foreach ($builtWorkers as $worker) {
                $worker->setRequireFiles($options['requireFiles']);
            }
        }

        if (isset($options['cwd'])) {
            foreach ($builtWorkers as $worker) {
                $worker->setCwd($options['cwd']);
            }
        }

        array_push($this->workers, ...$builtWorkers);
        $this->sortWorkersByResource();

        return $this;
    }

    private function getRunnableWorker(): ?Worker
    {
        $exitedWorkersIds = array_keys($this->exited);
        $runningWorkersIds = array_keys($this->started);

        if (count($exitedWorkersIds) === 0 && count($runningWorkersIds) === 0) {
            return reset($this->workers) ?: null;
        }

        $notStartedWorkers = array_filter(
            $this->workers,
            static function (Worker $w) use ($exitedWorkersIds, $runningWorkersIds): bool {
                $workerId = $w->getId();
                return !in_array($workerId, $exitedWorkersIds, false)
                    && !in_array($workerId, $runningWorkersIds, false);
            }
        );

        if (!count($notStartedWorkers)) {
            return null;
        }

        $busyResourcesIds = count($this->running) ? array_merge(
            ...array_map(static function (Running $rw): array {
                return $rw->getRequiredResourcesIds();
            }, array_values($this->running))
        ) : [];

        foreach ($notStartedWorkers as $w) {
            if (count(array_intersect($w->getRequiredResourcesIds(), $busyResourcesIds)) === 0) {
                return $w;
            }
        }

        return null;
    }

    private function startWorker(): void
    {
        $runnableWorker = $this->getRunnableWorker();

        if (!$runnableWorker instanceof Worker) {
            return;
        }

        try {
            $w = Running::fromWorker($runnableWorker, $this->useFilePayloads);
            $this->started[$w->getId()] = $w;
            $this->running[$w->getId()] = $w;
            $this->peakParallelism = max((int)$this->peakParallelism, count($this->running));
        } catch (Throwable $t) {
            $this->terminateAllRunningWorkers();
            throw $t;
        }
    }

    private function sortWorkersByResource(): void
    {
        usort($this->workers, static function (Worker $a, Worker $b): int {
            return count($a->getRequiredResourcesIds()) - count($b->getRequiredResourcesIds());
        });
        $this->workers = array_combine(
            array_map(static function (Worker $worker): string {
                return $worker->getId();
            }, $this->workers),
            $this->workers
        );
    }

    private function bootstrap(): void
    {
        $bootstrapLimit = min($this->parallelism, count($this->workers));
        for ($c = 0; $c < $bootstrapLimit; $c++) {
            $this->startWorker();
        }
    }

    private function collectOutput(): void
    {
        $readIndexToWorkerMap = [];

        $read = array_reduce(
            $this->running,
            function (array $streams, Running $w) use (&$readIndexToWorkerMap): array {
                $streams[] = $w->getStdoutStream();
                $streams[] = $w->getStdErrStream();
                $readIndexToWorkerMap[count($streams) - 2] = $w;
                $readIndexToWorkerMap[count($streams) - 1] = $w;
                return $streams;
            },
            []
        );

        if (empty($read)) {
            return;
        }

        $write = [];
        $except = [];
        $streamSelectStartTime = microtime(true);
        $updates = stream_select($read, $write, $except, 2);

        $streamSelectRuntime = microtime(true) - $streamSelectStartTime;
        if ($streamSelectRuntime < .01) {
            // Avoid CPU hogging on Mac.
            usleep((int)(10 ** 4 - $streamSelectRuntime));
        }

        if ($updates === false) {
            $this->terminateAllRunningWorkers();
            throw new ProcessException('Failed to read streams.');
        }

        if ($updates > 0) {
            /** @var int $readIndex */
            /** @var resource $stream */
            foreach ($read as $readIndex => $stream) {
                /** @var Running $worker */
                $worker = $readIndexToWorkerMap[$readIndex];
                $worker->readStream($stream);
            }
        }
    }

    private function collectResults(): void
    {
        foreach ($this->started as $runningWorker) {
            $id = $runningWorker->getId();
            $this->results[$id] = new Result(
                $id,
                $runningWorker->getExitCode(),
                $runningWorker->getStdoutBuffer(),
                $runningWorker->getStderrBuffer(),
                $runningWorker->getReturnValue(),
                $runningWorker->getMemoryUsage()
            );
        }
    }

    /**
     * @throws Throwable
     * @throws WorkerException
     * @throws ProcessException
     */
    public function run(): Loop
    {
        $this->bootstrap();

        /** @noinspection SlowArrayOperationsInLoopInspection */
        while (count($this->exited) < count($this->workers)) {
            foreach ($this->running as $w) {
                $status = $w->isRunning() ? null : $w->getExitCode();
                $isOverTime = (microtime(true) - $w->getStartTime()) > $this->timeout;
                $fastFailureFlagRaised = $this->fastFailure && ($isOverTime || ($status !== null && $status !== 0));

                $this->collectOutput();

                if ($fastFailureFlagRaised) {
                    $this->fastFailureFlagRaised = true;
                    $this->terminateAllRunningWorkers();
                    break 2;
                }

                if ($status !== null) {
                    $exitedWorker = Exited::fromRunningWorker($w);
                    $this->exited[$w->getId()] = $exitedWorker;
                    unset($this->running[$w->getId()]);
                    $this->startWorker();
                    continue;
                }

                if ($isOverTime) {
                    $exitedWorker = $w->terminate();
                    $this->exited[$w->getId()] = $exitedWorker;
                    unset($this->running[$w->getId()]);
                    $this->startWorker();
                }
            }
        }

        $this->memoryUsage = memory_get_peak_usage(true);

        $this->collectOutput();
        $this->collectResults();

        return $this;
    }

    /**
     * @return array<Result>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    private function terminateAllRunningWorkers(): Loop
    {
        foreach ($this->running as $runningWorker) {
            $this->exited[$runningWorker->getId()] = $runningWorker->terminate();
        }

        $this->running = array_diff_key($this->running, $this->exited);

        return $this;
    }

    public function failed(): bool
    {
        return $this->fastFailure && $this->fastFailureFlagRaised;
    }

    public function setUseFilePayloads(bool $useFilePayloads): Loop
    {
        $this->useFilePayloads = $useFilePayloads;
        return $this;
    }

    /**
     * @throws ConfigurationException
     */
    private function ensureWorker(string $id, callable|Worker $worker): Worker
    {
        if ($worker instanceof Worker) {
            return $worker;
        }

        return $this->buildWorker($id, $worker);
    }

    /**
     * @throws ConfigurationException
     */
    private function buildWorker(string $id, callable $worker): Worker
    {
        return new Worker($id, $worker, [], []);
    }
}
