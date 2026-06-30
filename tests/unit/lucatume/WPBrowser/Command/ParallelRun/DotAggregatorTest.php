<?php

namespace Unit\lucatume\WPBrowser\Command\ParallelRun;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Command\ParallelRun\DotAggregator;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @group fast
 */
class DotAggregatorTest extends Unit
{
    private function aggregator(BufferedOutput &$out = null): DotAggregator
    {
        $out = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, false);
        return new DotAggregator($out);
    }

    private function writeXml(string $dir, string $name, string $body): string
    {
        $path = $dir . '/' . $name;
        file_put_contents($path, $body);
        return $path;
    }

    public function test_forwards_only_test_outcome_chars(): void
    {
        $agg = $this->aggregator($out);

        $agg->ingest(1, 'out', '.FESIWU');
        // Non-vocabulary chars must be dropped.
        $agg->ingest(1, 'out', "Time: 123s\nOK (99 tests, 222 assertions)\n");

        $this->assertSame('.FESIWU', $out->fetch());
    }

    public function test_wraps_output_every_40_chars(): void
    {
        $agg = $this->aggregator($out);
        $agg->ingest(1, 'out', str_repeat('.', 85));

        $text = $out->fetch();
        $lines = explode("\n", $text);

        $this->assertCount(3, $lines);
        $this->assertSame(str_repeat('.', 40), $lines[0]);
        $this->assertSame(str_repeat('.', 40), $lines[1]);
        $this->assertSame(str_repeat('.', 5), $lines[2]);
    }

    public function test_stderr_is_buffered_not_streamed(): void
    {
        $agg = $this->aggregator($out);
        $agg->ingest(1, 'err', "PHP Warning: something\n");

        // stderr must not produce any live dot-stream output.
        $this->assertSame('', $out->fetch());
    }

    public function test_merges_xml_totals_from_testsuite_leaves(): void
    {
        $dir = FS::tmpDir('dot-agg-xml-');
        $agg = $this->aggregator($out);

        $this->writeXml($dir, 'shard-1.xml', '<?xml version="1.0"?>
<testsuites>
  <testsuite name="suite1" tests="10" assertions="20" failures="1" errors="0" skipped="2" time="1.5">
    <testcase classname="A" name="t1"/>
  </testsuite>
</testsuites>');
        $this->writeXml($dir, 'shard-2.xml', '<?xml version="1.0"?>
<testsuites>
  <testsuite name="suite2" tests="5" assertions="8" failures="0" errors="1" skipped="0" time="0.5">
    <testcase classname="B" name="t2"/>
  </testsuite>
</testsuites>');

        $agg->mergeXml($dir . '/shard-1.xml');
        $agg->mergeXml($dir . '/shard-2.xml');
        $agg->flushSummary(2.0);

        $summary = $out->fetch();
        $this->assertStringContainsString('Tests: 15', $summary);
        $this->assertStringContainsString('Assertions: 28', $summary);
        $this->assertStringContainsString('Failures: 1', $summary);
        $this->assertStringContainsString('Errors: 1', $summary);
        $this->assertStringContainsString('Skipped: 2', $summary);
        $this->assertTrue($agg->hasFailures());
    }

    public function test_ignores_parent_testsuite_to_avoid_double_counting(): void
    {
        $dir = FS::tmpDir('dot-agg-nested-');
        $agg = $this->aggregator($out);

        $this->writeXml($dir, 'shard.xml', '<?xml version="1.0"?>
<testsuites>
  <testsuite name="root" tests="4" assertions="4" failures="0" errors="0" skipped="0" time="1.0">
    <testsuite name="leaf1" tests="2" assertions="2" failures="0" errors="0" skipped="0" time="0.5">
      <testcase classname="A" name="t1"/>
      <testcase classname="A" name="t2"/>
    </testsuite>
    <testsuite name="leaf2" tests="2" assertions="2" failures="0" errors="0" skipped="0" time="0.5">
      <testcase classname="B" name="t1"/>
      <testcase classname="B" name="t2"/>
    </testsuite>
  </testsuite>
</testsuites>');

        $agg->mergeXml($dir . '/shard.xml');
        $agg->flushSummary(1.0);

        $this->assertStringContainsString('Tests: 4', $out->fetch());
    }

    public function test_missing_xml_file_is_tolerated(): void
    {
        $agg = $this->aggregator($out);
        $agg->mergeXml('/nonexistent/shard-99.xml');
        $agg->flushSummary(0.0);

        $this->assertStringContainsString('Tests: 0', $out->fetch());
        $this->assertFalse($agg->hasFailures());
    }

    public function test_record_crash_reports_exit_and_stderr_tail(): void
    {
        $agg = $this->aggregator($out);
        $agg->ingest(3, 'err', "some stderr output\nfinal line\n");
        $agg->recordCrash(3, 137);
        $agg->flushSummary(0.0);

        $summary = $out->fetch();
        $this->assertStringContainsString('Worker 3 exited with code 137', $summary);
        $this->assertStringContainsString('final line', $summary);
        $this->assertTrue($agg->hasFailures());
    }

    public function test_clean_summary_prints_OK(): void
    {
        $dir = FS::tmpDir('dot-agg-ok-');
        $agg = $this->aggregator($out);

        $this->writeXml($dir, 'shard.xml', '<?xml version="1.0"?>
<testsuites>
  <testsuite name="s" tests="3" assertions="3" failures="0" errors="0" skipped="0" time="0.1">
    <testcase classname="A" name="t1"/>
  </testsuite>
</testsuites>');
        $agg->mergeXml($dir . '/shard.xml');
        $agg->flushSummary(0.1);

        $summary = $out->fetch();
        $this->assertStringContainsString('OK', $summary);
        $this->assertStringNotContainsString('FAILURES!', $summary);
        $this->assertFalse($agg->hasFailures());
    }

    public function test_xml_failure_nodes_surface_in_summary(): void
    {
        $dir = FS::tmpDir('dot-agg-fail-');
        $agg = $this->aggregator($out);

        $this->writeXml($dir, 'shard.xml', '<?xml version="1.0"?>
<testsuites>
  <testsuite name="s" tests="1" assertions="1" failures="1" errors="0" skipped="0" time="0.1">
    <testcase classname="A\\B" name="test_fails">
      <failure type="AssertionError">expected 1 to equal 2</failure>
    </testcase>
  </testsuite>
</testsuites>');
        $agg->mergeXml($dir . '/shard.xml');
        $agg->flushSummary(0.1);

        $summary = $out->fetch();
        $this->assertStringContainsString('Failures & errors', $summary);
        $this->assertStringContainsString('test_fails', $summary);
        $this->assertStringContainsString('expected 1 to equal 2', $summary);
        $this->assertStringContainsString('FAILURES!', $summary);
    }
}
