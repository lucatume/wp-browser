<?php


namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;

class InstallationTest extends \Codeception\Test\Unit
{
    use UopzFunctions;

    /**
     * It should throw when buiding on non-existing root directory
     *
     * @test
     */
    public function should_throw_when_buiding_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Installation(__DIR__ . '/non-existing-dir');
    }

    /**
     * It should throw when building on non-writable root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_writable_root_directory(): void
    {
        $tmpDir = FS::tmpDir('installation_');
        $this->uopzSetFunctionReturn('is_writable',
            fn(string $dir) => !($dir === $tmpDir . '/') && is_writable($dir),
            true
        );

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_RW);

        new Installation($tmpDir);
    }

    /**
     * It should throw when building on non-readable root directory
     *
     * @test
     */
    public function should_throw_when_building_on_non_readable_root_directory()
    {
        $tmpDir = FS::tmpDir('installation_');
        $this->uopzSetFunctionReturn('is_readable',
            fn(string $dir) => !($dir === $tmpDir . '/') && is_readable($dir),
            true
        );

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_RW);

        new Installation($tmpDir);
    }

    /**
     * It should identify empty installation correctly
     *
     * @test
     */
    public function should_identify_empty_installation_correctly()
    {
        $wpRootDir = FS::tmpDir('installation_');

        $installation = new Installation($wpRootDir);

        $this->assertTrue($installation->isEmpty());
    }

    /**
     * It should read version from files
     *
     * @test
     */
    public function should_read_version_from_files(): void
    {
        $wpRoot = FS::tmpDir();

        Installation::scaffold($wpRoot, '4.9.8');

        $installation = new Installation($wpRoot);

        $this->assertEquals([
            'wpVersion' => '4.9.8',
            'wpDbVersion' => '38590',
            'tinymceVersion' => '4800-20180716',
            'requiredPhpVersion' => '5.2.4',
            'requiredMySqlVersion' => '5.0'
        ], $installation->getVersion()->toArray());
    }

    /**
     * It should read multisite from files
     *
     * @test
     */
    public function should_read_multisite_from_files(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir();

        $installation = Installation::scaffold($wpRoot, '4.9.8', true, false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $installation->isMultisite();

        $installation->configure(new Db($dbName, $dbUser, $dbPassword, $dbHost));

        $installation->convertToMultisite();

        $this->assertTrue($installation->isMultisite());

        $installation = new Installation($wpRoot);

        $this->assertTrue($installation->isMultisite());
    }

    /**
     * It should read the table prefix from files
     *
     * @test
     */
    public function should_read_the_table_prefix_from_files(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $wpRoot = FS::tmpDir();

        $installation = Installation::scaffold($wpRoot, '4.9.8', true, false);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::STATE_SCAFFOLDED);

        $installation->getDb();

        $installation->configure(new Db($dbName, $dbUser, $dbPassword, $dbHost, 'test_'));

        $installation = new Installation($wpRoot);

        $this->assertEquals('test_', $installation->getDb()->getTablePrefix());
    }
}
