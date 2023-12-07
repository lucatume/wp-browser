<?php

namespace lucatume\WPBrowser\Process\Protocol;

use lucatume\WPBrowser\Process\SerializableThrowable;
use lucatume\WPBrowser\Process\StderrStream;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\Opis\Closure\SerializableClosure;
use Throwable;

class Response
{
    /**
     * @var array{memoryPeakUsage: int}
     */
    private $telemetry = ['memoryPeakUsage' => 0];
    /**
     * @var mixed
     */
    private $returnValue;
    /**
     * @var int
     */
    private $exitValue;
    /**
     * @var string
     */
    public static $stderrValueSeparator = "\r\n\r\n#|worker-stderr-output|#\r\n\r\n";
    /**
     * @var int
     */
    private $stderrLength = 0;

    /**
     * @param array{memoryPeakUsage: int} $telemetry
     * @param mixed $returnValue
     */
    public function __construct(
        $returnValue,
        ?int $exitValue = null,
        array $telemetry = ['memoryPeakUsage' => 0]
    ) {
        $this->telemetry = $telemetry;
        if ($exitValue === null) {
            $this->exitValue = $returnValue instanceof Throwable || $returnValue instanceof SerializableThrowable ?
                1
                : 0;
        } else {
            $this->exitValue = $exitValue;
        }
        $this->returnValue = $returnValue;
    }

    public static function fromStderr(string $stderrBufferString): self
    {
        // Format: $<return value length>CRLF<return value>CRLF<memory peak usage length>CRLF<memory peak usage>CRLF.
        $payloadLength = strlen($stderrBufferString);
        $separatorPos = strpos($stderrBufferString, self::$stderrValueSeparator);

        if ($separatorPos === false) {
            // No separator found: the worker script did not fail gracefully.
            if ($payloadLength === 0) {
                // No information to build from: it's a failure.
                return new self(null, 1, ['memoryPeakUsage' => 0]);
            }

            // Got something on STDERR: try and build a useful Exception from it.
            $exception = (new StderrStream($stderrBufferString))->getThrowable();
            return new self($exception, 1, ['memoryPeakUsage' => 0]);
        }

        $afterSeparatorBuffer = substr($stderrBufferString, $separatorPos + strlen(self::$stderrValueSeparator));
        // Find the last CRLF in the buffer.
        $lastCrlfPos = strrpos($afterSeparatorBuffer, "\r\n");
        if ($lastCrlfPos !== false) {
            // Cut the string at the last CRLF.
            $afterSeparatorBuffer = substr($afterSeparatorBuffer, 0, $lastCrlfPos + 2);
        }

        [$returnValueClosure, $telemetry] = Parser::decode($afterSeparatorBuffer);

        $returnValue = is_callable($returnValueClosure) ? $returnValueClosure() : null;

        if ($returnValue instanceof SerializableThrowable) {
            $returnValue = $returnValue->getThrowable();
        }

        $exitValue = $returnValue instanceof Throwable ? 1 : 0;

        if (!(is_array($telemetry) && Arr::hasShape($telemetry, ['memoryPeakUsage' => 'int']))) {
            $telemetry = ['memoryPeakUsage' => 0];
        }

        /** @var array{memoryPeakUsage: int} $telemetry */
        $response = new self($returnValue, $exitValue, $telemetry);
        $response->stderrLength = $separatorPos;

        return $response;
    }

    public function getPayload(): string
    {
        $returnValue = $this->returnValue;
        $serializableClosure = new SerializableClosure(static function () use ($returnValue) {
            return $returnValue;
        });
        $telemetryData = array_merge($this->telemetry, [
            'memoryPeakUsage' => memory_get_peak_usage()
        ]);
        return Parser::encode([$serializableClosure, $telemetryData]);
    }

    public function getExitValue(): int
    {
        return $this->exitValue;
    }

    /**
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @return array{memoryPeakUsage: int}
     */
    public function getTelemetry(): array
    {
        return $this->telemetry;
    }

    public function getStderrLength(): int
    {
        return $this->stderrLength;
    }
}
