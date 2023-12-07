<?php

namespace lucatume\WPBrowser\Process;

use lucatume\WPBrowser\Utils\Property;
use lucatume\WPBrowser\Utils\Serializer;
use ReflectionException;
use Throwable;

class SerializableThrowable
{
    public const RELATIVE_PAHTNAMES = 1;

    /**
     * @var \Throwable
     */
    private $throwable;
    /**
     * @var array<int,array<string,mixed>>
     */
    private $trace;
    /**
     * @var string
     */
    private $file;
    /**
     * @var int
     */
    private $line;
    /**
     * @var int
     */
    private $code;
    /**
     * @var string
     */
    private $message;
    /**
     * @var bool
     */
    private $colorize = true;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = Serializer::makeThrowableSerializable($throwable);
        $this->message = $throwable->getMessage();
        $this->code = $throwable->getCode();
        $trace = $throwable->getTrace();
        foreach ($trace as &$traceEntry) {
            unset($traceEntry['args']);
        }
        unset($traceEntry);
        $this->trace = $trace;
        $this->file = $throwable->getFile();
        $this->line = $throwable->getLine();
    }

    /**
     * @return array{
     *     colorize: bool,
     *     throwable: Throwable,
     *     message: string,
     *     code: int,
     *     file: string,
     *     line: int,
     *     trace: array<int,array<string,mixed>>
     * }
     */
    public function __serialize(): array
    {
        return [
            'colorize' => $this->colorize,
            'throwable' => $this->throwable,
            'message' => $this->message,
            'code' => $this->code,
            'file' => $this->file,
            'line' => $this->line,
            'trace' => $this->trace,
        ];
    }

    /**
     * @param array{
     *     colorize: bool,
     *     throwable: Throwable,
     *     message: string,
     *     code: int,
     *     file: string,
     *     line: int,
     *     trace: array<int,array<string,mixed>>
     * } $data
     *
     * @throws ReflectionException
     */
    public function __unserialize(array $data): void
    {
        $this->throwable = $data['throwable'];
        $this->colorize = $data['colorize'];
        $this->message = $data['message'];
        $this->code = $data['code'];
        $this->file = $data['file'];
        $this->line = $data['line'];
        $this->trace = $data['trace'];
    }

    /**
     * @throws ReflectionException
     */
    public function getThrowable(int $options = 0): Throwable
    {
        $errorClass = $this->throwable instanceof \Error ? \Error::class : \Exception::class;
        Property::setPropertiesForClass($this->throwable, $errorClass, [
            'message' => $this->message,
            'code' => $this->code,
            'file' => $this->file,
            'line' => $this->line,
            'trace' => $this->prettyPrintTrace($this->trace)
        ]);

        if ($options & self::RELATIVE_PAHTNAMES) {
            $this->makeTraceFilesRelative();
        }
        return $this->throwable;
    }

    /**
     * @param array<int,array<string,mixed>> $trace
     *
     * @return array<int,array<string,mixed>>
     */
    private function prettyPrintTrace(array $trace): array
    {
        $updatedTrace = [];
        $streamIsatty = function ($stream) {
            if (\function_exists('stream_isatty')) {
                return stream_isatty($stream);
            }
            if (!\is_resource($stream)) {
                trigger_error('stream_isatty() expects parameter 1 to be resource, ' . \gettype($stream) . ' given', \E_USER_WARNING);
                return false;
            }
            if ('\\' === \DIRECTORY_SEPARATOR) {
                $stat = @fstat($stream);
                // Check if formatted mode is S_IFCHR
                return $stat ? 020000 === ($stat['mode'] & 0170000) : false;
            }
            return \function_exists('posix_isatty') && @posix_isatty($stream);
        };
        $colorize = $this->colorize && $streamIsatty(STDOUT);
        // Detect whether to use colors or not depending on the TTY.
        foreach ($trace as $k => $traceEntry) {
            if (!(isset($traceEntry['file']) && is_string($traceEntry['file'])
                && strpos($traceEntry['file'], 'closure://') !== false)) {
                $updatedTrace[$k] = $traceEntry;
                continue;
            }

            $line = $traceEntry['line'];
            $correctLine = $line - 2;
            if ($correctLine < 1) {
                $updatedTrace[$k] = $traceEntry;
                continue;
            }
            $lines = explode("\n", $traceEntry['file']);
            $linesCount = count($lines);
            for ($i = 1; $i < $linesCount; $i++) {
                $isCorrectLine = $i === $correctLine;
                $linePrefix = ($isCorrectLine ? '>' : '') . " $i|";
                $paddedLine = str_pad($linePrefix, 5, ' ', STR_PAD_LEFT) . $lines[$i];
                if ($isCorrectLine && $colorize) {
                    // Colorize the line in pink.
                    $paddedLine = "\e[35m" . $paddedLine . "\e[0m";
                }
                $lines[$i] = $paddedLine;
            }
            $lines[$i - 1] = preg_replace('~}:\\d+~', '', $lines[$i - 1]);
            $traceEntry['file'] = implode(PHP_EOL, $lines);
            $traceEntry['line'] = $correctLine;
            $updatedTrace[$k] = $traceEntry;
        }
        return $updatedTrace;
    }

    /**
     * @throws ReflectionException
     */
    protected function makeTraceFilesRelative(): void
    {
        $relativePathnameTrace = [];
        $cwd = getcwd() ?: '';
        $relativeFile = str_replace($cwd, '', $this->throwable->getFile());
        foreach ($this->throwable->getTrace() as $k => $traceEntry) {
            if (!isset($traceEntry['file']) || strpos($traceEntry['file'], 'closure://') !== false) {
                $relativePathnameTrace[$k] = $traceEntry;
                continue;
            }
            $traceEntry['file'] = str_replace($cwd, '', $traceEntry['file']);
            $traceEntry['line'] = 1;
            $relativePathnameTrace[$k] = $traceEntry;
        }
        $errorClass = $this->throwable instanceof \Error ? \Error::class : \Exception::class;
        Property::setPropertiesForClass($this->throwable, $errorClass, [
            'file' => $relativeFile,
            'trace' => $relativePathnameTrace,
        ]);
    }

    public function colorize(bool $colorize): void
    {
        $this->colorize = $colorize;
    }
}
