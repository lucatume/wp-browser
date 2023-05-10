<?php

namespace lucatume\WPBrowser\Process;

use CompileError;
use ErrorException;
use lucatume\WPBrowser\Utils\Property;
use ParseError;
use ReflectionClass;
use ReflectionException;
use Throwable;

class StderrStream
{
    public const RELATIVE_PATHNAMES = 1;

    /**
     * @var array<int,mixed>
     */
    private array $parsed = [];

    public function __construct(string $streamContents, int $options = 0)
    {
        $this->parse($streamContents, $options);
    }

    /**
     * @param array<string,mixed> $traceItem
     * @return array<string,mixed>
     */
    private function applyOptions(array $traceItem, int $options): array
    {
        if ($options & self::RELATIVE_PATHNAMES) {
            foreach (['file', 'args'] as $key) {
                if (!isset($traceItem[$key])) {
                    continue;
                }
                $traceItem[$key] = str_replace(getcwd(), '', $traceItem[$key]);
            }
        }
        return $traceItem;
    }

    /**
     * @param array{date: string, time: string, timezone: string, type: string, message: string, file: string, line: int, trace: array<int,array<string,mixed>>} $currentError
     * @return array{date: string, time: string, timezone: string, type: string, message: string, file: string, line: int, trace: array<int,array<string,mixed>>}
     */
    private function formatInvertedTraceError(array $currentError, bool $isNumericStackTrace): array
    {
        if ($isNumericStackTrace) {
            // Reverse the trace to match the order of the date trace.
            $currentError['trace'] = array_reverse($currentError['trace']);
            // Add a trace entry for the error itself.
            $currentError['trace'][] = [
                'date' => $currentError['date'],
                'time' => $currentError['time'],
                'timezone' => $currentError['timezone'],
                'call' => 'n/a',
                'args' => 'n/a',
                'file' => $currentError['file'],
                'line' => $currentError['line'],
            ];
        }

        return $currentError;
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

                // Start a new error
                $currentError = [
                    'isException' => false,
                    'exceptionClass' => null,
                ];
                $isNumericStackTrace = false;
                foreach (['date', 'time', 'timezone', 'type', 'message', 'file', 'line'] as $key) {
                    $currentError[$key] = $typeMatches[$key];
                }

                if (preg_match($uncaughtExceptionPattern, $currentError['message'], $uncaughtExceptionMatches)) {
                    $currentError['isException'] = true;
                    $currentError['exceptionClass'] = $uncaughtExceptionMatches['exceptionClass'];
                }

                $currentError = $this->applyOptions($currentError, $options);

                $currentError['trace'] = [];

                continue;
            }

            if (preg_match($typeStartPattern, $line, $typeStartMatches)) {
                if ($currentError !== null) {
                    $parsed[] = $this->formatInvertedTraceError($currentError, $isNumericStackTrace);
                }

                // Start a new error
                $currentError = [
                    'isException' => false,
                    'exceptionClass' => null,
                ];
                $isNumericStackTrace = false;
                foreach (['date', 'time', 'timezone', 'type', 'message'] as $key) {
                    $currentError[$key] = $typeStartMatches[$key];
                }

                // Keep ingesting until the line matches with $typeEndPattern
                while (isset($lines[$i]) && !preg_match($typeEndPattern, $lines[$i], $typeEndMatches)) {
                    $currentError['message'] .= PHP_EOL . $lines[$i++];
                }

                if (preg_match($uncaughtExceptionPattern, $currentError['message'], $uncaughtExceptionMatches)) {
                    $currentError['isException'] = true;
                    $currentError['exceptionClass'] = $uncaughtExceptionMatches['exceptionClass'];
                }

                if (!isset($typeEndMatches['file'], $typeEndMatches['line'])) {
                    $currentError['file'] = 'n/a';
                    $currentError['line'] = 'n/a';
                } else {
                    $currentError['file'] = $typeEndMatches['file'];
                    $currentError['line'] = $typeEndMatches['line'];
                }

                $currentError = $this->applyOptions($currentError, $options);

                $currentError['trace'] = [];

                continue;
            }

            if (preg_match($dateTracePattern, $line, $dateTraceMatches)) {
                $traceItem = [];
                foreach (['date', 'time', 'timezone', 'call', 'args', 'file', 'line'] as $key) {
                    $traceItem[$key] = $dateTraceMatches[$key];
                }

                $traceItem = $this->applyOptions($traceItem, $options);

                $currentError['trace'][] = $traceItem;
                continue;
            }

            if (!preg_match($numberTracePattern, $line)) {
                continue;
            }

            $isNumericStackTrace = true;

            if (preg_match($numberTraceInlinePattern, $line, $numberTraceInlineMatches)) {
                $traceItem = [
                    'date' => $currentError['date'],
                    'time' => $currentError['time'],
                    'timezone' => $currentError['timezone']
                ];
                foreach (['call', 'args', 'file', 'line'] as $key) {
                    $traceItem[$key] = $numberTraceInlineMatches[$key];
                }

                $traceItem = $this->applyOptions($traceItem, $options);
                $currentError['trace'][] = $traceItem;
                continue;
            }

            if (preg_match($closureTracePattern, $line, $closureTraceMatches)) {
                $traceItem = [
                    'date' => $currentError['date'],
                    'time' => $currentError['time'],
                    'timezone' => $currentError['timezone'],
                    'file' => 'n/a',
                    'line' => 'n/a',
                    'call' => 'n/a',
                    'args' => 'n/a',
                ];
                $closureLines = $closureTraceMatches['closure'];
                while (
                    isset($lines[$i + 1]) &&
                    !preg_match($closureFinalLinePattern, $lines[$i + 1], $closureFinalLineMatches)
                ) {
                    $closureLines .= PHP_EOL . $lines[$i + 1];
                    $i++;
                }

                if (!isset($closureFinalLineMatches)) {
                    continue;
                }

                $traceItem['file'] = $closureLines . PHP_EOL . $lines[$i];
                $traceItem['line'] = $closureFinalLineMatches['line'];
                $traceItem['call'] = $closureFinalLineMatches['call'];
                $traceItem['args'] = $closureFinalLineMatches['args'];

                $currentError['trace'][] = $traceItem;
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

        if ($sourceError['isException']) {
            $throwableClass = $sourceError['exceptionClass'];
        } else {
            $throwableClass = $this->mapTypeToThrowableClass($sourceError['type']);
        }

        if ($throwableClass === ErrorException::class) {
            return new $throwableClass(
                $sourceError['message'],
                0,
                $this->mapTypeToSeverity($sourceError['type']),
                $sourceError['file'],
                $sourceError['line']);
        }

        $throwable = (new ReflectionClass($throwableClass))->newInstanceWithoutConstructor();

        Property::setPrivateProperties($throwable, [
            'message' => $sourceError['message'],
            'file' => $sourceError['file'],
            'line' => $sourceError['line'],
            'trace' => $sourceError['trace'],
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
