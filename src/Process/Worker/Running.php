<?php

namespace lucatume\WPBrowser\Process\Worker;

use BadMethodCallException;
use Closure;
use lucatume\WPBrowser\Process\MemoryUsage;
use Opis\Closure\SerializableClosure;
use RuntimeException;

class Running implements WorkerInterface
{
    use MemoryUsage;

    /**
     * @var array<string,mixed>
     */
    private array $telemetry = [];
    /**
     * @var array<string>
     */
    private array $requiredResourcesIds;
    private string $returnValueSeparator;
    /**
     * @var mixed|null
     */
    private mixed $returnValue = null;
    /**
     * @var resource
     */
    private $stdin;
    /**
     * @var null|array<string,string|int|bool>
     */
    private ?array $cachedStatus = null;

    private string $id;
    /**
     * @var resource
     */
    private $proc;
    /**
     * @var resource
     */
    private $stdout;
    /**
     * @var resource
     */
    private $stderr;
    private float $startTime;

    private string $stdoutBuffer = '';
    private string $stderrBuffer = '';

    public function __construct(
        string $id,
        $proc,
        array $pipes,
        float $startTime,
        array $control,
        array $requiredResourcesIds = []
    ) {
        [$this->stdin, $this->stdout, $this->stderr] = $pipes;

        if (!is_resource($proc)) {
            throw new BadMethodCallException('proc must be a resource');
        }

        if (!is_resource($this->stdin)) {
            throw new BadMethodCallException('stdin must be a resource');
        }

        if (!is_resource($this->stdout)) {
            throw new BadMethodCallException('stdout must be a resource');
        }

        if (!is_resource($this->stderr)) {
            throw new BadMethodCallException('stderr must be a resource');
        }

        $this->id = $id;
        $this->proc = $proc;
        $this->startTime = $startTime;
        $this->returnValueSeparator = $control['returnValueSeparator'] ?? md5(uniqid('sep_', true));
        $this->requiredResourcesIds = $requiredResourcesIds;
    }

    public static function fromWorker(Worker $worker): Running
    {
        $workerCallable = $worker->getCallable();
        $workerClosure = $workerCallable instanceof Closure ?
            $workerCallable
            : static function () use ($workerCallable) {
                return $workerCallable();
            };

        $workerSerializableClosure = new SerializableClosure($workerClosure);
        $serializedWorkerClosure = serialize($workerSerializableClosure);
        $workerScriptPathname = __DIR__ . '/worker-script.php';
        $workerCommand = sprintf(
            "%s %s %s %s",
            escapeshellarg(PHP_BINARY),
            escapeshellarg($workerScriptPathname),
            base64_encode(serialize($worker->getControl())),
            base64_encode($serializedWorkerClosure),
        );
        $pipesDef = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $startTime = microtime(true);
        $workerProc = proc_open($workerCommand, $pipesDef, $pipes);

        if (!is_resource($workerProc)) {
            throw new RuntimeException('Failed to open process.');
        }

        return new Running(
            $worker->getId(),
            $workerProc,
            $pipes,
            $startTime,
            $worker->getControl(),
            $worker->getRequiredResourcesIds()
        );
    }

    public function getStatus(): ?array
    {
        $liveStatus = is_resource($this->proc) ?
            proc_get_status($this->proc) :
            ['exitcode' => -1];

        if ($this->cachedStatus !== null && $liveStatus['exitcode'] === -1) {
            return $this->cachedStatus;
        }

        $this->cachedStatus = $liveStatus;

        return $liveStatus;
    }

    private function kill(int $pid): void
    {
        stripos(php_uname(''), 'win') > -1 ?
            exec("taskkill /F /T /PID $pid 2>nul 1>nul")
            : exec("kill -9 $pid 2>&1 > /dev/null");
    }

    public function terminate(): Exited
    {
        $status = $this->getStatus();
        $pid = (int)$status['pid'];

        $this->kill($pid);

        foreach (
            [
                'STDIN' => $this->stdin,
                'STDOUT' => $this->stdout,
                'STDERR' => $this->stderr,
            ] as $name => $resource
        ) {
            if (is_resource($resource) && !fclose($resource)) {
                throw new RuntimeException("Failed to close the $name pipe.");
            }
        }

        // Kill signal.
        $procClose = proc_close($this->proc);
        if ($procClose !== -1) {
            // Do not update the cached status if the process had already terminated.
            $this->cachedStatus['exitcode'] = $procClose;
        }

        return Exited::fromRunningWorker($this);
    }

    public function isRunning(): bool
    {
        $status = $this->getStatus();
        return (bool)(($status['running']) ?? false);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRequiredResourcesIds(): array
    {
        return $this->requiredResourcesIds;
    }

    public function getExitCode(): int
    {
        return $this->getStatus()['exitcode'] ?? -1;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @return resource
     */
    public function getStdoutStream()
    {
        return $this->stdout;
    }

    /**
     * @return resource
     */
    public function getStderrStream()
    {
        return $this->stderr;
    }

    /**
     * @param resource $stream
     */
    public function readStream($stream): int
    {
        if (!is_resource($stream) || feof($stream)) {
            return 0;
        }

        $buffer = '';
        do {
            $read = fread($stream, 2048);
            if ($read === false) {
                throw new RuntimeException('Failed to read from stream.');
            }
            $buffer .= $read;
        } while (!feof($stream));

        if ($stream === $this->stdout) {
            $this->stdoutBuffer .= $buffer;
        } else {
            $this->stderrBuffer .= $buffer;
        }

        return strlen($buffer);
    }

    public function readStdoutStream(): int
    {
        return $this->readStream($this->stdout);
    }

    public function readStderrStream(): int
    {
        return $this->readStream($this->stderr);
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
        $stderrBufferString = $this->stderrBuffer;

        if (!str_contains($stderrBufferString, $this->returnValueSeparator)) {
            return;
        }

        if (empty($this->stderrBuffer)) {
            return;
        }

        [$stderrBuffer, $base64EncodedSerializedReturnValueClosure, $base64EncodedSerializedTelemetry] = explode(
            $this->returnValueSeparator,
            $this->stderrBuffer,
            3
        );

        $serializedReturnValueClosure = (string)base64_decode($base64EncodedSerializedReturnValueClosure);
        $returnValueClosure = null;
        if ($serializedReturnValueClosure !== '') {
            $returnValueClosure = @unserialize($serializedReturnValueClosure, ['allowed_classes' => true]);
        }
        $serializedTelemetry = (string)base64_decode($base64EncodedSerializedTelemetry);
        /** @var array{memoryPeakUsage: int } $telemetry */
        $telemetry = ['memoryPeakUsage' => 0];
        if ($serializedTelemetry !== '') {
            // Telemetry will be just an array.
            $telemetry = array_replace($telemetry, @unserialize($serializedTelemetry, ['allowed_classes' => false]));
        }
        $this->stderrBuffer = $stderrBuffer;
        $this->memoryUsage = $telemetry['memoryPeakUsage'] ?? null;
        $this->returnValue = $returnValueClosure instanceof SerializableClosure ? $returnValueClosure() : null;
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