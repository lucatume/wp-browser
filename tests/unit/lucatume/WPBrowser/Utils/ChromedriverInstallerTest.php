<?php


namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;

class ChromedriverInstallerTest extends \Codeception\Test\Unit
{
    use UopzFunctions;
    use TmpFilesCleanup;

    /**
     * It should throw if detected platform is not supported
     *
     * @test
     */
    public function should_throw_if_detected_platform_is_not_supported(): void
    {
        $this->setFunctionReturn('php_uname', 'Lorem');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_DETECT_PLATFORM);

        new ChromedriverInstaller();
    }

    /**
     * It should throw if specified platform is not supported
     *
     * @test
     */
    public function should_throw_if_specified_platform_is_not_supported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_UNSUPPORTED_PLATFORM);

        new ChromedriverInstaller('1.2.3.4', 'Lorem86');
    }

    /**
     * It should throw if specified binary cannot be found
     *
     * @test
     */
    public function should_throw_if_specified_binary_cannot_be_found(): void
    {
        $this->setFunctionReturn('is_file', function (string $file) {
            return strpos($file, 'chrome') === false;
        }, true);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_INVALID_BINARY);

        new ChromedriverInstaller(null, 'win32', '/path/to/chrome.exe');
    }

    /**
     * @return string[]
     */
    public function platforms_provider(): array
    {
        return [
            'win32' => ['win32', 'chrome'],
            'win64' => ['win64', 'chrome'],
            'linux64' => ['linux64', 'chrom'],
            'mac-x64' => ['mac-x64', 'Chrome'],
            'mac-arm64' => ['mac-arm64', 'Chrome'],
        ];
    }

    /**
     * It should throw if binary cannot be found in default paths for platform
     *
     * @test
     * @dataProvider platforms_provider
     */
    public function should_throw_if_binary_cannot_be_found_in_default_paths_for_platform(
        string $platform,
        string $binNamePattern
    ): void {
        $isNotAnExecutableFile = function (string $file) use ($binNamePattern) {
            return strpos($file, $binNamePattern) === false;
        };
        $this->setFunctionReturn('is_file', $isNotAnExecutableFile, true);
        $this->setFunctionReturn('is_executable', $isNotAnExecutableFile, true);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_INVALID_BINARY);

        new ChromedriverInstaller(null, $platform);
    }

    /**
     * It should throw if binary cannot be executed
     *
     * @test
     */
    public function should_throw_if_binary_cannot_be_executed(): void
    {
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return strpos($file, 'chrome') === false && is_executable($file);
        }, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_INVALID_BINARY);

        new ChromedriverInstaller('1.2.3.4', 'win32');
    }

    /**
     * It should throw if specified binary is not valid
     *
     * @test
     */
    public function should_throw_if_specified_binary_is_not_valid(): void
    {
        $this->setFunctionReturn('is_executable', function (string $file): bool {
            return strpos($file, 'Chromium') === false && is_executable($file);
        }, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_INVALID_BINARY);

        new ChromedriverInstaller(
            '1.2.3.4',
            'mac-arm64',
            '/Applications/Chromium.app/Contents/MacOS/Chromium'
        );
    }

    /**
     * It should throw if version from binary is not a string
     *
     * @test
     */
    public function should_throw_if_version_from_binary_is_not_a_string(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_VERSION_NOT_STRING);

        new ChromedriverInstaller(
            null,
            null,
            codecept_data_dir('bins/chrome-version-not-string')
        );
    }

    /**
     * It should throw if version from binary has not correct format
     *
     * @test
     */
    public function should_throw_if_version_from_binary_has_not_correct_format(): void
    {
        $this->setFunctionReturn('exec', 'Could not start Google Chrome.');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_INVALID_VERSION_FORMAT);

        new ChromedriverInstaller(null, null, codecept_data_dir('bins/chrome-version-wrong-format'));
    }

    /**
     * It should throw if specified version is not valid
     *
     * @test
     */
    public function should_throw_if_specified_version_is_not_valid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_INVALID_VERSION_FORMAT);

        new ChromedriverInstaller('lorem.dolor.sit.amet', null, codecept_data_dir('bins/chrome-mock'));
    }

    /**
     * It should pick up version and platform correctly
     *
     * @test
     */
    public function should_pick_up_version_and_platform_correctly(): void
    {
        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));

        $this->assertEquals('116', $ci->getVersion());
        $this->assertEquals(codecept_data_dir('bins/chrome-mock'), $ci->getBinary());
        $this->assertEquals('linux64', $ci->getPlatform());
    }

    /**
     * It should throw if trying to install to non-existing directory
     *
     * @test
     */
    public function should_throw_if_trying_to_install_to_non_existing_directory(): void
    {
        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_DESTINATION_NOT_DIR);

        $ci->install(__DIR__ . '/non-existing-dir');
    }

    /**
     * It should throw if it cannot get milestone downloads
     *
     * @test
     */
    public function should_throw_if_it_cannot_get_milestone_downloads(): void
    {
        $this->setFunctionReturn('file_get_contents', function (string $file) {
            return strpos($file, 'chrome-for-testing') !== false ? false : file_get_contents($file);
        }, true);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));
        $ci->useEnvZipFile(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_FETCH_MILESTONE_DOWNLOADS);

        $ci->install(__DIR__);
    }

    /**
     * It should throw if response is not valid JSON
     *
     * @test
     */
    public function should_throw_if_response_is_not_valid_json(): void
    {
        $this->setFunctionReturn('file_get_contents', function (string $file) {
            return strpos($file, 'chrome-for-testing') !== false ? '{}' : file_get_contents($file);
        }, true);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));
        $ci->useEnvZipFile(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_DECODE_MILESTONE_DOWNLOADS);
        $this->expectExceptionMessage("Failed to find a version of Chromedriver to download for your platform and " .
            "Chrome combination.\nTry upgrading Chrome and making sure it is executable from one of the expected " .
            "locations for your platform (linux64): chromium, google-chrome");

        $ci->install(__DIR__);
    }

    /**
     * It should throw if download URL for Chrome version cannot be found in milestone downloads
     *
     * @test
     */
    public function should_throw_if_download_url_for_chrome_version_cannot_be_found_in_milestone_downloads(): void
    {
        $this->setFunctionReturn('file_get_contents', function (string $file) {
            return strpos($file, 'chrome-for-testing') !== false ?
                '{"milestones":{"116": {"downloads":{"chrome":{},"chromedriver":{}}}}}'
                : file_get_contents($file);
        }, true);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));
        $ci->useEnvZipFile(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_DOWNLOAD_URL_NOT_FOUND);

        $ci->install(__DIR__);
    }

    /**
     * It should throw if existing zip file cannot be removed
     *
     * @test
     */
    public function should_throw_if_existing_zip_file_cannot_be_removed(): void
    {
        $this->setFunctionReturn('sys_get_temp_dir', codecept_output_dir());
        $this->setFunctionReturn('unlink', function (string $file): bool {
            return preg_match('~chromedriver\\.zip$~', $file) ? false : unlink($file);
        }, true);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));
        $ci->useEnvZipFile(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_REMOVE_EXISTING_ZIP_FILE);

        $ci->install(__DIR__);
    }

    /**
     * It should throw if existing binary cannot be removed
     *
     * @test
     */
    public function should_throw_if_existing_binary_cannot_be_removed(): void
    {
        $dir = Filesystem::tmpDir('chromedriver_installer_', ['chromedriver' => '']);
        $this->setFunctionReturn('sys_get_temp_dir', codecept_output_dir());
        $this->setFunctionReturn('unlink', function (string $file) use ($dir): bool {
            return $file === $dir . '/chromedriver' ? false : unlink($file);
        }, true);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_REMOVE_EXISTING_BINARY);

        $ci->install($dir);
    }

    /**
     * It should throw if new binary cannot be made executable
     *
     * @test
     */
    public function should_throw_if_new_binary_cannot_be_made_executable(): void
    {
        $dir = Filesystem::tmpDir('chromedriver_installer_');
        $this->setFunctionReturn('sys_get_temp_dir', codecept_output_dir());
        $this->setFunctionReturn('chmod', false);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(ChromedriverInstaller::ERR_BINARY_CHMOD);

        $ci->install($dir);
    }

    /**
     * It should correctly install chromedriver
     *
     * @test
     */
    public function should_correctly_install_chromedriver(): void
    {
        $tmpDir = Filesystem::tmpDir('chromedriver_installer_tmp_');
        $dir = Filesystem::tmpDir('chromedriver_installer_');
        $this->setFunctionReturn('sys_get_temp_dir', $tmpDir);

        $ci = new ChromedriverInstaller(null, 'linux64', codecept_data_dir('bins/chrome-mock'));

        $executablePath = $ci->install($dir);

        $this->assertEquals($dir . '/chromedriver', $executablePath);
        $this->assertFileExists($executablePath);
    }
}
