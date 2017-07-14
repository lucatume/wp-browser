<?php

namespace tad\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Prophecy\Argument;
use tad\WPBrowser\Filesystem\Filesystem;

class SymlinkerTest extends \Codeception\TestCase\Test {

	protected $backupGlobals = false;

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
	 * @var string
	 */
	protected $filename;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf('tad\WPBrowser\Extension\Symlinker', $sut);
	}

	private function make_instance() {
		return new Symlinker($this->config, $this->options, $this->filesystem->reveal());
	}

	/**
	 * @test
	 * it should throw if symlinking mode is missing
	 */
	public function it_should_throw_if_symlinking_mode_is_missing() {
		$this->config = [];

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if symlinking destination is missing
	 */
	public function it_should_throw_if_symlinking_destination_is_missing() {
		$this->config = ['mode' => 'plugin'];

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if mode is not `plugin` or `theme`
	 */
	public function it_should_throw_if_mode_is_not_plugin_or_theme_() {
		$this->config = ['mode' => 'something', 'destination' => __DIR__];

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if destination is not dir
	 */
	public function it_should_throw_if_destination_is_not_dir() {
		$this->config = ['mode' => 'something', 'destination' => __DIR__];
		$this->filesystem->is_dir(__DIR__)->willReturn(false);

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if destination is not writeable
	 */
	public function it_should_throw_if_destination_is_not_writeable() {
		$this->config = ['mode' => 'something', 'destination' => __DIR__];
		$this->filesystem->is_dir(__DIR__)->willReturn(true);
		$this->filesystem->is_writeable(__DIR__)->willReturn(false);

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should symlink the root folder into the destination before the suite runs
	 */
	public function it_should_symlink_the_root_folder_into_the_destination_before_the_suite_runs() {
		$this->config = ['mode' => 'plugin', 'destination' => __DIR__];
		$this->filesystem->is_dir(__DIR__)->willReturn(true);
		$this->filesystem->is_writeable(__DIR__)->willReturn(true);
		$this->filesystem->file_exists($this->filename)->willReturn(false);
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $this->filename,
			true)->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should not attempt re-linking if file exists already
	 */
	public function it_should_not_attempt_re_linking_if_file_exists_already() {
		$this->config = ['mode' => 'plugin', 'destination' => __DIR__];
		$this->filesystem->is_dir(__DIR__)->willReturn(true);
		$this->filesystem->is_writeable(__DIR__)->willReturn(true);
		$this->filesystem->file_exists($this->filename)->willReturn(true);
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $this->filename,
			true)->shouldNotBeCalled();

		$sut = $this->make_instance();
		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should symlink the files over to the destination if mode is theme before the suite runs
	 */
	public function it_should_symlink_the_files_over_to_the_destination_if_mode_is_theme_before_the_suite_runs() {
		$this->config = ['mode' => 'theme', 'destination' => __DIR__];
		$this->filesystem->is_dir(__DIR__)->willReturn(true);
		$this->filesystem->is_writeable(__DIR__)->willReturn(true);
		$this->filesystem->file_exists($this->filename)->willReturn(false);
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $this->filename,
			true)->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should unlink the root folder from the destination after the suite ran
	 */
	public function it_should_unlink_the_root_folder_from_the_destination_after_the_suite_ran() {
		$this->config = ['mode' => 'plugin', 'destination' => __DIR__];
		$this->filesystem->file_exists($this->filename)->willReturn(true);
		$this->filesystem->unlink(__DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir()))->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->unlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should unlink the linked theme from the destination folder after the suite ran if mode is theme
	 */
	public function it_should_unlink_the_linked_theme_from_the_destination_folder_after_the_suite_ran_if_mode_is_theme() {
		$this->config = ['mode' => 'theme', 'destination' => __DIR__];
		$this->filesystem->file_exists($this->filename)->willReturn(true);
		$this->filesystem->unlink($this->filename)->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->unlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should not attempt unlinking if destination file does not exist
	 */
	public function it_should_not_attempt_unlinking_if_destination_file_does_not_exist() {
		$this->config = ['mode' => 'theme', 'destination' => __DIR__];
		$this->filesystem->file_exists($this->filename)->willReturn(false);
		$this->filesystem->unlink(__DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir()))->shouldNotBeCalled();

		$sut = $this->make_instance();
		$sut->unlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should support array of destinations to allow for environments settings
	 */
	public function it_should_support_array_of_destinations_to_allow_for_environments_settings() {
		$fooDestinationFolder = '/foo';
		$barDestinationFolder = '/bar';
		$fooDestination       = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$barDestination       = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$this->config = [
			'mode'        => 'theme',
			'destination' => ['foo' => $fooDestinationFolder, 'bar' => $barDestinationFolder],
		];
		$this->event->getSettings()->willReturn(['current_environment' => 'foo']);
		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($barDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($barDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($fooDestination)->willReturn(false);
		$this->filesystem->file_exists($barDestination)->shouldNotBeCalled();
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $fooDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should fallback to use the first available destination if multiple envs destination are set but no cli env specified
	 */
	public function it_should_fallback_to_use_the_first_available_destination_if_multiple_envs_destination_are_set_but_no_cli_env_specified() {
		$fooDestinationFolder = '/foo';
		$barDestinationFolder = '/bar';
		$fooDestination       = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$barDestination       = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$this->config = [
			'mode'        => 'theme',
			'destination' => ['foo' => $fooDestinationFolder, 'bar' => $barDestinationFolder],
		];
		$this->event->getSettings()->willReturn([]);
		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($barDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($barDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($fooDestination)->willReturn(false);
		$this->filesystem->file_exists($barDestination)->shouldNotBeCalled();
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $fooDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should fallback to use the default destination if multiple envs destinations are set but no cli env specified
	 */
	public function it_should_fallback_to_use_the_default_destination_if_multiple_envs_destinations_are_set_but_no_cli_env_specified() {
		$fooDestinationFolder     = '/foo';
		$barDestinationFolder     = '/bar';
		$defaultDestinationFolder = '/default';
		$fooDestination           = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$barDestination           = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$defaultDestination       = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$this->config = [
			'mode'        => 'theme',
			'destination' => [
				'foo'     => $fooDestinationFolder,
				'bar'     => $barDestinationFolder,
				'default' => $defaultDestinationFolder,
			],
		];
		$this->event->getSettings()->willReturn([]);
		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($barDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($barDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($defaultDestination)->willReturn(false);
		$this->filesystem->file_exists($fooDestination)->shouldNotBeCalled();
		$this->filesystem->file_exists($barDestination)->shouldNotBeCalled();
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $defaultDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should fallback to use default destination if current env has no destination assigned and default destination is specified
	 */
	public function it_should_fallback_to_use_default_destination_if_current_env_has_no_destination_assigned_and_default_destination_is_specified() {
		$fooDestinationFolder     = '/foo';
		$barDestinationFolder     = '/bar';
		$defaultDestinationFolder = '/default';
		$fooDestination           = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$barDestination           = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$defaultDestination       = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$this->config = [
			'mode'        => 'theme',
			'destination' => [
				'foo'     => $fooDestinationFolder,
				'bar'     => $barDestinationFolder,
				'default' => $defaultDestinationFolder,
			],
		];
		$this->event->getSettings()->willReturn(['current_environment' => 'another']);
		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($barDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($barDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($defaultDestination)->willReturn(false);
		$this->filesystem->file_exists($fooDestination)->shouldNotBeCalled();
		$this->filesystem->file_exists($barDestination)->shouldNotBeCalled();
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $defaultDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should read supported env from comma separated list of envs
	 */
	public function it_should_read_supported_env_from_comma_separated_list_of_envs() {
		$fooDestinationFolder     = '/foo';
		$defaultDestinationFolder = '/default';

		$fooDestination     = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$defaultDestination = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$envDestinations = ['foo' => $fooDestinationFolder, 'default' => $defaultDestinationFolder];
		$this->config    = ['mode' => 'plugin', 'destination' => $envDestinations];
		$this->event->getSettings()->willReturn(['current_environment' => 'some,other,env,foo']);

		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($fooDestination)->willReturn(false);
		$this->filesystem->file_exists($defaultDestination)->shouldNotBeCalled();
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $fooDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should fallback to first destination if specifying multiple envs and none supported
	 */
	public function it_should_fallback_to_first_destination_if_specifying_multiple_envs_and_none_supported() {
		$fooDestinationFolder = '/foo';
		$gooDestinationFolder = '/goo';

		$fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$gooDestination = $gooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$envDestinations = ['foo' => $fooDestinationFolder, 'goo' => $gooDestinationFolder];
		$this->config    = ['mode' => 'plugin', 'destination' => $envDestinations];
		$this->event->getSettings()->willReturn(['current_environment' => 'bar, baz']);

		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($gooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($gooDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($fooDestination)->willReturn(false);
		$this->filesystem->file_exists($gooDestination)->shouldNotBeCalled();
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $fooDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should fallback to default destination if specified and multiple envs and none supported
	 */
	public function it_should_fallback_to_default_destination_if_specified_and_multiple_envs_and_none_supported() {
		$fooDestinationFolder     = '/foo';
		$defaultDestinationFolder = '/default';

		$fooDestination     = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$defaultDestination = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

		$envDestinations = ['foo' => $fooDestinationFolder, 'default' => $defaultDestinationFolder];
		$this->config    = ['mode' => 'plugin', 'destination' => $envDestinations];
		$this->event->getSettings()->willReturn(['current_environment' => 'bar, baz']);

		$this->filesystem->is_dir($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($fooDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->file_exists($fooDestination)->shouldNotBeCalled(false);
		$this->filesystem->file_exists($defaultDestination)->willReturn(false);
		$this->filesystem->symlink(rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR), $defaultDestination,
			true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should throw if config specified root folder is not a folder
	 */
	public function it_should_throw_if_config_specified_root_folder_is_not_a_folder() {
		$this->config['rootFolder'] = __DIR__;
		$this->filesystem->is_dir(__DIR__)->willReturn(false);

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if config specified root folder is not readable
	 */
	public function it_should_throw_if_config_specified_root_folder_is_not_readable() {
		$this->config['rootFolder'] = __DIR__;
		$this->filesystem->is_dir(__DIR__)->willReturn(true);
		$this->filesystem->is_readable(__DIR__)->willReturn(false);

		$this->expectException('Codeception\Exception\ExtensionException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should allow specifying the root folder in the configuration
	 */
	public function it_should_allow_specifying_the_root_folder_in_the_configuration() {
		$rootFolder               = __DIR__;
		$defaultDestinationFolder = '/default';
		$defaultDestination       = $defaultDestinationFolder;
		$envDestinations          = ['default' => $defaultDestinationFolder];
		$this->config             = ['mode' => 'plugin', 'destination' => $envDestinations, 'rootFolder' => $rootFolder];

		$this->event->getSettings()->willReturn(['current_environment' => 'default']);

		$this->filesystem->file_exists($defaultDestination . DIRECTORY_SEPARATOR . basename($rootFolder))->willReturn(false);
		$this->filesystem->is_dir($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->is_writeable($defaultDestinationFolder)->willReturn(true);
		$this->filesystem->is_dir($rootFolder)->willReturn(true);
		$this->filesystem->is_readable($rootFolder)->willReturn(true);
		$this->filesystem->symlink(rtrim($rootFolder, DIRECTORY_SEPARATOR),
			$defaultDestination . DIRECTORY_SEPARATOR . basename($rootFolder), true)->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * @test
	 * it should support array of root folders to allow for environments settings
	 */
	public function it_should_support_array_of_root_folders_to_allow_for_environments_settings() {
		$settings     = [
			'current_environment' => 'bar',
		];
		$destination  = '/some/path';
		$this->config = [
			'mode'        => 'plugin',
			'rootFolder'  => [
				'foo' => '/',
				'bar' => '/one',
				'baz' => '/two',
			],
			'destination' => $destination,
		];

		$this->event->getSettings()->willReturn($settings);

		$this->filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		foreach ($this->config['rootFolder'] as $env => $folder) {
			$this->filesystem->is_dir($folder)->willReturn(true);
			$this->filesystem->is_readable($folder)->willReturn(true);
		}
		$this->filesystem->is_dir($destination)->willReturn(true);
		$this->filesystem->is_writeable($destination)->willReturn(true);
		$this->filesystem->file_exists($destination . '/one')->willReturn(false);
		$this->filesystem->symlink('/one', $destination . '/one', true)->shouldBeCalled(true);

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * It should fall back to first root folder if array is provided, env does not match and default is not set
	 *
	 * @test
	 */
	public function should_fall_back_to_first_root_folder_if_array_is_provided_env_does_not_match_and_default_is_not_set() {
		$settings     = [
			'current_environment' => 'zoop',
		];
		$destination  = '/some/path';
		$this->config = [
			'mode'        => 'plugin',
			'rootFolder'  => [
				'foo' => '/first',
				'bar' => '/second',
				'baz' => '/third',
			],
			'destination' => $destination,
		];

		$this->event->getSettings()->willReturn($settings);

		$this->filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		foreach ($this->config['rootFolder'] as $env => $folder) {
			$this->filesystem->is_dir($folder)->willReturn(true);
			$this->filesystem->is_readable($folder)->willReturn(true);
		}
		$this->filesystem->is_dir($destination)->willReturn(true);
		$this->filesystem->is_writeable($destination)->willReturn(true);
		$this->filesystem->file_exists($destination . '/first')->willReturn(false);
		$this->filesystem->symlink('/first', $destination . '/first', true)->shouldBeCalled(true);

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	/**
	 * It should fall back to default root folder if array is provided and env does not match
	 *
	 * @test
	 */
	public function should_fall_back_to_default_root_folder_if_array_is_provided_and_env_does_not_match() {
		$settings     = [
			'current_environment' => 'zoop',
		];
		$destination  = '/some/path';
		$this->config = [
			'mode'        => 'plugin',
			'rootFolder'  => [
				'foo' => '/first',
				'bar' => '/second',
				'baz' => '/third',
				'default' => '/default'
			],
			'destination' => $destination,
		];

		$this->event->getSettings()->willReturn($settings);

		$this->filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		foreach ($this->config['rootFolder'] as $env => $folder) {
			$this->filesystem->is_dir($folder)->willReturn(true);
			$this->filesystem->is_readable($folder)->willReturn(true);
		}
		$this->filesystem->is_dir($destination)->willReturn(true);
		$this->filesystem->is_writeable($destination)->willReturn(true);
		$this->filesystem->file_exists($destination . '/default')->willReturn(false);
		$this->filesystem->symlink('/default', $destination . '/default', true)->shouldBeCalled(true);

		$sut = $this->make_instance();

		$sut->symlink($this->event->reveal());
	}

	protected function _before() {
		$this->filename   = __DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
		$this->filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		$this->filesystem->is_dir(__DIR__)->willReturn(true);
		$this->filesystem->is_writeable(__DIR__)->willReturn(true);
		$this->event      = $this->prophesize('\Codeception\Event\SuiteEvent');
		$this->printEvent = $this->prophesize('\Codeception\Event\PrintResultEvent');
	}

	protected function _after() {
	}
}