<?php

namespace tad\WPBrowser\Tests;

abstract class BaseTest extends \Codeception\Test\Unit
{
    protected function createWorkDir($workDir)
    {
        if (is_dir($workDir)) {
            \tad\WPBrowser\rrmdir($workDir);
        }

        if (! mkdir($workDir, 0777, true)) {
            throw new \RuntimeException('Failed to create the work directory.');
        }
    }
}
