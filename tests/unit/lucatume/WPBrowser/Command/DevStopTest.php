<?php


namespace lucatume\WPBrowser\Command;

use Codeception\Configuration;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Extension\DockerComposeController;
use lucatume\WPBrowser\Extension\EventDispatcherBridge;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DevStopTest extends \Codeception\Test\Unit
{
    use UopzFunctions;
    use ClassStubs;

    /**
     * It should stop nothing if there are no service extensions
     *
     * @test
     */
    public function should_stop_nothing_if_there_are_no_service_extensions(): void
    {
        $mockConfig = [];
        $this->uopzSetStaticMethodReturn(Configuration::class, 'config', $mockConfig);
        $input = new StringInput('');
        $output = new BufferedOutput();

        $command = new DevStop();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertStringContainsString('No services to stop.', $output->fetch());
    }

    /**
     * It should stop each service extension found in configuration
     *
     * @test
     */
    public function should_start_each_service_extension_found_in_configuration(): void
    {
        $builtInServerControllerBuildArgs = null;
        $builtInServerControllerStopped = false;
        $this->uopzSetMock(BuiltInServerController::class,
            $this->makeEmptyClass(BuiltInServerController::class, [
                '__construct' => function () use (&$builtInServerControllerBuildArgs) {
                    $builtInServerControllerBuildArgs = func_get_args();
                },
                'stop' => function () use (&$builtInServerControllerStopped) {
                    $builtInServerControllerStopped = true;
                }
            ]));
        $dockerComposeControllerBuildArgs = null;
        $dockerComposeControllerStopped = false;
        $this->uopzSetMock(DockerComposeController::class,
            $this->makeEmptyClass(DockerComposeController::class, [
                '__construct' => function () use (&$dockerComposeControllerBuildArgs) {
                    $dockerComposeControllerBuildArgs = func_get_args();
                },
                'stop' => function () use (&$dockerComposeControllerStopped) {
                    $dockerComposeControllerStopped = true;
                }
            ]));
        $chromeDriverControllerBuildArgs = null;
        $crhomeDriverControllerStopped = false;
        $this->uopzSetMock(ChromeDriverController::class,
            $this->makeEmptyClass(ChromeDriverController::class, [
                '__construct' => function () use (&$chromeDriverControllerBuildArgs) {
                    $chromeDriverControllerBuildArgs = func_get_args();
                },
                'stop' => function () use (&$crhomeDriverControllerStopped) {
                    $crhomeDriverControllerStopped = true;
                }
            ]));
        $mockConfig = [
            'extensions' => [
                'enabled' => [
                    [
                        BuiltInServerController::class => null
                    ],
                    [
                        ChromeDriverController::class => null
                    ],
                    DockerComposeController::class => [
                        [
                            'compose-file' => __FILE__
                        ]
                    ],
                    2 => EventDispatcherBridge::class,
                ],
                'config' => [
                    BuiltInServerController::class => [
                        'docroot' => __DIR__
                    ],
                    ChromeDriverController::class => [
                        'port' => 2389
                    ]
                ]
            ],
        ];

        $this->uopzSetStaticMethodReturn(Configuration::class, 'config', $mockConfig, false);
        $input = new StringInput('');
        $output = new BufferedOutput();

        $command = new DevStop();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertEquals([
            [
                'docroot' => __DIR__
            ],
            []
        ], $builtInServerControllerBuildArgs);
        $this->assertTrue($builtInServerControllerStopped);
        $this->assertEquals([
            [
                'compose-file' => __FILE__
            ],
            []
        ], $dockerComposeControllerBuildArgs);
        $this->assertTrue($dockerComposeControllerStopped);
        $this->assertEquals([
            [
                'port' => 2389
            ],
            []
        ], $chromeDriverControllerBuildArgs);
        $this->assertTrue($crhomeDriverControllerStopped);
    }
}
