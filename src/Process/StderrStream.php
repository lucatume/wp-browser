<?php

namespace lucatume\WPBrowser\Process;

use CompileError;
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
    private array $parsed = [];

    public function __construct(string $streamContents, int $options = 0)
    {
        $this->parse($streamContents, $options);
    }

    private function applyItemOptions(Error|TraceEntry $traceItem, int $options): TraceEntry|Error
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

        $typePattern = '/^\\[(?<date>.+?) (?<time>.+?) (?<timezone>.+?)\\] PHP (?<type>[\\w\\s]+?):\\s+(?<message>.*) in (?<file>.+?)(?:on line |:)(?<line>\\d+)$/';
        $typeStartPattern = '/^\\[(?<date>.+?) (?<time>.+?) (?<timezone>.+?)\\] PHP (?<type>[\\w\\s]+?):\\s+(?<message>.*)$/';
        $typeEndPattern = '/ in (?<file>.+?)(?:on line |:)(?<line>\\d+)$/';
        $dateTracePattern = '/^\\[(?<date>.+?) (?<time>.+?) (?<timezone>.+?)\\] PHP\\s+\\d+\\. (?<call>.+?)\\((?<args>.*?)\\) (?<file>.+?):(?<line>\\d+)$/';
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
                $currentError->date = $typeMatches['date'];
                $currentError->time = $typeMatches['time'];
                $currentError->timezone = $typeMatches['timezone'];
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
                $currentError->date = $typeStartMatches['date'];
                $currentError->time = $typeStartMatches['time'];
                $currentError->timezone = $typeStartMatches['timezone'];
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

        if (!empty($sourceError['isException']) && !empty($sourceError->exceptionClass)) {
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
            'trace' => $sourceError->trace,
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
        return match ($type) {
            'Parse error' => ParseError::class,
            'Compile error' => CompileError::class,
            default => ErrorException::class,
        };
    }

    private function mapTypeToSeverity(string $type): int
    {
        return match ($type) {
            'Runtime warning', 'Warning' => E_WARNING,
            'Parse error' => E_PARSE,
            'Runtime notice', 'Notice' => E_NOTICE,
            'Strict Standards' => E_STRICT,
            'Recoverable error' => E_RECOVERABLE_ERROR,
            'Deprecated' => E_DEPRECATED,
            'Core error' => E_CORE_ERROR,
            'Core warning' => E_CORE_WARNING,
            'Compile error' => E_COMPILE_ERROR,
            'Compile warning' => E_COMPILE_WARNING,
            'User error' => E_USER_ERROR,
            'User warning' => E_USER_WARNING,
            'User notice' => E_USER_NOTICE,
            'User deprecated' => E_USER_DEPRECATED,
            default => E_ERROR,
        };
    }
}
