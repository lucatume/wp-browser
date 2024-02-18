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

    private ?string $composerAutoloadPath = null;
    private ?string $composerBinDir = null;

    /**
     * @before
     */
    public function backupComposerAutoloadPath(): void
    {
        $this->composerAutoloadPath = $GLOBALS['_composer_autoload_path'];
        $this->composerBinDir = $GLOBALS['_composer_bin_dir'];
    }

    /**
     * @after
     */
    public function restoreEnv(): void
    {
        putenv('LOADED');
        putenv('LOADED_2');
        putenv('LOADED_3');
        putenv('LOADED_4');
        $GLOBALS['_composer_autoload_path'] = $this->composerAutoloadPath;
        $GLOBALS['_composer_bin_dir'] = $this->composerBinDir;
    }

    public function testConstructWithDefaultArguments(): void
    {
        $control = new Control([]);

        $this->assertEquals([
            'autoloadFile' => $GLOBALS['_composer_autoload_path'],
            'requireFiles' => [],
            'cwd' => getcwd(),
            'codeceptionRootDir' => codecept_root_dir(),
            'codeceptionConfig' => Configuration::config(),
            'composerAutoloadPath' => $GLOBALS['_composer_autoload_path'],
            'composerBinDir' => $GLOBALS['_composer_bin_dir'],
            'env' => array_merge(getenv(), $_ENV ?? [])
        ], $control->toArray());
    }

    public function testConstructWithCustomArguments(): void
    {
        $control = new Control([
            'autoloadFile' => 'autoload.php',
            'requireFiles' => ['file1.php', 'file2.php'],
            'cwd' => '/tmp',
            'codeceptionRootDir' => __DIR__,
            'codeceptionConfig' => ['config' => 'value'],
            'composerAutoloadPath' => 'autoload.php',
            'composerBinDir' => '/var/bin',
            'env' => ['FOO' => 'bar', 'BAR' => 'baz']
        ]);

        $this->assertEquals([
            'autoloadFile' => 'autoload.php',
            'requireFiles' => ['file1.php', 'file2.php'],
            'cwd' => '/tmp',
            'codeceptionRootDir' => __DIR__,
            'codeceptionConfig' => ['config' => 'value'],
            'composerAutoloadPath' => 'autoload.php',
            'composerBinDir' => '/var/bin',
            'env' => ['FOO' => 'bar', 'BAR' => 'baz']
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

    public function testSetsAndLoadsComposerAutoloadPathIfSet(): void
    {
        $control = new Control([
            'composerAutoloadPath' => codecept_data_dir('files/test_file_004.php'),
        ]);
        $control->apply();

        $this->assertEquals(codecept_data_dir('files/test_file_004.php'), $GLOBALS['_composer_autoload_path']);
        $this->assertEquals('test_file_004.php', getenv('LOADED_4'));
    }

    public function testThrowsIfComposerAutoloadPathIsNotFile(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::COMPOSER_AUTOLOAD_FILE_NOT_FOUND);

        $control = new Control([
            'composerAutoloadPath' => codecept_data_dir('files/some-file.php'),
        ]);
        $control->apply();
    }

    public function testSetsComposerBinDirIfSet(): void
    {
        $control = new Control([
            'composerBinDir' => codecept_data_dir('files'),
        ]);
        $control->apply();

        $this->assertEquals(codecept_data_dir('files'), $GLOBALS['_composer_bin_dir']);
    }

    public function testThrowsIfComposerBinDirIsNotDir(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::COMPOSER_BIN_DIR_NOT_FOUND);

        $control = new Control([
            'composerBinDir' => codecept_data_dir('some-bin-dir'),
        ]);
        $control->apply();
    }

    public function testItCorrectlyHandlesEmptyCodeceptionConfiguration(): void
    {
        $this->uopzSetStaticMethodReturn(Configuration::class, 'isEmpty', true);

        $this->assertEquals([], Control::getDefault()['codeceptionConfig']);

        $control = new Control([]);

        $this->assertEquals([], $control->toArray()['codeceptionConfig']);
    }

    /**
     * It should  build with default env vars
     *
     * @test
     */
    public function should_build_with_default_env_vars(): void
    {
        $control = new Control([]);

        $this->assertArrayHasKey('env', $control->toArray());
    }

    /**
     * It should parse correctly env vars from build array
     *
     * @test
     */
    public function should_parse_correctly_env_vars_from_build_array(): void
    {
        $controlArray = [
            'env' => [
                'FOO' => 'BAR',
                'BAR' => 'BAZ',
            ]
        ];

        $control = new Control($controlArray);

        $this->assertArrayHasKey('env', $control->toArray());
        $this->assertEquals([
            'FOO' => 'BAR',
            'BAR' => 'BAZ',
        ], $control->toArray()['env']);
    }

    /**
     * It should set environment variables when applying
     *
     * @test
     */
    public function should_set_environment_variables_when_applying(): void
    {
        putenv('TEST_ENV_VAR_1=TEST_ENV_VAR_1_VALUE');
        $_ENV['TEST_ENV_VAR_2'] = 'TEST_ENV_VAR_2_VALUE';

        $control = new Control([]);

        $this->assertArrayHasKey('env', $control->toArray());
        $this->assertEquals('TEST_ENV_VAR_1_VALUE', $control->toArray()['env']['TEST_ENV_VAR_1']);
        $this->assertEquals('TEST_ENV_VAR_2_VALUE', $control->toArray()['env']['TEST_ENV_VAR_2']);

        putenv('TEST_ENV_VAR_1=ANOTHER_VALUE');
        $_ENV['TEST_ENV_VAR_2'] = 'ANOTHER_VALUE';

        $control->apply();

        $this->assertEquals('TEST_ENV_VAR_1_VALUE', $_ENV['TEST_ENV_VAR_1']);
        $this->assertEquals('TEST_ENV_VAR_1_VALUE', getenv('TEST_ENV_VAR_1'));
        $this->assertEquals('TEST_ENV_VAR_2_VALUE', $_ENV['TEST_ENV_VAR_2']);
        $this->assertEquals('TEST_ENV_VAR_2_VALUE', getenv('TEST_ENV_VAR_2'));
    }
}
