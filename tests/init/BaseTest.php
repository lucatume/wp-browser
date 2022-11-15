<?php

namespace lucatume\WPBrowser\Tests;

use lucatume\WPBrowser\Utils\Filesystem as FS;

abstract class BaseTest extends \Codeception\Test\Unit
{
    protected function createWorkDir($workDir): void
    {
        if (is_dir($workDir)) {
            FS::rrmdir($workDir);
        }

        if (!mkdir($workDir, 0777, true)) {
            throw new \RuntimeException('Failed to create the work directory.');
        }
    }
}
