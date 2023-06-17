<?php

namespace lucatume\WPBrowser\Extension;

use Closure;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Test\Unit;
use Generator;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Filesystem\Exception\IOException;

class SymlinkerTest extends Unit
{
    use UopzFunctions;

    protected $backupGlobals = false;
    protected array $config = ['destination' => __DIR__];
    private array $options = ['silent' => true];
    protected string $filename;

    private function makeInstance(): Symlinker
    {
        return new Symlinker($this->config, $this->options);
    }

    /**
     * @test
     * it should throw if symlinking destination is missing
     */
    public function it_should_throw_if_symlinking_destination_is_missing(): void
    {
        $this->config = [];

        $this->expectException(ExtensionException::class);

        $this->makeInstance();
    }

    /**
     * @test
     * it should throw if destination is not dir
     */
    public function it_should_throw_if_destination_is_not_dir(): void
    {
        $this->config = ['destination' => __FILE__];

        $this->expectException(ExtensionException::class);

        $this->makeInstance();
    }

    public function bad_root_folder_provider(): Generator
    {
        yield 'not a string in config' => [
            [
                'rootFolder' => 231,
                'destination' => __DIR__
            ],
            []
        ];
        yield 'not readable dir in config' => [
            [
                'rootFolder' => __DIR__,
                'destination' => __DIR__
            ],
            [],
            function () {
                $this->uopzSetFunctionReturn('is_readable', function (string $file) {
                    return $file !== __DIR__ && is_readable($file);
                }, true);
            }
        ];
        yield 'not a dir in config' => [
            [
                'rootFolder' => __FILE__,
                'destination' => __DIR__
            ],
            []
        ];
        yield 'not a string in env based config, no env specfied' => [
            [
                'rootFolder' => [
                    'first' => 231,
                    'second' => __DIR__
                ],
                'destination' => __DIR__,
            ],
            []
        ];
        yield 'not a string in env based config, env specfied' => [
            [
                'rootFolder' => [
                    'first' => 231,
                    'second' => __DIR__
                ],
                'destination' => __DIR__,
            ],
            ['current_environment' => 'first']
        ];
        yield 'not a string in env based config, default env specfied' => [
            [
                'rootFolder' => [
                    'default' => 231,
                    'second' => __DIR__
                ],
                'destination' => __DIR__,
            ],
            []
        ];
        yield 'not readable dir in env' => [
            [
                'rootFolder' => [
                    'default' => __DIR__,
                    'second' => __DIR__
                ],
                'destination' => __DIR__
            ],
            [],
            function () {
                $this->uopzSetFunctionReturn('is_readable', function (string $file) {
                    return $file !== __DIR__ && is_readable($file);
                }, true);
            }
        ];
        yield 'not readable dir in default env' => [
            [
                'rootFolder' => [
                    'default' => __DIR__,
                    'second' => __DIR__
                ],
                'destination' => __DIR__
            ],
            ['current_environment' => 'default'],
            function () {
                $this->uopzSetFunctionReturn('is_readable', function (string $file) {
                    return $file !== __DIR__ && is_readable($file);
                }, true);
            }
        ];
        yield 'not readable dir in specific env' => [
            [
                'rootFolder' => [
                    'default' => __DIR__,
                    'second' => __DIR__
                ],
                'destination' => __DIR__
            ],
            ['current_environment' => 'second'],
            function () {
                $this->uopzSetFunctionReturn('is_readable', function (string $file) {
                    return $file !== __DIR__ && is_readable($file);
                }, true);
            }
        ];
    }

    /**
     * It should throw on bad rootFolder
     *
     * @test
     * @dataProvider bad_root_folder_provider
     */
    public function should_throw_on_bad_root_folder(array $config, array $eventConfig, Closure $fixture = null): void
    {
        $fixture && $fixture();
        $this->expectException(ExtensionException::class);

        $this->config = $config;
        $this->makeInstance()->symlink(new SuiteEvent(null, $eventConfig));
    }

    /**
     * @test
     * it should throw if destination is not writeable
     */
    public function it_should_throw_if_destination_is_not_writeable(): void
    {
        $this->config = ['destination' => __DIR__];
        $thisDir = __DIR__;
        $this->uopzSetFunctionReturn('is_writable', static function (string $file) use ($thisDir) {
            return $file !== $thisDir && is_writable($file);
        }, true);

        $this->expectException(ExtensionException::class);

        $this->makeInstance();
    }

    public function symlink_data_provider(): Generator
    {

        $destination = FS::tmpDir('symlinker_');
        $rootFolder = FS::tmpDir('symlinker_');

        yield 'rootFolder in config' => [
            ['destination' => $destination, 'rootFolder' => $rootFolder],
            [],
            $rootFolder,
            $destination . '/' . basename($rootFolder),
        ];

        yield 'no rootFolder in config' => [
            ['destination' => $destination],
            [],
            rtrim(codecept_root_dir(), '/'),
            $destination . '/' . basename(codecept_root_dir())
        ];

        $rootFolder2 = FS::tmpDir('symlinker_');
        yield 'array rootFolder in config w/ default, no env specified' => [
            [
                'destination' => $destination,
                'rootFolder' => [
                    'default' => $rootFolder,
                    'alternate' => $rootFolder2,
                ]
            ],
            [],
            $rootFolder,
            $destination . '/' . basename($rootFolder)
        ];
        yield 'array rootFolder in config w/ default, default env specified' => [
            [
                'destination' => $destination,
                'rootFolder' => [
                    'default' => $rootFolder,
                    'alternate' => $rootFolder2,
                ]
            ],
            ['current_environment' => 'default'],
            $rootFolder,
            $destination . '/' . basename($rootFolder)
        ];
        yield 'array rootFolder in config w/ default, alternate env specified' => [
            [
                'destination' => $destination,
                'rootFolder' => [
                    'default' => $rootFolder,
                    'alternate' => $rootFolder2,
                ]
            ],
            ['current_environment' => 'alternate'],
            $rootFolder2,
            $destination . '/' . basename($rootFolder2)
        ];
        yield 'array rootFolder in config w/ default, non existing env specified' => [
            [
                'destination' => $destination,
                'rootFolder' => [
                    'default' => $rootFolder,
                    'alternate' => $rootFolder2,
                ]
            ],
            ['current_environment' => 'not-existing'],
            $rootFolder,
            $destination . '/' . basename($rootFolder)
        ];
        yield 'array rootFolder in config w/ default, multiple envs specified' => [
            [
                'destination' => $destination,
                'rootFolder' => [
                    'default' => $rootFolder,
                    'alternate' => $rootFolder2,
                ]
            ],
            ['current_environment' => 'default,alternate,not-existing'],
            $rootFolder,
            $destination . '/' . basename($rootFolder)
        ];
    }

    /**
     * @test
     * it should symlink and unlink the root folder into the destination correctly
     *
     * @dataProvider symlink_data_provider
     */
    public function it_should_symlink_and_unlink_the_root_folder_into_the_destination_correctly(
        array $config,
        array $eventConfig,
        string $expectedTarget,
        string $expectedLink
    ): void {
        $this->config = $config;

        $sut = $this->makeInstance();

        $this->assertTrue($sut->symlink(new SuiteEvent(null, $eventConfig)));

        $this->assertTrue(is_link($expectedLink));
        $this->assertEquals($expectedTarget, readlink($expectedLink));

        $sut->unlink(new SuiteEvent(null, $eventConfig));

        $this->assertFalse(is_link($expectedLink));
    }

    /**
     * @test
     * it should not attempt re-linking if directory exists already
     */
    public function it_should_not_attempt_re_linking_if_directory_exists_already(): void
    {
        $fs = FS::tmpDir('symlinker_', [
            'my-plugin' => [
                'plugin.php' => '<?php //Something',
            ],
            'destination' => [
                'my-plugin' => [
                    'plugin.php' => '<?php //Something',
                ]
            ]
        ]);
        $this->config = ['rootFolder' => $fs . '/my-plugin', 'destination' => $fs . '/destination'];

        $sut = $this->makeInstance();

        $this->assertFalse($sut->symlink(new SuiteEvent(null, [])));
    }

    /**
     * It should throw if symliking fails
     *
     * @test
     */
    public function should_throw_if_symliking_fails(): void
    {
        $this->uopzSetFunctionReturn('symlink', false);

        $this->expectException(ExtensionException::class);

        $this->config = ['rootFolder' => __DIR__, 'destination' => __DIR__ . '/link'];
        $this->makeInstance()->symlink(new SuiteEvent(null, []));
    }

    /**
     * It should throw if unlinking fails
     *
     * @test
     */
    public function should_throw_if_unlinking_fails(): void
    {
        $source = FS::tmpDir('symlinker_');
        $destination = FS::tmpDir('symlinker_');
        $this->uopzSetFunctionReturn('unlink', false);
        $this->config = ['rootFolder' => $source, 'destination' => $destination];
        if (!symlink($source, $destination . '/' . basename($source))) {
            $this->fail('Could not create symlink');
        }

        $this->assertFalse($this->makeInstance()->unlink(new SuiteEvent(null, [])));
    }
}
