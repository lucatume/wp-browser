<?php


namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use \UnitTester;

class ComposerTest extends \Codeception\Test\Unit
{
    use UopzFunctions;

    /**
     * It should throw if trying to build on non existing file
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_non_existing_file(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Composer::ERR_FILE_NOT_FOUND);
        new Composer('/not-a-file');
    }

    /**
     * It should throw if trying to build on non-readable file
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_non_readable_file(): void
    {
        $this->uopzSetFunctionReturn('is_readable', false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Composer::ERR_FILE_NOT_FOUND);
        new Composer(__FILE__);
    }

    /**
     * It should throw if trying to build on non JSON file
     *
     * @test
     */
    public function should_throw_if_trying_to_build_on_non_json_file(): void
    {
        $this->expectException(\JsonException::class);
        new Composer(__FILE__);
    }

    /**
     * It should throw if file contents cannot be read
     *
     * @test
     */
    public function should_throw_if_file_contents_cannot_be_read(): void
    {
        $this->uopzSetFunctionReturn('file_get_contents', false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Composer::ERR_FILE_UNREADABLE);
        new Composer(__FILE__);
    }

    /**
     * It should allow adding composer dev packages
     *
     * @test
     */
    public function should_allow_adding_composer_dev_packages(): void
    {
        $hash = md5(microtime());
        $testFile = sys_get_temp_dir() . "/$hash-composer.json";
        copy(codecept_data_dir('composer-files/test-1-composer.json'), $testFile);
        $composer = new Composer($testFile);
        $composer->requireDev(['foo/bar' => '1.0.0', 'foo/baz' => '^2.3']);
        $this->assertStringContainsString('"foo/bar": "1.0.0"', $composer->getContents());
        $this->assertStringContainsString('"foo/baz": "^2.3"', $composer->getContents());
        $composer->write();
        $this->assertFileExists($testFile);
        $contents = file_get_contents($testFile);
        $this->assertEquals($contents, $composer->getContents());
        $this->assertStringContainsString('"foo/bar": "1.0.0"', $contents);
        $this->assertStringContainsString('"foo/baz": "^2.3"', $contents);
    }

    /**
     * It should throw if write fails
     *
     * @test
     */
    public function should_throw_if_write_fails(): void
    {
        $composer = new Composer(codecept_data_dir('composer-files/test-1-composer.json'));
        $composer->requireDev(['foo/bar' => '1.0.0', 'foo/baz' => '^2.3']);
        $hash = md5(microtime());
        $outputFile = sys_get_temp_dir() . "/$hash-composer.json";
        $this->uopzSetFunctionReturn('file_put_contents', false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Composer::ERR_FILE_WRITE_FAILED);
        $composer->write();
    }

    /**
     * It should allow updating the Composer file
     *
     * @test
     */
    public function should_allow_updating_the_composer_file(): void
    {
        $calledCommand = null;
        $this->uopzSetFunctionReturn('exec',
            function (string $command, ?array &$output, ?int &$result) use (&$calledCommand) {
                $calledCommand = $command;
                return exec('exit 0', $output, $result);
            },
            true);
        $hash = md5(microtime());
        $testFile = sys_get_temp_dir() . "/$hash-composer.json";
        copy(codecept_data_dir('composer-files/test-1-composer.json'), $testFile);
        $composer = new Composer($testFile);
        $composer->requireDev(['foo/bar' => '1.0.0', 'foo/baz' => '^2.3']);
        $composer->update();
        $this->assertEquals('composer update', $calledCommand);
    }

    /**
     * It should throw if update fails
     *
     * @test
     */
    public function should_throw_if_update_fails(): void
    {
        $this->uopzSetFunctionReturn('exec',
            function (string $command, ?array &$output, ?int &$result) {
                return exec('exit 1', $output, $result);
            },
            true);
        $hash = md5(microtime());
        $testFile = sys_get_temp_dir() . "/$hash-composer.json";
        copy(codecept_data_dir('composer-files/test-1-composer.json'), $testFile);
        $composer = new Composer($testFile);
        $composer->requireDev(['foo/bar' => '1.0.0', 'foo/baz' => '^2.3']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(Composer::ERR_UPDATE_FAILED);

        $composer->update();
    }

    /**
     * It should build on the project Composer file if no composer file specified in constructor
     *
     * @test
     */
    public function should_build_on_the_project_composer_file_if_no_composer_file_specified_in_constructor(): void
    {
        $composer = new Composer();
        $this->assertEquals(
            json_decode(file_get_contents(codecept_root_dir('composer.json')), false),
            $composer->getDecodedContents()
        );
    }
}
