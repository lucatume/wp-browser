<?php

declare(strict_types=1);

namespace Unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\ClosureStreamWrapper;
use lucatume\WPBrowser\Utils\Filesystem;

class ClosureStreamWrapperTest extends Unit
{
    use TmpFilesCleanup;

    private bool $wrapperRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (!in_array('closure', stream_get_wrappers(), true)) {
            stream_wrapper_register('closure', ClosureStreamWrapper::class);
            $this->wrapperRegistered = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->wrapperRegistered) {
            stream_wrapper_unregister('closure');
            $this->wrapperRegistered = false;
        }
        parent::tearDown();
    }

    public function test_stream_wrapper_opens_with_base64_payload(): void
    {
        $code = 'return 42;';
        $payload = base64_encode($code);

        $handle = fopen("closure://{$payload}", 'r');

        $this->assertNotFalse($handle);
        fclose($handle);
    }

    public function test_stream_wrapper_reads_php_code(): void
    {
        $code = 'return 42;';
        $payload = base64_encode($code);

        $handle = fopen("closure://{$payload}", 'r');
        $content = stream_get_contents($handle);
        fclose($handle);

        $this->assertSame("<?php\n" . $code, $content);
    }

    public function test_stream_wrapper_returns_false_on_invalid_base64(): void
    {
        $invalidPayload = '!!!not-valid-base64!!!';

        $handle = @fopen("closure://{$invalidPayload}", 'r');

        $this->assertFalse($handle);
    }

    public function test_include_executes_closure_code(): void
    {
        $code = 'return function() { return 42; };';
        $payload = base64_encode($code);

        $closure = include "closure://{$payload}";

        $this->assertIsCallable($closure);
        $this->assertSame(42, $closure());
    }

    public function test_stream_eof_returns_true_at_end(): void
    {
        $code = 'return 1;';
        $payload = base64_encode($code);

        $handle = fopen("closure://{$payload}", 'r');
        $this->assertFalse(feof($handle));

        stream_get_contents($handle);
        $this->assertTrue(feof($handle));

        fclose($handle);
    }

    public function test_stream_seek_with_seek_set(): void
    {
        $code = 'return 123;';
        $payload = base64_encode($code);

        $handle = fopen("closure://{$payload}", 'r');
        fread($handle, 10);
        fseek($handle, 0, SEEK_SET);

        $content = stream_get_contents($handle);
        fclose($handle);

        $this->assertSame("<?php\n" . $code, $content);
    }

    public function test_stream_seek_with_seek_cur(): void
    {
        $code = 'return 123;';
        $payload = base64_encode($code);
        $expectedContent = "<?php\n" . $code;

        $handle = fopen("closure://{$payload}", 'r');
        fread($handle, 3);
        $this->assertSame(3, ftell($handle));

        fseek($handle, 2, SEEK_CUR);
        $this->assertSame(5, ftell($handle));

        $remaining = stream_get_contents($handle);
        fclose($handle);

        $this->assertSame(substr($expectedContent, 5), $remaining);
    }

    public function test_stream_seek_with_seek_end(): void
    {
        $code = 'return 123;';
        $payload = base64_encode($code);
        $expectedContent = "<?php\n" . $code;
        $contentLength = strlen($expectedContent);

        $handle = fopen("closure://{$payload}", 'r');

        fseek($handle, -5, SEEK_END);
        $this->assertSame($contentLength - 5, ftell($handle));

        $remaining = stream_get_contents($handle);
        fclose($handle);

        $this->assertSame(substr($expectedContent, -5), $remaining);
    }

    public function test_stream_tell_returns_current_position(): void
    {
        $code = 'return 1;';
        $payload = base64_encode($code);

        $handle = fopen("closure://{$payload}", 'r');
        $this->assertSame(0, ftell($handle));

        fread($handle, 5);
        $this->assertSame(5, ftell($handle));

        fclose($handle);
    }

    public function test_stream_stat_returns_size_and_mode(): void
    {
        $code = 'return 1;';
        $payload = base64_encode($code);

        $handle = fopen("closure://{$payload}", 'r');
        $stat = fstat($handle);
        fclose($handle);

        $this->assertArrayHasKey('size', $stat);
        $this->assertArrayHasKey('mode', $stat);
        $expectedSize = strlen("<?php\n" . $code);
        $this->assertSame($expectedSize, $stat['size']);
    }

    public function test_closure_unpack_does_not_create_files(): void
    {
        $tmpDir = Filesystem::tmpDir();
        $originalCwd = getcwd();
        chdir($tmpDir);

        try {
            $code = 'return function() { return "test"; };';
            $payload = base64_encode($code);
            include "closure://{$payload}";

            $files = glob($tmpDir . '/*');
            $this->assertEmpty($files);

            $this->assertFileDoesNotExist($tmpDir . '/debug.php');
        } finally {
            chdir($originalCwd);
        }
    }
}
