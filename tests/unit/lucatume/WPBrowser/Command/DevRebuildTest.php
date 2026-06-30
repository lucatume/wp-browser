<?php

namespace lucatume\WPBrowser\Command;

use lucatume\WPBrowser\Exceptions\RuntimeException;

/**
 * @group fast
 */
class DevRebuildTest extends \Codeception\Test\Unit
{
    /**
     * @test
     */
    public function resolves_relative_docroot_and_sqlite_data_dir(): void
    {
        [$wpRootDir, $dataDir, $url] = DevRebuild::resolveTarget([
            'docroot' => 'tests/_wordpress',
            'port' => 9876,
            'env' => ['DATABASE_TYPE' => 'sqlite', 'DB_ENGINE' => 'sqlite'],
        ], '/project');

        $this->assertEquals('/project/tests/_wordpress', $wpRootDir);
        $this->assertEquals('/project/tests/_wordpress/data', $dataDir);
        $this->assertEquals('http://localhost:9876', $url);
    }

    /**
     * @test
     */
    public function keeps_absolute_docroot_as_is(): void
    {
        [$wpRootDir, $dataDir, $url] = DevRebuild::resolveTarget([
            'docroot' => '/abs/wp',
            'env' => ['DB_ENGINE' => 'sqlite'],
        ], '/project');

        $this->assertEquals('/abs/wp', $wpRootDir);
        $this->assertEquals('/abs/wp/data', $dataDir);
        $this->assertEquals('http://localhost', $url);
    }

    /**
     * @test
     */
    public function rejects_non_sqlite_configuration(): void
    {
        $this->expectException(RuntimeException::class);
        DevRebuild::resolveTarget([
            'docroot' => 'tests/_wordpress',
            'env' => ['DATABASE_TYPE' => 'mysql'],
        ], '/project');
    }

    /**
     * @test
     */
    public function rejects_missing_built_in_server_configuration(): void
    {
        $this->expectException(RuntimeException::class);
        DevRebuild::resolveTarget([], '/project');
    }

    /**
     * @test
     */
    public function resolves_version_with_option_taking_precedence(): void
    {
        $this->assertEquals('6.8.1', DevRebuild::resolveVersion('6.8.1', '6.7'));
    }

    /**
     * @test
     */
    public function resolves_version_from_environment_when_no_option(): void
    {
        $this->assertEquals('6.7', DevRebuild::resolveVersion(null, '6.7'));
        $this->assertEquals('6.7', DevRebuild::resolveVersion('', '6.7'));
    }

    /**
     * @test
     */
    public function resolves_version_to_latest_when_nothing_set(): void
    {
        $this->assertEquals('latest', DevRebuild::resolveVersion(null, false));
        $this->assertEquals('latest', DevRebuild::resolveVersion(null, ''));
        $this->assertEquals('latest', DevRebuild::resolveVersion('', null));
    }
}
