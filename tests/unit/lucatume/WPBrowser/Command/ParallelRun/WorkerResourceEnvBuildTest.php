<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\Filesystem;

class WorkerResourceEnvBuildTest extends Unit
{
    public function test_build_sets_flags_per_shard_contents(): void
    {
        $tmpDir = Filesystem::tmpDir('par-resource-');
        $serverFile = $tmpDir . '/ServerTest.php';
        $plainFile  = $tmpDir . '/PlainTest.php';
        file_put_contents($serverFile, <<<'PHP'
<?php
class ServerTest {
    /** @group requires-server */
    public function test_a(): void {}
}
PHP
);
        file_put_contents($plainFile, <<<'PHP'
<?php
class PlainTest {
    public function test_a(): void {}
}
PHP
);

        $shards = [
            1 => ['files' => [$serverFile], 'weight' => 1.0],
            2 => ['files' => [$plainFile],  'weight' => 1.0],
        ];

        $envs = WorkerResourceEnv::build($shards);

        $this->assertSame('1', $envs[1][WorkerResourceEnv::ENV_NEEDS_SERVER]);
        $this->assertSame('0', $envs[1][WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER]);
        $this->assertSame('0', $envs[2][WorkerResourceEnv::ENV_NEEDS_SERVER]);
        $this->assertSame('0', $envs[2][WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER]);
    }

    public function test_build_returns_empty_when_given_no_shard_assignments(): void
    {
        $this->assertSame([], WorkerResourceEnv::build([]));
    }

    public function test_build_with_files_missing_marks_everything_off(): void
    {
        $shards = [
            1 => ['files' => [], 'weight' => 0.0],
            2 => ['files' => [], 'weight' => 0.0],
        ];

        $envs = WorkerResourceEnv::build($shards);

        foreach ([1, 2] as $i) {
            $this->assertSame('0', $envs[$i][WorkerResourceEnv::ENV_NEEDS_SERVER]);
            $this->assertSame('0', $envs[$i][WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER]);
        }
    }

    public function test_build_chromedriver_flag_reflects_tag(): void
    {
        $tmpDir = Filesystem::tmpDir('par-resource-');
        $cdFile = $tmpDir . '/BrowserTest.php';
        file_put_contents($cdFile, <<<'PHP'
<?php
/** @group requires-chromedriver */
class BrowserTest {
    public function test_a(): void {}
}
PHP
);

        $shards = [1 => ['files' => [$cdFile], 'weight' => 1.0]];

        $envs = WorkerResourceEnv::build($shards);

        $this->assertSame('0', $envs[1][WorkerResourceEnv::ENV_NEEDS_SERVER]);
        $this->assertSame('1', $envs[1][WorkerResourceEnv::ENV_NEEDS_CHROMEDRIVER]);
    }
}
