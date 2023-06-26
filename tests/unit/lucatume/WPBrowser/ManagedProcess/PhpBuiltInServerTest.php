<?php


namespace lucatume\WPBrowser\ManagedProcess;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Filesystem;

class PhpBuiltInServerTest extends Unit
{
    use TmpFilesCleanup;
    use UopzFunctions;

    /**
     * It should start PHP Built-in server on random port if none is provided
     *
     * @test
     */
    public function should_start_php_built_in_server_on_random_port_if_none_is_provided(): void
    {
        $docRoot = Filesystem::tmpDir('server_', [
            'index.php' => '<?php echo "Hello World!";',
        ]);
        $server = new PhpBuiltInServer($docRoot);
        $server->start(['XDEBUG_MODE' => 'off']);
        $port = $server->getPort();

        $this->assertEquals('Hello World!', file_get_contents("http://localhost:$port"));

        $this->assertEquals(0, pcntl_wexitstatus($server->stop()));
    }

    /**
     * It should throw if document root does not exist
     *
     * @test
     */
    public function should_throw_if_document_root_does_not_exist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_DOC_ROOT_NOT_FOUND);
        new PhpBuiltInServer(__DIR__ . '/not-a-dir');
    }

    /**
     * It should throw if server cannot be started on port
     *
     * @test
     */
    public function should_throw_if_server_cannot_be_started_on_port(): void
    {
        $docRoot = Filesystem::tmpDir('server_', [
            'index.php' => '<?php echo "Hello World!";',
        ]);
        $server1 = new PhpBuiltInServer($docRoot);
        $server1->start(['XDEBUG_MODE' => 'off']);
        $port = $server1->getPort();

        usleep(750000);
        $server2 = new PhpBuiltInServer($docRoot, $port);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_PORT_ALREADY_IN_USE);

        $server2->start();
    }

    /**
     * It should throw if trying to stop not running server
     *
     * @test
     */
    public function should_throw_if_trying_to_stop_not_running_server(): void
    {
        $docRoot = Filesystem::tmpDir('server_', [
            'index.php' => '<?php echo "Hello World!";',
        ]);
        $server = new PhpBuiltInServer($docRoot);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_NO_STARTED);

        $server->stop();
    }

    /**
     * It should throw if trying to get PID of not running server
     *
     * @test
     */
    public function should_throw_if_trying_to_get_pid_of_not_running_server(): void
    {
        $docRoot = Filesystem::tmpDir('server_', [
            'index.php' => '<?php echo "Hello World!";',
        ]);
        $server = new PhpBuiltInServer($docRoot);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_NO_STARTED);

        $server->getPid();
    }

    /**
     * It should register its PID in a file
     *
     * @test
     */
    public function should_register_its_pid_in_a_file(): void
    {
        $docRoot = Filesystem::tmpDir('server_', [
            'index.php' => '<?php echo "Hello World!";',
        ]);

        $server = new PhpBuiltInServer($docRoot);
        $server->start(['XDEBUG_MODE' => 'off']);

        $this->assertIsInt($server->getPid());
        $this->assertFileExists($server->getPidFile());
    }

    /**
     * It should throw if PID file cannot be written
     *
     * @test
     */
    public function should_throw_if_pid_file_cannot_be_written(): void
    {
        $docRoot = Filesystem::tmpDir('server_', [
            'index.php' => '<?php echo "Hello World!";',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(PhpBuiltInServer::ERR_PID_FILE);
        $this->uopzSetFunctionReturn('file_put_contents', false);

        $server = new PhpBuiltInServer($docRoot);
        $server->start(['XDEBUG_MODE' => 'off']);
    }
}
