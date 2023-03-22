<?php


namespace Unit\lucatume\WPBrowser\Process\Protocol;

use Codeception\Configuration;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Process\Protocol\Control;
use lucatume\WPBrowser\Process\Protocol\ProtocolException;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;

class ControlTest extends Unit
{
    use UopzFunctions;

    /**
     * @before
     * @after
     */
    public function unsetLoadedEnvVar(): void
    {
        putenv('LOADED');
        putenv('LOADED_2');
    }

    public function testConstructWithDefaultArguments(): void
    {
        $control = new Control([]);

        $this->assertEquals([
            'autoloadFile' => $GLOBALS['_composer_autoload_path'],
            'requireFiles' => [],
            'cwd' => getcwd(),
            'codeceptionRootDir' => null,
            'codeceptionConfig' => Configuration::config()
        ], $control->toArray());
    }

    public function testConstructWithCustomArguments(): void
    {
        $control = new Control([
            'autoloadFile' => 'autoload.php',
            'requireFiles' => ['file1.php', 'file2.php'],
            'cwd' => '/tmp',
            'codeceptionRootDir' => __DIR__,
            'codeceptionConfig' => ['config' => 'value']
        ]);

        $this->assertEquals([
            'autoloadFile' => 'autoload.php',
            'requireFiles' => ['file1.php', 'file2.php'],
            'cwd' => '/tmp',
            'codeceptionRootDir' => __DIR__,
            'codeceptionConfig' => ['config' => 'value']
        ], $control->toArray());
    }

    public function testAutoloadFileIsLoadedOnApplication(): void
    {
        $control = new Control([
            'autoloadFile' => codecept_data_dir('files/test_file_001.php'),
        ]);
        $control->apply();

        $this->assertEquals('test_file_001.php', getenv('LOADED'));
    }

    public function testThrowsIfAutoloadFileDoesNotExist(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::AUTLOAD_FILE_NOT_FOUND);

        $control = new Control([
            'autoloadFile' => codecept_data_dir('files/test_file_foo.php'),
        ]);
        $control->apply();
    }

    public function testLoadsRequireFiles(): void
    {
        $control = new Control([
            'requireFiles' => [
                codecept_data_dir('files/test_file_002.php'),
                codecept_data_dir('files/test_file_003.php'),
            ],
        ]);
        $control->apply();

        $this->assertEquals('test_file_002.php', getenv('LOADED_2'));
        $this->assertEquals('test_file_003.php', getenv('LOADED_3'));
    }

    public function testThrowsIfRequireFileDoesNotExist(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::REQUIRED_FILE_NOT_FOUND);

        $control = new Control([
            'requireFiles' => [
                codecept_data_dir('files/test_file_foo.php'),
            ],
        ]);
        $control->apply();
    }

    public function testConfigsCodeceptionIfConfigIsSet(): void
    {
        $configCalled = false;
        $this->uopzSetStaticMethodReturn(Configuration::class, 'config', function () use (&$configCalled) {
            $configCalled = true;
        }, true);
        $appended = [];
        $this->uopzSetStaticMethodReturn(Configuration::class, 'append', function (array $append) use (&$appended) {
            $appended = $append;
        }, true);

        $control = new Control([
            'codeceptionConfig' => [
                'config' => 'value'
            ],
        ]);
        $control->apply();

        $this->assertTrue($configCalled);
        $this->assertEquals(['config' => 'value'], $appended);
    }

    public function testSetsCwdIfCodeceptionRootDirSet(): void
    {
        $changedDir = null;
        $this->uopzSetFunctionReturn('chdir', function (string $dir) use (&$changedDir) {
            $changedDir = $changedDir ? chdir($dir) : $dir;
        }, true);
        $configCalled = false;
        $this->uopzSetStaticMethodReturn(Configuration::class, 'config', function () use (&$configCalled) {
            $configCalled = true;
        }, true);
        $appended = [];
        $this->uopzSetStaticMethodReturn(Configuration::class, 'append', function (array $append) use (&$appended) {
            $appended = $append;
        }, true);

        $control = new Control([
            'codeceptionRootDir' => __DIR__,
            'codeceptionConfig' => [
                'config' => 'value'
            ],
        ]);
        $control->apply();

        $this->assertEquals(__DIR__, $changedDir);
        $this->assertTrue($configCalled);
        $this->assertEquals(['config' => 'value'], $appended);
        $this->assertEquals(rtrim(codecept_root_dir(), '\\/'), getcwd());
    }

    public function testThrowsIfCodeceptionRootDirDoesNotExist(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::CODECEPTION_ROOT_DIR_NOT_FOUND);

        $control = new Control([
            'codeceptionRootDir' => codecept_data_dir('files/some-dir'),
        ]);
        $control->apply();
    }

    public function testSetsCwdIfSet(): void
    {
        $changedDir = null;
        $this->uopzSetFunctionReturn('chdir', function (string $dir) use (&$changedDir) {
            $changedDir = $dir;
        }, true);
        $control = new Control([
            'cwd' => __DIR__,
        ]);
        $control->apply();

        $this->assertEquals(__DIR__, $changedDir);
    }

    public function testThrowsIfCwdDoesNotExist(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::CWD_NOT_FOUND);

        $control = new Control([
            'cwd' => codecept_data_dir('files/some-dir'),
        ]);
        $control->apply();
    }
}
