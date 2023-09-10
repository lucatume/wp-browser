<?php


namespace lucatume\WPBrowser\WordPress;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class VersionTest extends Unit
{
    use TmpFilesCleanup;

    /**
     * It should throw when built on non-existing root directory
     *
     * @test
     */
    public function should_throw_when_built_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new Version('/non-existing-dir');
    }

    /**
     * It should throw if the version file is missing
     *
     * @test
     */
    public function should_throw_if_the_version_file_is_missing(): void
    {
        $wpRootDir = FS::tmpDir('version_', ['wp-includes' => []]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::VERSION_FILE_NOT_FOUND);

        new Version($wpRootDir);
    }

    /**
     * It should throw if version file does not contain any information
     *
     * @test
     */
    public function should_throw_if_version_file_does_not_contain_any_information(): void
    {
        $wpRootDir = FS::tmpDir('version_', [
            'wp-includes' => [
                'version.php' => '<?php echo "Hello there!";'
            ]
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::VERSION_FILE_MISSING_INFO);

        new Version($wpRootDir);
    }

    /**
     * It should throw if version file does not contain all expected information
     *
     * @test
     */
    public function should_throw_if_version_file_does_not_contain_all_expected_information(): void
    {
        // Missing $tinymceVersion.
        $versionFileContents = <<< PHP
        \$wpVersion = '1.2.3';
        \$wpDbVersion = '1.2.3';
        \$requiredPhpVersion = '1.2.3';
        \$requiredMySqlVersion = '1.2.3';
PHP;
        $wpRootDir = FS::tmpDir('version_', [
            'wp-includes' => [
                'version.php' => $versionFileContents
            ]
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::VERSION_FILE_MISSING_INFO);

        new Version($wpRootDir);
    }

    /**
     * It should return the version information from files
     *
     * @test
     */
    public function should_return_the_version_information_from_files(): void
    {
        $wpRootDir = FS::tmpDir('version_');
        Installation::scaffold($wpRootDir, '5.5.1');

        $version = new Version($wpRootDir);

        $this->assertEquals([
            'wpVersion' => '5.5.1',
            'wpDbVersion' => '48748',
            'tinymceVersion' => '49100-20200624',
            'requiredPhpVersion' => '5.6.20',
            'requiredMySqlVersion' => '5.0'
        ], $version->toArray());
    }
}
