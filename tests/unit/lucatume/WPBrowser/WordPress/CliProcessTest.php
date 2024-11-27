<?php


namespace Unit\lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\WordPress\CliProcess;

class CliProcessTest extends \Codeception\Test\Unit
{
    private ?string $homeBackup = null;

    public function setUp(): void
    {
        parent::setUp();
        if (isset($_SERVER['HOME'])) {
            $this->homeBackup = $_SERVER['HOME'];
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if ($this->homeBackup !== null) {
            $_SERVER['HOME'] = $this->homeBackup;
        }
    }

    public function test_construct_with_custom_binary(): void
    {
        $binary = codecept_data_dir('bins/wp-cli-custom-bin');

        $cliProcess = new CliProcess(['core', 'version'], null, null, null, null, $binary);

        $this->assertEquals(
            escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($binary) . " 'core' 'version'",
            $cliProcess->getCommandLine()
        );
    }

    public function test_throws_if_custom_binary_does_not_exist(): void
    {
        $binary = codecept_data_dir('bins/not-a-bin');

        $this->expectException(InvalidArgumentException::class);

        $cliProcess = new CliProcess(['core', 'version'], null, null, null, null, $binary);
    }

    public function test_throws_if_custom_binary_is_not_executable(): void
    {
        $binary = codecept_data_dir('bins/not-executable');

        $this->expectException(InvalidArgumentException::class);

        $cliProcess = new CliProcess(['core', 'version'], null, null, null, null, $binary);
    }

    public function test_tilde_for_home_dir_is_supported_in_custom_binary_path(): void
    {
        $_SERVER['HOME'] = codecept_data_dir();
        $binary = '~/bins/wp-cli-custom-bin';
        $binaryAbsolutePath = codecept_data_dir('bins/wp-cli-custom-bin');
        // Sanity check.
        $this->assertEquals(rtrim(codecept_data_dir(), '\\/'), Filesystem::homeDir());

        $cliProcess = new CliProcess(['core', 'version'], null, null, null, null, $binary);

        $this->assertEquals(
            escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($binaryAbsolutePath) . " 'core' 'version'",
            $cliProcess->getCommandLine()
        );
    }
}
