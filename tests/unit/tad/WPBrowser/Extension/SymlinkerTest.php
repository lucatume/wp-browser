<?php

namespace tad\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Exception\IOException;
use tad\WPBrowser\StubProphecy\Arg;
use tad\WPBrowser\StubProphecy\FunctionProphecy as the_function;
use tad\WPBrowser\Traits\WithStubProphecy;

class SymlinkerTest extends \Codeception\TestCase\Test
{
    use WithStubProphecy;

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
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf('tad\WPBrowser\Extension\Symlinker', $sut);
    }

    private function make_instance()
    {
        return new Symlinker($this->config, $this->options);
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
        the_function::is_dir(__DIR__)->willReturn(false);

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
        the_function::is_dir(__DIR__)->willReturn(true);
        the_function::is_writable(__DIR__)->willReturn(false);

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
        $this->givenFileExists($this->filename, true);
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $this->filename,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();
        $sut->symlink($this->event->reveal());
    }

    private function givenFileExists($filename, $exists)
    {
        the_function::file_exists(Arg::any())->will(static function ($file) use ($filename, $exists) {
            if ($file === $filename) {
                return $exists;
            }
            return file_exists($file);
        });
    }

    /**
     * @test
     * it should not attempt re-linking if file exists already
     */
    public function it_should_not_attempt_re_linking_if_file_exists_already()
    {
        $this->config = ['mode' => 'plugin', 'destination' => __DIR__];
        $this->givenFileExists($this->filename, true);
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $this->filename,
            true
        )->shouldNotBeCalled();

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
        $this->givenFileExists($this->filename, false);
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $this->filename,
            true
        )->shouldBeCalled()->willReturn(true);

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
        $this->givenFileExists($this->filename, true);
        the_function::unlink(__DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir()))->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();
        $sut->unlink($this->event->reveal());
    }

    /**
     * @test
     * it should unlink the linked theme from the destination folder after the suite ran if mode is theme
     */
    public function it_should_unlink_the_linked_theme_from_the_destination_folder_after_the_suite_ran_if_mode_is_theme()
    {
        $this->config = ['mode' => 'theme', 'destination' => __DIR__];
        $this->givenFileExists($this->filename, true);
        the_function::unlink($this->filename)->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();
        $sut->unlink($this->event->reveal());
    }

    /**
     * @test
     * it should not attempt unlinking if destination file does not exist
     */
    public function it_should_not_attempt_unlinking_if_destination_file_does_not_exist()
    {
        $this->config = ['mode' => 'theme', 'destination' => __DIR__];
        $this->givenFileExists($this->filename, false);
        the_function::unlink(__DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir()))->shouldNotBeCalled();

        $sut = $this->make_instance();
        $sut->unlink($this->event->reveal());
    }

    /**
     * @test
     * it should support array of destinations to allow for environments settings
     */
    public function it_should_support_array_of_destinations_to_allow_for_environments_settings()
    {
        $fooDestinationFolder = '/foo';
        $barDestinationFolder = '/bar';
        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $barDestination = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $this->config = [
            'mode' => 'theme',
            'destination' => ['foo' => $fooDestinationFolder, 'bar' => $barDestinationFolder],
        ];
        $this->event->getSettings()->willReturn(['current_environment' => 'foo']);
        $returnTrueOrRelayTo = $this->ifIn([$fooDestinationFolder, $barDestinationFolder]);
        the_function::is_dir(Arg::type('string'))->will($returnTrueOrRelayTo('is_dir'));
        the_function::is_writable(Arg::type('string'))->will($returnTrueOrRelayTo('is_writable'));
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($barDestination, $fooDestination) {
            if ($filename === $fooDestination) {
                return false;
            }
            if ($filename === $barDestination) {
                throw new AssertionFailedError("is_file should not be called with {$barDestination}");
            }
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $fooDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    private function ifIn(array $matches)
    {
        return static function ($relay) use ($matches) {
            return static function ($filename) use ($relay, $matches) {
                if (in_array($filename, $matches, true)) {
                    return true;
                }
                return $relay($filename);
            };
        };
    }

    /**
     * @test
     * it should fallback to use the first available destination if multiple envs destination are set but no cli env specified
     */
    public function it_should_fallback_to_use_the_first_available_destination_if_multiple_envs_destination_are_set_but_no_cli_env_specified()
    {
        $fooDestinationFolder = '/foo';
        $barDestinationFolder = '/bar';
        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $barDestination = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $this->config = [
            'mode' => 'theme',
            'destination' => ['foo' => $fooDestinationFolder, 'bar' => $barDestinationFolder],
        ];
        $this->event->getSettings()->willReturn([]);
        $matches = [$fooDestinationFolder, $barDestinationFolder];
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($barDestination, $fooDestination) {
            if ($filename === $fooDestination) {
                return false;
            }
            if ($filename === $barDestination) {
                throw new AssertionFailedError('is_file should not be called with ' . $barDestination);
            }
            return is_file($filename);
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $fooDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should fallback to use the default destination if multiple envs destinations are set but no cli env specified
     */
    public function it_should_fallback_to_use_the_default_destination_if_multiple_envs_destinations_are_set_but_no_cli_env_specified()
    {
        $fooDestinationFolder = '/foo';
        $barDestinationFolder = '/bar';
        $defaultDestinationFolder = '/default';
        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $barDestination = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $defaultDestination = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $this->config = [
            'mode' => 'theme',
            'destination' => [
                'foo' => $fooDestinationFolder,
                'bar' => $barDestinationFolder,
                'default' => $defaultDestinationFolder,
            ],
        ];
        $this->event->getSettings()->willReturn([]);
        $matches = [$fooDestinationFolder, $barDestinationFolder, $defaultDestinationFolder];
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($defaultDestination) {
            if ($filename === $defaultDestination) {
                return false;
            }
            throw new AssertionFailedError('is_file  should not be called');
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $defaultDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should fallback to use default destination if current env has no destination assigned and default destination is specified
     */
    public function it_should_fallback_to_use_default_destination_if_current_env_has_no_destination_assigned_and_default_destination_is_specified()
    {
        $fooDestinationFolder = '/foo';
        $barDestinationFolder = '/bar';
        $defaultDestinationFolder = '/default';
        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $barDestination = $barDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $defaultDestination = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $this->config = [
            'mode' => 'theme',
            'destination' => [
                'foo' => $fooDestinationFolder,
                'bar' => $barDestinationFolder,
                'default' => $defaultDestinationFolder,
            ],
        ];
        $matches = [$fooDestinationFolder, $barDestinationFolder, $defaultDestinationFolder];
        $this->event->getSettings()->willReturn(['current_environment' => 'another']);
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($defaultDestination) {
            if ($filename === $defaultDestination) {
                return false;
            }
            throw new AssertionFailedError('is_file should not be called');
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $defaultDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should read supported env from comma separated list of envs
     */
    public function it_should_read_supported_env_from_comma_separated_list_of_envs()
    {
        $fooDestinationFolder = '/foo';
        $defaultDestinationFolder = '/default';

        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $envDestinations = ['foo' => $fooDestinationFolder, 'default' => $defaultDestinationFolder];
        $this->config = ['mode' => 'plugin', 'destination' => $envDestinations];
        $this->event->getSettings()->willReturn(['current_environment' => 'some,other,env,foo']);

        $matches = [$fooDestinationFolder, $defaultDestinationFolder];
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($fooDestination) {
            if ($filename === $fooDestination) {
                return false;
            }
            throw new AssertionFailedError('is_file should not be called');
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $fooDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should fallback to first destination if specifying multiple envs and none supported
     */
    public function it_should_fallback_to_first_destination_if_specifying_multiple_envs_and_none_supported()
    {
        $fooDestinationFolder = '/foo';
        $gooDestinationFolder = '/goo';

        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $envDestinations = ['foo' => $fooDestinationFolder, 'goo' => $gooDestinationFolder];
        $this->config = ['mode' => 'plugin', 'destination' => $envDestinations];
        $this->event->getSettings()->willReturn(['current_environment' => 'bar, baz']);

        $matches = [$fooDestinationFolder, $gooDestinationFolder];
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($fooDestination) {
            if ($filename === $fooDestination) {
                return false;
            }
            throw new AssertionFailedError('is_file should not be called');
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $fooDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should fallback to default destination if specified and multiple envs and none supported
     */
    public function it_should_fallback_to_default_destination_if_specified_and_multiple_envs_and_none_supported()
    {
        $fooDestinationFolder = '/foo';
        $defaultDestinationFolder = '/default';

        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $defaultDestination = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $envDestinations = ['foo' => $fooDestinationFolder, 'default' => $defaultDestinationFolder];
        $this->config = ['mode' => 'plugin', 'destination' => $envDestinations];
        $this->event->getSettings()->willReturn(['current_environment' => 'bar, baz']);

        $matches = [$fooDestinationFolder, $defaultDestinationFolder];
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($matches) {
            return in_array($filename, $matches, true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($defaultDestination) {
            if ($filename === $defaultDestination) {
                return false;
            }
            throw new AssertionFailedError('is_file should not be called');
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $defaultDestination,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should throw if config specified root folder is not a folder
     */
    public function it_should_throw_if_config_specified_root_folder_is_not_a_folder()
    {
        $this->config['rootFolder'] = __DIR__;
        the_function::is_dir(__DIR__)->willReturn(false);

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should throw if config specified root folder is not readable
     */
    public function it_should_throw_if_config_specified_root_folder_is_not_readable()
    {
        $this->config['rootFolder'] = __DIR__;
        the_function::is_dir(__DIR__)->willReturn(true);
        the_function::is_readable(__DIR__)->willReturn(false);

        $this->expectException('Codeception\Exception\ExtensionException');

        $this->make_instance();
    }

    /**
     * @test
     * it should allow specifying the root folder in the configuration
     */
    public function it_should_allow_specifying_the_root_folder_in_the_configuration()
    {
        $rootFolder = __DIR__;
        $defaultDestinationFolder = '/default';
        $envDestinations = ['default' => $defaultDestinationFolder];
        $this->config = ['mode' => 'plugin', 'destination' => $envDestinations, 'rootFolder' => $rootFolder];
        $this->event->getSettings()->willReturn(['current_environment' => 'default']);
        $expected = $defaultDestinationFolder . DIRECTORY_SEPARATOR . basename($rootFolder);
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($expected) {
            return $filename === $expected ? false : is_file($filename);
        });
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($rootFolder, $defaultDestinationFolder) {
            return in_array($filename, [$defaultDestinationFolder, $rootFolder], true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($fileanme) use ($defaultDestinationFolder) {
            return $fileanme === $defaultDestinationFolder ?: is_writable($fileanme);
        });
        the_function::is_readable(Arg::type('string'))->will(static function ($fileanme) use ($defaultDestinationFolder) {
            return $fileanme === $defaultDestinationFolder ?: is_readable($fileanme);
        });
        the_function::symlink(
            rtrim($rootFolder, DIRECTORY_SEPARATOR),
            $expected,
            true
        )->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * @test
     * it should support array of root folders to allow for environments settings
     */
    public function it_should_support_array_of_root_folders_to_allow_for_environments_settings()
    {
        $settings = [
            'current_environment' => 'bar',
        ];
        $destination = '/some/path';
        $this->config = [
            'mode' => 'plugin',
            'rootFolder' => [
                'foo' => '/',
                'bar' => '/one',
                'baz' => '/two',
            ],
            'destination' => $destination,
        ];

        $this->event->getSettings()->willReturn($settings);

        $rootFolders = array_values($this->config['rootFolder']);
        $haystack = array_merge([$destination], $rootFolders);
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($haystack) {
            return in_array($filename, $haystack, true) ?: is_dir($filename);
        });
        the_function::is_readable(Arg::type('string'))->will(static function ($filename) use ($rootFolders) {
            return in_array($filename, $rootFolders, true) ?: is_readable($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($destination) {
            return $filename === $destination ?: is_writable($filename);
        });
        the_function::symlink('/one', $destination . '/one', true)->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * It should fall back to first root folder if array is provided, env does not match and default is not set
     *
     * @test
     */
    public function should_fall_back_to_first_root_folder_if_array_is_provided_env_does_not_match_and_default_is_not_set()
    {
        $settings = [
            'current_environment' => 'zoop',
        ];
        $destination = '/some/path';
        $this->config = [
            'mode' => 'plugin',
            'rootFolder' => [
                'foo' => '/first',
                'bar' => '/second',
                'baz' => '/third',
            ],
            'destination' => $destination,
        ];

        $this->event->getSettings()->willReturn($settings);

        $rootFolders = array_values($this->config['rootFolder']);
        $haystack = array_merge([$destination], $rootFolders);
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($haystack) {
            return in_array($filename, $haystack, true) ?: is_dir($filename);
        });
        the_function::is_readable(Arg::type('string'))->will(static function ($filename) use ($rootFolders) {
            return in_array($filename, $rootFolders, true) ?: is_readable($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($destination) {
            return $filename === $destination ?: is_writable($filename);
        });
        the_function::symlink('/first', $destination . '/first', true)->shouldBeCalled()->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * It should fall back to default root folder if array is provided and env does not match
     *
     * @test
     */
    public function should_fall_back_to_default_root_folder_if_array_is_provided_and_env_does_not_match()
    {
        $settings = [
            'current_environment' => 'zoop',
        ];
        $destination = '/some/path';
        $this->config = [
            'mode' => 'plugin',
            'rootFolder' => [
                'foo' => '/first',
                'bar' => '/second',
                'baz' => '/third',
                'default' => '/default'
            ],
            'destination' => $destination,
        ];

        $this->event->getSettings()->willReturn($settings);

        $rootFolders = array_values($this->config['rootFolder']);
        $haystack = array_merge([$destination, '/default'], $rootFolders);
        the_function::is_dir(Arg::type('string'))->will(static function ($dir) use ($haystack) {
            return in_array($dir, $haystack, true) ?: is_dir($dir);
        });
        the_function::is_readable(Arg::type('string'))->will(static function ($filename) use ($rootFolders) {
            return in_array($filename, $rootFolders, true) ?: is_readable($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($destination) {
            return $filename === $destination ?: is_writable($filename);
        });
        the_function::symlink('/default', $destination . '/default', true)->shouldBeCalled(true)->willReturn(true);

        $sut = $this->make_instance();

        $sut->symlink($this->event->reveal());
    }

    /**
     * It should throw if file cannot be symlinked
     *
     * @test
     */
    public function should_throw_if_file_cannot_be_symlinked()
    {
        $fooDestinationFolder = '/foo';
        $gooDestinationFolder = '/goo';

        $fooDestination = $fooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $gooDestination = $gooDestinationFolder . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $envDestinations = ['foo' => $fooDestinationFolder, 'goo' => $gooDestinationFolder];
        $this->config = ['mode' => 'plugin', 'destination' => $envDestinations];
        $this->event->getSettings()->willReturn([]);

        the_function::is_dir(Arg::type('string'))->will(static function ($filename) use ($fooDestinationFolder, $gooDestinationFolder) {
            return in_array($filename, [$fooDestinationFolder, $gooDestinationFolder], true) ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) use ($fooDestinationFolder, $gooDestinationFolder) {
            return in_array($filename, [$fooDestinationFolder, $gooDestinationFolder], true) ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($fooDestination, $gooDestination) {
            if (!in_array($filename, [$fooDestination, $gooDestination], true)) {
                return is_file($filename);
            }
            if ($filename === $fooDestination) {
                return false;
            }
            throw new AssertionFailedError('is_file shoould not be called with ' . $gooDestination);
        });
        the_function::symlink(
            rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR),
            $fooDestination,
            true
        )->will(static function () {
            throw new IOException('Symlink failed.');
        });

        $sut = $this->make_instance();

        $this->expectException(ExtensionException::class);
        $sut->symlink($this->event->reveal());
    }

    /**
     * It should throw if unlink operation throws
     *
     * @test
     */
    public function should_throw_if_unlink_operation_throws()
    {
        $dest = '/foo' . DIRECTORY_SEPARATOR . basename(codecept_root_dir());
        $this->config = ['mode' => 'plugin', 'destination' => ['foo' => '/foo']];
        $this->event->getSettings()->willReturn([]);
        the_function::is_dir(Arg::type('string'))->will(static function ($filename) {
            return $filename === '/foo' ?: is_dir($filename);
        });
        the_function::is_writable(Arg::type('string'))->will(static function ($filename) {
            return $filename === '/foo' ?: is_writable($filename);
        });
        the_function::is_file(Arg::type('string'))->will(static function ($filename) use ($dest) {
            return $filename === $dest ?: is_file($filename);
        });
        the_function::unlink(Arg::type('string'))->will(static function ($filename) use ($dest) {
            if ($filename !== $dest) {
                return unlink($filename);
            }

            throw new \Exception('Something happened');
        });
        $sut = $this->make_instance();
        $sut->setOutput(new BufferedOutput());

        $sut->unlink($this->event->reveal());
    }

    protected function _before()
    {
        if (!(PHP_VERSION_ID >= 70000 && extension_loaded('uopz'))) {
            $this->markTestSkipped('This test will require PHP 7.0+ and the uopz extension to run.');
        }

        $this->filename = __DIR__ . DIRECTORY_SEPARATOR . basename(codecept_root_dir());

        $this->event = $this->stubProphecy('\Codeception\Event\SuiteEvent');
        $this->printEvent = $this->stubProphecy('\Codeception\Event\PrintResultEvent');
    }

    protected function _after()
    {
        the_function::reset();
    }
}
