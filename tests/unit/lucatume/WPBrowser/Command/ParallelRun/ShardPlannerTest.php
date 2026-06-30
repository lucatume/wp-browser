<?php

namespace Unit\lucatume\WPBrowser\Command\ParallelRun;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Command\ParallelRun\ShardPlanner;
use lucatume\WPBrowser\Utils\Filesystem as FS;

/**
 * @group fast
 */
class ShardPlannerTest extends Unit
{
    public function test_from_report_returns_empty_when_file_missing(): void
    {
        $planner = new ShardPlanner();
        $this->assertSame([], $planner->fromReport('/nonexistent/path/report.xml'));
    }

    public function test_from_report_sums_time_per_file(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="unit" tests="3" time="3.5">
    <testcase name="a" file="/abs/A.php" time="1.0"/>
    <testcase name="b" file="/abs/A.php" time="1.5"/>
    <testcase name="c" file="/abs/B.php" time="1.0"/>
  </testsuite>
</testsuites>
XML;
        $dir = FS::tmpDir('shardplanner_');
        $path = $dir . '/report.xml';
        file_put_contents($path, $xml);

        $planner = new ShardPlanner();
        $weights = $planner->fromReport($path);

        $this->assertEqualsWithDelta(2.5, $weights['/abs/A.php'], 1e-6);
        $this->assertEqualsWithDelta(1.0, $weights['/abs/B.php'], 1e-6);
    }

    public function test_from_tags_weighs_by_group_annotations(): void
    {
        $dir = FS::tmpDir('shardplanner_tags_');

        $fastFile = $dir . '/FastTest.php';
        file_put_contents($fastFile, <<<'PHP'
<?php
/**
 * @group fast
 */
class FastTest {
    public function test_one() {}
    public function test_two() {}
}
PHP
);

        $slowFile = $dir . '/SlowTest.php';
        file_put_contents($slowFile, <<<'PHP'
<?php
/**
 * @group slow
 */
class SlowTest {
    public function test_one() {}
}
PHP
);

        $mixedFile = $dir . '/MixedTest.php';
        file_put_contents($mixedFile, <<<'PHP'
<?php
class MixedTest {
    /** @group fast */
    public function test_quick() {}
    public function test_normal() {}
    /** @group slow */
    public function test_big() {}
}
PHP
);

        $planner = new ShardPlanner();
        $weights = $planner->fromTags($dir);

        $this->assertArrayHasKey(realpath($fastFile), $weights);
        $this->assertArrayHasKey(realpath($slowFile), $weights);
        $this->assertArrayHasKey(realpath($mixedFile), $weights);

        $this->assertEqualsWithDelta(2 * ShardPlanner::WEIGHT_FAST, $weights[realpath($fastFile)], 1e-6);
        $this->assertEqualsWithDelta(ShardPlanner::WEIGHT_SLOW, $weights[realpath($slowFile)], 1e-6);
        $this->assertEqualsWithDelta(
            ShardPlanner::WEIGHT_FAST + ShardPlanner::WEIGHT_NORMAL + ShardPlanner::WEIGHT_SLOW,
            $weights[realpath($mixedFile)],
            1e-6
        );
    }

    public function test_from_tags_returns_empty_for_missing_dir(): void
    {
        $planner = new ShardPlanner();
        $this->assertSame([], $planner->fromTags('/definitely/not/here'));
    }

    public function test_plan_returns_empty_shards_for_empty_input(): void
    {
        $planner = new ShardPlanner();
        $shards = $planner->plan([], 3);
        $this->assertCount(3, $shards);
        foreach ($shards as $shard) {
            $this->assertSame([], $shard['files']);
            $this->assertSame(0.0, $shard['weight']);
        }
    }

    public function test_plan_packs_within_four_thirds_of_optimal(): void
    {
        // Optimal for 3 shards: total 60, ideal 20 each.
        $weights = [
            'a' => 10.0, 'b' => 10.0, 'c' => 8.0, 'd' => 7.0,
            'e' => 6.0,  'f' => 5.0,  'g' => 5.0, 'h' => 4.0,
            'i' => 3.0,  'j' => 2.0,
        ];
        $planner = new ShardPlanner();
        $shards = $planner->plan($weights, 3);

        $total = 0.0;
        $max = 0.0;
        $assigned = [];
        foreach ($shards as $shard) {
            $total += $shard['weight'];
            $max = max($max, $shard['weight']);
            foreach ($shard['files'] as $f) {
                $assigned[] = $f;
            }
        }
        $this->assertEqualsWithDelta(60.0, $total, 1e-6);
        sort($assigned);
        $this->assertSame(array_keys($weights), $assigned);

        $optimal = $total / 3;
        $this->assertLessThanOrEqual($optimal * 4 / 3 + 1e-6, $max);
    }

    public function test_plan_assigns_heaviest_first_to_different_shards(): void
    {
        $planner = new ShardPlanner();
        $shards = $planner->plan(['a' => 10.0, 'b' => 9.0, 'c' => 1.0], 2);

        $this->assertSame(['a'], $shards[1]['files']);
        $this->assertSame(['b', 'c'], $shards[2]['files']);
        $this->assertEqualsWithDelta(10.0, $shards[1]['weight'], 1e-6);
        $this->assertEqualsWithDelta(10.0, $shards[2]['weight'], 1e-6);
    }

    public function test_plan_handles_more_workers_than_files(): void
    {
        $planner = new ShardPlanner();
        $shards = $planner->plan(['a' => 5.0, 'b' => 3.0], 5);

        $this->assertCount(5, $shards);
        $allFiles = [];
        foreach ($shards as $shard) {
            foreach ($shard['files'] as $f) {
                $allFiles[] = $f;
            }
        }
        sort($allFiles);
        $this->assertSame(['a', 'b'], $allFiles);
    }

    public function test_needs_resources_detects_server_tag_at_method_level(): void
    {
        $tmpDir = FS::tmpDir('shard-planner-');
        $file   = $tmpDir . '/AlphaTest.php';
        file_put_contents($file, <<<'PHP'
<?php
class AlphaTest extends \PHPUnit\Framework\TestCase {
    /**
     * @group requires-server
     */
    public function test_hits_server(): void {}
    public function test_plain(): void {}
}
PHP
);

        $planner = new ShardPlanner();

        $this->assertSame(
            ['server' => true, 'chromedriver' => false, 'mysql' => false],
            $planner->needsResources([$file])
        );
    }

    public function test_needs_resources_detects_chromedriver_tag_at_class_level(): void
    {
        $tmpDir = FS::tmpDir('shard-planner-');
        $file   = $tmpDir . '/BravoTest.php';
        file_put_contents($file, <<<'PHP'
<?php
/**
 * @group requires-chromedriver
 */
class BravoTest extends \PHPUnit\Framework\TestCase {
    public function test_a(): void {}
}
PHP
);

        $planner = new ShardPlanner();

        $this->assertSame(
            ['server' => false, 'chromedriver' => true, 'mysql' => false],
            $planner->needsResources([$file])
        );
    }

    public function test_needs_resources_returns_all_false_for_empty_file_list(): void
    {
        $planner = new ShardPlanner();

        $this->assertSame(
            ['server' => false, 'chromedriver' => false, 'mysql' => false],
            $planner->needsResources([])
        );
    }

    public function test_needs_resources_unions_across_files(): void
    {
        $tmpDir = FS::tmpDir('shard-planner-');
        $f1 = $tmpDir . '/OneTest.php';
        $f2 = $tmpDir . '/TwoTest.php';
        file_put_contents($f1, '<?php /** @group requires-server */ class OneTest {}');
        file_put_contents($f2, '<?php /** @group requires-mysql-server */ class TwoTest {}');

        $planner = new ShardPlanner();

        $this->assertSame(
            ['server' => true, 'chromedriver' => false, 'mysql' => true],
            $planner->needsResources([$f1, $f2])
        );
    }

    public function test_needs_resources_ignores_group_tokens_outside_docblocks(): void
    {
        $tmpDir = FS::tmpDir('shard-planner-');
        $file   = $tmpDir . '/DecoyTest.php';
        file_put_contents($file, <<<'PHP'
<?php
class DecoyTest extends \PHPUnit\Framework\TestCase {
    public function test_string_literal_does_not_trigger(): void
    {
        $tag = '@group requires-server';
        // @group requires-chromedriver — this is a line comment, not a docblock
        $_ = $tag;
    }
}
PHP
);

        $planner = new ShardPlanner();

        $this->assertSame(
            ['server' => false, 'chromedriver' => false, 'mysql' => false],
            $planner->needsResources([$file])
        );
    }
}
