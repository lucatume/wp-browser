<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class DotAggregator
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;
    private const DOT_CHARS = ['.', 'F', 'E', 'S', 'I', 'W', 'U'];
    private const WIDTH = 40;

    /**
     * @var int
     */
    private $printed = 0;
    /** @var array<int,string> */
    private $stderrBuf = [];
    /**
     * @var int
     */
    private $totalTests = 0;
    /**
     * @var int
     */
    private $totalAssertions = 0;
    /**
     * @var int
     */
    private $totalFailures = 0;
    /**
     * @var int
     */
    private $totalErrors = 0;
    /**
     * @var int
     */
    private $totalSkipped = 0;
    /**
     * @var float
     */
    private $totalWorkerTime = 0.0;
    /** @var array<int,string> */
    private $failureBlocks = [];
    /** @var array<int,string> */
    private $crashedWorkers = [];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function ingest(int $worker, string $type, string $data): void
    {
        if ($type === 'err') {
            $this->stderrBuf[$worker] = ($this->stderrBuf[$worker] ?? '') . $data;
            return;
        }

        $chars = str_split($data);
        foreach ($chars as $ch) {
            if (!in_array($ch, self::DOT_CHARS, true)) {
                continue;
            }
            if ($this->printed > 0 && $this->printed % self::WIDTH === 0) {
                $this->output->writeln('');
            }
            switch ($ch) {
                case 'F':
                case 'E':
                    $style = "<error>{$ch}</error>";
                    break;
                case 'S':
                case 'I':
                case 'W':
                    $style = "<comment>{$ch}</comment>";
                    break;
                default:
                    $style = $ch;
                    break;
            }
            $this->output->write($style);
            $this->printed++;
        }
    }

    public function recordCrash(int $worker, int $exitCode): void
    {
        $tail = substr(trim($this->stderrBuf[$worker] ?? ''), -500);
        $this->crashedWorkers[$worker] = sprintf(
            "Worker %d exited with code %d.\n%s",
            $worker,
            $exitCode,
            $tail === '' ? '(no stderr)' : $tail
        );
    }

    public function mergeXml(string $xmlFile): void
    {
        if (!is_file($xmlFile)) {
            return;
        }
        $content = file_get_contents($xmlFile);
        if ($content === false || $content === '') {
            return;
        }
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($content);
        libxml_use_internal_errors($prev);
        if (!$doc instanceof \SimpleXMLElement) {
            return;
        }

        foreach ($doc->xpath('//testsuite[@name]') ?: [] as $suite) {
            $attrs = $suite->attributes();
            if ($attrs === null) {
                continue;
            }
            if (isset($suite->testsuite) && count($suite->testsuite) > 0) {
                continue;
            }
            $this->totalTests      += (int)($attrs['tests']      ?? 0);
            $this->totalAssertions += (int)($attrs['assertions'] ?? 0);
            $this->totalFailures   += (int)($attrs['failures']   ?? 0);
            $this->totalErrors     += (int)($attrs['errors']     ?? 0);
            $this->totalSkipped    += (int)($attrs['skipped']    ?? 0);
            $this->totalWorkerTime += (float)($attrs['time']     ?? 0);
        }

        foreach ($doc->xpath('//testcase/failure | //testcase/error') ?: [] as $node) {
            $case = $node->xpath('..')[0] ?? null;
            $caseAttrs = ($nullsafeVariable1 = $case) ? $nullsafeVariable1->attributes() : null;
            $name = $caseAttrs !== null
                ? sprintf('%s::%s', (string)($caseAttrs['classname'] ?? ''), (string)($caseAttrs['name'] ?? ''))
                : 'unknown';
            $this->failureBlocks[] = sprintf("%s\n%s", $name, (string)$node);
        }
    }

    public function flushSummary(float $wallClock): void
    {
        $this->output->writeln('');
        $this->output->writeln('');

        foreach ($this->crashedWorkers as $msg) {
            $this->output->writeln("<error>{$msg}</error>");
        }

        if ($this->failureBlocks !== []) {
            $this->output->writeln('<comment>--- Failures & errors ---</comment>');
            foreach ($this->failureBlocks as $i => $block) {
                $this->output->writeln(sprintf("\n%d) %s", $i + 1, $block));
            }
            $this->output->writeln('');
        }

        $this->output->writeln(sprintf(
            'Time: %.2fs (wall), %.2fs (worker sum), Peak memory: %s',
            $wallClock,
            $this->totalWorkerTime,
            $this->formatMemory(memory_get_peak_usage(true))
        ));

        $style = $this->hasFailures() ? 'error' : 'info';
        $header = $this->hasFailures() ? 'FAILURES!' : 'OK';
        $this->output->writeln(sprintf('<%s>%s</%s>', $style, $header, $style));
        $this->output->writeln(sprintf(
            'Tests: %d, Assertions: %d, Failures: %d, Errors: %d, Skipped: %d',
            $this->totalTests,
            $this->totalAssertions,
            $this->totalFailures,
            $this->totalErrors,
            $this->totalSkipped
        ));
    }

    public function hasFailures(): bool
    {
        return $this->totalFailures > 0
            || $this->totalErrors > 0
            || $this->crashedWorkers !== [];
    }

    private function formatMemory(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $v = (float)$bytes;
        while ($v >= 1024 && $i < count($units) - 1) {
            $v /= 1024;
            $i++;
        }
        return sprintf('%.2f%s', $v, $units[$i]);
    }
}
