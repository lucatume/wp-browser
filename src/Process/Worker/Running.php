<?php

namespace lucatume\WPBrowser\Process\Worker;

use Closure;
use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Opis\Closure\SerializableClosure;
use lucatume\WPBrowser\Process\MemoryUsage;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\Protocol\Request;
use lucatume\WPBrowser\Process\Protocol\Response;

class Running implements WorkerInterface
{
    use MemoryUsage;

    /**
     * @var mixed|null
     */
    private mixed $returnValue = null;
    private string $stdoutBuffer = '';
    private string $stderrBuffer = '';
    private bool $didExtractReturnValueFromStderr = false;

    /**
     * @param string[] $requiredResourcesIds
     */
    public function __construct(
        private string $id,
        private WorkerProcess $proc,
        private array $requiredResourcesIds = []
    ) {
    }

    /**
     * @throws ConfigurationException|ProcessException
     */
    public static function fromWorker(Worker $worker, bool $useFilePayloads = false, bool $inheritEnv = false): Running
    {
        $workerCallable = $worker->getCallable();
        $workerClosure = $workerCallable instanceof Closure ?
            $workerCallable
            : static function () use ($workerCallable) {
                return $workerCallable();
            };


        $workerScriptPathname = __DIR__ . '/worker-script.php';
        $control = $worker->getControl();

        if ($inheritEnv) {
            $control['env'] = array_merge($control['env'] ?? [], getenv());
        }

        $workerSerializableClosure = new SerializableClosure($workerClosure);

        $request = new Request($control, $workerSerializableClosure);
        $request->setUseFilePayloads($useFilePayloads);


        try {
            $workerProcess = new WorkerProcess([PHP_BINARY, $workerScriptPathname, $request->getPayload()]);
            $workerProcess->start();
        } catch (\Exception $e) {
            throw new ProcessException(
                "Failed to start the worker process: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }

        return new Running(
            $worker->getId(),
            $workerProcess,
            $worker->getRequiredResourcesIds()
        );
    }

    private function kill(int $pid): void
    {
        DIRECTORY_SEPARATOR === '\\' ?
            exec("taskkill /F /T /PID $pid 2>nul 1>nul")
            : exec("kill -9 $pid 2>&1 > /dev/null");
    }

    public function terminate(): Exited
    {
        $pid = $this->proc->getPid();

        if (!empty($pid)) {
            $this->kill($pid);
        }

        $this->proc->stop(0, 9); // SIGKILL.

        return Exited::fromRunningWorker($this);
    }

    public function isRunning(): bool
    {
        return $this->proc->isRunning();
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array<string>
     */
    public function getRequiredResourcesIds(): array
    {
        return $this->requiredResourcesIds;
    }

    public function getExitCode(): int
    {
        return $this->proc->getExitCode() ?? -1;
    }

    public function getStartTime(): float
    {
        return $this->proc->getStartTime();
    }

    /**
     * @return resource
     * @throws ProcessException
     */
    public function getStdoutStream()
    {
        return $this->proc->getStdoutStream();
    }

    /**
     * @return resource
     * @throws ProcessException
     */
    public function getStdErrStream()
    {
        return $this->proc->getStdErrStream();
    }

    /**
     * @param resource $stream
     * @throws ProcessException
     */
    public function readStream($stream): int
    {
        if (!is_resource($stream) || feof($stream)) {
            return 0;
        }

        if ($stream === $this->getStdoutStream()) {
            $buffer = $this->proc->getIncrementalOutput();
            $this->stdoutBuffer .= $buffer;
        } else {
            $buffer = $this->proc->getIncrementalErrorOutput();
            $this->stderrBuffer .= $buffer;
        }

        return strlen($buffer);
    }

    public function getStderrBuffer(): string
    {
        $this->extractReturnValueFromStderr();

        return $this->stderrBuffer;
    }

    public function getStdoutBuffer(): string
    {
        return $this->stdoutBuffer;
    }

    private function extractReturnValueFromStderr(): void
    {
        if ($this->didExtractReturnValueFromStderr) {
            return;
        }

        $stderrBufferString = $this->stderrBuffer;

        $this->didExtractReturnValueFromStderr = true;

        if (empty($stderrBufferString)) {
            return;
        }

        $response = Response::fromStderr($stderrBufferString);
        $returnValue = $response->getReturnValue();
        $telemetry = $response->getTelemetry();

        $this->stderrBuffer = substr($stderrBufferString, 0, $response->getStderrLength());
        $this->memoryUsage = $telemetry['memoryPeakUsage'] ?? null;
        $this->returnValue = $returnValue;
    }

    /**
     * @return mixed|null
     */
    public function getReturnValue(): mixed
    {
        $this->extractReturnValueFromStderr();

        return $this->returnValue;
    }
}
