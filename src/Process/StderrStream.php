<?php

namespace lucatume\WPBrowser\Process;

use CompileError;
use DateTime;
use DateTimeInterface;
use ErrorException;
use lucatume\WPBrowser\Process\StderrStream\Error;
use lucatume\WPBrowser\Process\StderrStream\TraceEntry;
use lucatume\WPBrowser\Utils\Property;
use ParseError;
use ReflectionClass;
use ReflectionException;
use Throwable;

class StderrStream
{
    public const RELATIVE_PATHNAMES = 1;

    /**
     * @var array<Error>
     */
    private $parsed = [];

    /**
     * @var \DateTimeInterface
     */
    private $currentDatetime;

    public function __construct(string $streamContents, int $options = 0, DateTimeInterface $currentDateTime = null)
    {
        $this->currentDatetime = $currentDateTime ?: new DateTime();
        $this->parse($streamContents, $options);
    }

    /**
     * @param \lucatume\WPBrowser\Process\StderrStream\Error|\lucatume\WPBrowser\Process\StderrStream\TraceEntry $traceItem
     * @return \lucatume\WPBrowser\Process\StderrStream\TraceEntry|\lucatume\WPBrowser\Process\StderrStream\Error
     */
    private function applyItemOptions($traceItem, int $options)
    {
        if ($options & self::RELATIVE_PATHNAMES) {
            $cwd = (getcwd() ?: codecept_root_dir());
            foreach (['file', 'args'] as $key) {
                if (!(isset($traceItem->$key) && is_string($traceItem->$key))) {
                    continue;
                }
                $traceItem->$key = str_replace($cwd, '', $traceItem->$key);
            }
        }
        return $traceItem;
    }

    private function applyErrorOptions(Error $item, int $options): Error
    {
        /** @var Error $updated */
        $updated = $this->applyItemOptions($item, $options);
        return $updated;
    }

    private function applyTraceEntryOptions(TraceEntry $item, int $options): TraceEntry
    {
        /** @var TraceEntry $updated */
        $updated = $this->applyItemOptions($item, $options);
        return $updated;
    }

    private function formatInvertedTraceError(Error $error, bool $isNumericStackTrace): Error
    {
        if (!$isNumericStackTrace) {
            return $error;
        }

        // Reverse the trace to match the order of the date trace.
        $error->trace = array_reverse($error->trace);
        // Add a trace entry for the error itself.
        $errorTrace = new TraceEntry();
        $errorTrace->date = $error->date;
        $errorTrace->time = $error->time;
        $errorTrace->timezone = $error->timezone;
        $errorTrace->call = 'n/a';
        $errorTrace->args = 'n/a';
        $errorTrace->file = $error->file;
        $errorTrace->line = $error->line;
        $error->trace[] = $errorTrace;

        return $error;
    }

    public function parse(string $stderrStreamContents, int $options = 0): void
    {
        $currentDateTime = $this->currentDatetime ?? new DateTime();
        $len = strlen($stderrStreamContents);

        if ($len === 0) {
            $this->parsed = [];
            return;
        }

        $parsed = [];
        $lines = explode(PHP_EOL, $stderrStreamContents);
        $linesCount = count($lines);
        $currentError = null;
        $isNumericStackTrace = false;

        $typePattern = '/^(\\[(?<date>.+?) (?<time>.+?) (?<timezone>.+?)\\] )?' .
            'PHP (?<type>[\\w\\s]+?):\\s+(?<message>.*) in (?<file>.+?)(?:on line |:)(?<line>\\d+)$/';
        $typeStartPattern = '/^(\\[(?<date>.+?) (?<time>.+?) (?<timezone>.+?)\\] )?' .
            'PHP (?<type>[\\w\\s]+?):\\s+(?<message>.*)$/';
        $typeEndPattern = '/ in (?<file>.+?)(?:on line |:)(?<line>\\d+)$/';
        $dateTracePattern = '/^(\\[(?<date>.+?) (?<time>.+?) (?<timezone>.+?)\\] )?' .
            'PHP\\s+\\d+\\. (?<call>.+?)\\((?<args>.*?)\\) (?<file>.+?):(?<line>\\d+)$/';
        $numberTracePattern = '/^#\\d+ /';
        $numberTraceInlinePattern = '/^#\\d+ (?<file>.+?)\\((?<line>\\d+)\\): (?<call>.+?)\\((?<args>.*?)\\)$/';
        $closureTracePattern = '/^#\\d+ (?<closure>closure:\\/\\/.*)$/';
        $closureFinalLinePattern = '/\\}\\((?<line>\\d+)\\): (?<call>.+?)\\((?<args>.*?)\\)$/';
        $uncaughtExceptionPattern = '/^Uncaught (?<exceptionClass>.+?):/';

        for ($i = 0; $i < $linesCount; $i++) {
            $line = $lines[$i];

            if (preg_match($typePattern, $line, $typeMatches)) {
                if ($currentError !== null) {
                    $parsed[] = $this->formatInvertedTraceError($currentError, $isNumericStackTrace);
                }

                $currentError = new Error();
                $isNumericStackTrace = false;
                $currentError->date = $typeMatches['date'] ?: $currentDateTime->format('d-M-Y');
                $currentError->time = $typeMatches['time'] ?: $currentDateTime->format('H:i:s');
                $currentError->timezone = $typeMatches['timezone'] ?: $currentDateTime->getTimezone()->getName();
                $currentError->type = $typeMatches['type'];
                $currentError->message = $typeMatches['message'];
                $currentError->file = $typeMatches['file'];
                $currentError->line = (int)$typeMatches['line'];

                if (preg_match($uncaughtExceptionPattern, $currentError->message, $uncaughtExceptionMatches)) {
                    $currentError->isException = true;
                    $currentError->exceptionClass = $uncaughtExceptionMatches['exceptionClass'];
                }

                $currentError = $this->applyErrorOptions($currentError, $options);

                $currentError->trace = [];

                continue;
            }

            if (preg_match($typeStartPattern, $line, $typeStartMatches)) {
                if ($currentError !== null) {
                    $parsed[] = $this->formatInvertedTraceError($currentError, $isNumericStackTrace);
                }

                $currentError = new Error();
                $isNumericStackTrace = false;
                $currentError->date = $typeStartMatches['date'] ?: $currentDateTime->format('d-M-Y');
                $currentError->time = $typeStartMatches['time'] ?: $currentDateTime->format('H:i:s');
                $currentError->timezone = $typeStartMatches['timezone'] ?: $currentDateTime->getTimezone()->getName();
                $currentError->type = $typeStartMatches['type'];
                $currentError->message = $typeStartMatches['message'];

                // Keep ingesting until the line matches with $typeEndPattern
                while (isset($lines[$i]) && !preg_match($typeEndPattern, $lines[$i], $typeEndMatches)) {
                    $currentError->message .= PHP_EOL . $lines[$i++];
                }

                if (preg_match($uncaughtExceptionPattern, $currentError->message, $uncaughtExceptionMatches)) {
                    $currentError->isException = true;
                    $currentError->exceptionClass = $uncaughtExceptionMatches['exceptionClass'];
                }

                if (!isset($typeEndMatches['file'], $typeEndMatches['line'])) {
                    $currentError->file = 'n/a';
                    $currentError->line = 0;
                } else {
                    $currentError->file = $typeEndMatches['file'];
                    $currentError->line = (int)$typeEndMatches['line'];
                }

                $currentError = $this->applyErrorOptions($currentError, $options);

                $currentError->trace = [];

                continue;
            }

            if ($currentError !== null && preg_match($dateTracePattern, $line, $dateTraceMatches)) {
                $traceEntry = new TraceEntry();
                foreach (['date', 'time', 'timezone', 'call', 'args', 'file', 'line'] as $key) {
                    $value = $dateTraceMatches[$key];
                    $traceEntry->$key = $key === 'line' ? (int)$value : $value;
                }

                $traceEntry = $this->applyTraceEntryOptions($traceEntry, $options);

                $currentError->trace[] = $traceEntry;
                continue;
            }

            if (!preg_match($numberTracePattern, $line)) {
                continue;
            }

            $isNumericStackTrace = true;

            if ($currentError !== null && preg_match($numberTraceInlinePattern, $line, $numberTraceInlineMatches)) {
                $traceEntry = new TraceEntry();
                $traceEntry->date = $currentError->date ?? '';
                $traceEntry->time = $currentError->time ?? '';
                $traceEntry->timezone = $currentError->timezone ?? '';
                foreach (['call', 'args', 'file', 'line'] as $key) {
                    $value = $numberTraceInlineMatches[$key];
                    $traceEntry->$key = $key === 'line' ? (int)$value : $value;
                }

                $traceEntry = $this->applyTraceEntryOptions($traceEntry, $options);
                $currentError->trace[] = $traceEntry;
                continue;
            }

            if ($currentError !== null && preg_match($closureTracePattern, $line, $closureTraceMatches)) {
                $traceEntry = new TraceEntry();
                $traceEntry->date = $currentError->date ?? '';
                $traceEntry->time = $currentError->time ?? '';
                $traceEntry->timezone = $currentError->timezone ?? '';
                $traceEntry->file = 'n/a';
                $traceEntry->line = 0;
                $traceEntry->call = 'n/a';
                $traceEntry->args = 'n/a';
                $closureLines = $closureTraceMatches['closure'];
                while (isset($lines[$i + 1]) &&
                    !preg_match($closureFinalLinePattern, $lines[$i + 1], $closureFinalLineMatches)
                ) {
                    $closureLines .= PHP_EOL . $lines[$i + 1];
                    $i++;
                }

                if (!isset($closureFinalLineMatches)) {
                    continue;
                }

                $traceEntry->file = $closureLines . PHP_EOL . $lines[$i];
                $traceEntry->line = (int)$closureFinalLineMatches['line'];
                $traceEntry->call = $closureFinalLineMatches['call'];
                $traceEntry->args = $closureFinalLineMatches['args'];

                $currentError->trace[] = $traceEntry;
            }
        }

        if ($currentError !== null) {
            // Store the previous error.
            $parsed[] = $this->formatInvertedTraceError($currentError, $isNumericStackTrace);
        }

        $this->parsed = $parsed;
    }

    /**
     * @throws ReflectionException
     */
    public function getThrowable(): ?Throwable
    {
        if (!count($this->parsed)) {
            return null;
        }

        $sourceError = $this->parsed[0];

        if (!empty($sourceError->isException) && !empty($sourceError->exceptionClass)) {
            $throwableClass = $sourceError->exceptionClass;
        } else {
            $throwableClass = $this->mapTypeToThrowableClass($sourceError->type);
        }

        if ($throwableClass === ErrorException::class) {
            return new $throwableClass(
                $sourceError->message,
                0,
                $this->mapTypeToSeverity($sourceError->type),
                $sourceError->file,
                $sourceError->line
            );
        }

        if (!class_exists($throwableClass)) {
            return null;
        }

        /** @var Throwable $throwable */
        $throwable = (new ReflectionClass($throwableClass))->newInstanceWithoutConstructor();

        Property::setPrivateProperties($throwable, [
            'message' => $sourceError->message,
            'file' => $sourceError->file,
            'line' => $sourceError->line,
            'trace' => array_map(static function (TraceEntry $t) {
                return $t->toArray();
            }, $sourceError->trace),
            'code' => 0, // The code is not available in the error log.
        ]);

        return $throwable;
    }

    /**
     * @return array<int, mixed>
     */
    public function getParsed(): array
    {
        return $this->parsed;
    }

    private function mapTypeToThrowableClass(string $type): string
    {
        switch ($type) {
            case 'Parse error':
                return ParseError::class;
            case 'Compile error':
                return CompileError::class;
            default:
                return ErrorException::class;
        }
    }

    private function mapTypeToSeverity(string $type): int
    {
        switch ($type) {
            case 'Runtime warning':
            case 'Warning':
                return E_WARNING;
            case 'Parse error':
                return E_PARSE;
            case 'Runtime notice':
            case 'Notice':
                return E_NOTICE;
            case 'Strict Standards':
                return E_STRICT;
            case 'Recoverable error':
                return E_RECOVERABLE_ERROR;
            case 'Deprecated':
                return E_DEPRECATED;
            case 'Core error':
                return E_CORE_ERROR;
            case 'Core warning':
                return E_CORE_WARNING;
            case 'Compile error':
                return E_COMPILE_ERROR;
            case 'Compile warning':
                return E_COMPILE_WARNING;
            case 'User error':
                return E_USER_ERROR;
            case 'User warning':
                return E_USER_WARNING;
            case 'User notice':
                return E_USER_NOTICE;
            case 'User deprecated':
                return E_USER_DEPRECATED;
            default:
                return E_ERROR;
        }
    }
}
