<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

use Codeception\Test\Unit;

class WorkerResourceEnvTest extends Unit
{
    protected function _before(): void
    {
        unset($_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER], $_ENV[WorkerResourceEnv::ENV_NEEDS_SERVER]);
        putenv(WorkerResourceEnv::ENV_NEEDS_SERVER);
    }

    protected function _after(): void
    {
        unset($_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER], $_ENV[WorkerResourceEnv::ENV_NEEDS_SERVER]);
        putenv(WorkerResourceEnv::ENV_NEEDS_SERVER);
    }

    public function test_is_disabled_returns_false_when_env_is_unset(): void
    {
        $this->assertFalse(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));
    }

    public function test_is_disabled_returns_true_only_for_exact_zero_string(): void
    {
        $_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER] = '0';
        $this->assertTrue(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));

        $_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER] = '1';
        $this->assertFalse(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));

        $_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER] = 'false';
        $this->assertFalse(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));

        $_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER] = '';
        $this->assertFalse(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));
    }

    public function test_is_disabled_falls_back_through_server_env_getenv(): void
    {
        $_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER] = '1';
        $_ENV[WorkerResourceEnv::ENV_NEEDS_SERVER]    = '0';
        $this->assertFalse(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));
        unset($_SERVER[WorkerResourceEnv::ENV_NEEDS_SERVER]);

        putenv(WorkerResourceEnv::ENV_NEEDS_SERVER . '=1');
        $_ENV[WorkerResourceEnv::ENV_NEEDS_SERVER] = '0';
        $this->assertTrue(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));
        unset($_ENV[WorkerResourceEnv::ENV_NEEDS_SERVER]);

        putenv(WorkerResourceEnv::ENV_NEEDS_SERVER . '=0');
        $this->assertTrue(WorkerResourceEnv::isDisabled(WorkerResourceEnv::ENV_NEEDS_SERVER));
    }
}
