<?php
namespace tad\WPBrowser\Extension;

use Codeception\Event\PrintResultEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use tad\WPBrowser\Filesystem\Filesystem;

class SymlinkerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    protected $config = ['mode' => 'plugin', 'destination' => __DIR__];

    /**
     * @var array
     */
    protected $options = ['silent' => true];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var SuiteEvent
     */
    protected $event;

    /**
     * @var PrintResultEvent
     */
    protected $printEvent;

    /**
     * @var string
     */
    protected $filename;

    protected function _before()
    {
        $this->filename = __DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $this->filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
        $this->filesystem->isDir(__DIR__)->willReturn(true);
        $this->filesystem->isWriteable(__DIR__)->willReturn(true);
        $this->event = $this->prophesize('\Codeception\Event\SuiteEvent');
        $this->printEvent = $this->prophesize('\Codeception\Event\PrintResultEvent');
    }

    protected function _after()
    {
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf('tad\WPBrowser\Extension\Symlinker', $sut);
    }

    /**
     * @test
     * it should throw if symlinking mode is missing
     */
    public function it_should_throw_if_symlinking_mode_is_missing()
    {
        $this->config = [];

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should throw if symlinking destination is missing
     */
    public function it_should_throw_if_symlinking_destination_is_missing()
    {
        $this->config = ['mode' => 'plugin'];

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should throw if mode is not `plugin` or `theme`
     */
    public function it_should_throw_if_mode_is_not_plugin_or_theme_()
    {
        $this->config = ['mode' => 'something', 'destination' => __DIR__];

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should throw if destination is not dir
     */
    public function it_should_throw_if_destination_is_not_dir()
    {
        $this->config = ['mode' => 'something', 'destination' => __DIR__];
        $this->filesystem->isDir(__DIR__)->willReturn(false);

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should throw if destination is not writeable
     */
    public function it_should_throw_if_destination_is_not_writeable()
    {
        $this->config = ['mode' => 'something', 'destination' => __DIR__];
        $this->filesystem->isDir(__DIR__)->willReturn(true);
        $this->filesystem->isWriteable(__DIR__)->willReturn(false);

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should symlink the root folder into the destination before the suite runs
     */
    public function it_should_symlink_the_root_folder_into_the_destination_before_the_suite_runs()
    {
        $this->config = ['mode' => 'plugin', 'destination' => __DIR__];
        $this->filesystem->isDir(__DIR__)->willReturn(true);
        $this->filesystem->isWriteable(__DIR__)->willReturn(true);
        $this->filesystem->fileExists($this->filename)->willReturn(false);
        $this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $this->filename, true)->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should not attempt re-linking if file exists already
     */
    public function it_should_not_attempt_re_linking_if_file_exists_already()
    {
        $this->config = ['mode' => 'plugin', 'destination' => __DIR__];
        $this->filesystem->isDir(__DIR__)->willReturn(true);
        $this->filesystem->isWriteable(__DIR__)->willReturn(true);
        $this->filesystem->fileExists($this->filename)->willReturn(true);
        $this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $this->filename, true)->shouldNotBeCalled();

        $sut = $this->make_instance();
        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should symlink the files over to the destination if mode is theme before the suite runs
     */
    public function it_should_symlink_the_files_over_to_the_destination_if_mode_is_theme_before_the_suite_runs()
    {
        $this->config = ['mode' => 'theme', 'destination' => __DIR__];
        $this->filesystem->isDir(__DIR__)->willReturn(true);
        $this->filesystem->isWriteable(__DIR__)->willReturn(true);
        $this->filesystem->fileExists($this->filename)->willReturn(false);
        $this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $this->filename, true)->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should unlink the root folder from the destination after the suite ran
     */
    public function it_should_unlink_the_root_folder_from_the_destination_after_the_suite_ran()
    {
        $this->config = ['mode' => 'plugin', 'destination' => __DIR__];
        $this->filesystem->fileExists($this->filename)->willReturn(true);
        $this->filesystem->unlink(__DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir()))->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->unlink($this->printEvent->reveal());
    }

    /**
     * @test
     * it should unlink the linked theme from the destination folder after the suite ran if mode is theme
     */
    public function it_should_unlink_the_linked_theme_from_the_destination_folder_after_the_suite_ran_if_mode_is_theme()
    {
        $this->config = ['mode' => 'theme', 'destination' => __DIR__];
        $this->filesystem->fileExists($this->filename)->willReturn(true);
        $this->filesystem->unlink($this->filename)->shouldBeCalled();

        $sut = $this->make_instance();
        $sut->unlink($this->printEvent->reveal());
    }

    /**
     * @test
     * it should not attempt unlinking if destination file does not exist
     */
    public function it_should_not_attempt_unlinking_if_destination_file_does_not_exist()
    {
        $this->config = ['mode' => 'theme', 'destination' => __DIR__];
        $this->filesystem->fileExists($this->filename)->willReturn(false);
        $this->filesystem->unlink(__DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir()))->shouldNotBeCalled();

        $sut = $this->make_instance();
        $sut->unlink($this->printEvent->reveal());
    }

    private function make_instance()
    {
        return new Symlinker($this->config, $this->options, $this->filesystem->reveal());
    }
}