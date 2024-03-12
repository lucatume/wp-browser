<?php


namespace lucatume\WPBrowser\Command;

use Codeception\Configuration;
use lucatume\WPBrowser\Extension\BuiltInServerController;
use lucatume\WPBrowser\Extension\ChromeDriverController;
use lucatume\WPBrowser\Extension\DockerComposeController;
use lucatume\WPBrowser\Extension\EventDispatcherBridge;
use lucatume\WPBrowser\Tests\Traits\ClassStubs;
use lucatume\WPBrowser\Traits\UopzFunctions;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DevStartTest extends \Codeception\Test\Unit
{
    use UopzFunctions;
    use ClassStubs;

    /**
     * It should start nothing if there are no service extensions
     *
     * @test
     */
    public function should_start_nothing_if_there_are_no_service_extensions(): void
    {
        $mockConfig = [];
        $this->setMethodReturn(Configuration::class, 'config', $mockConfig);
        $input = new StringInput('');
        $output = new BufferedOutput();

        $command = new DevStart();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertStringContainsString('No services to start.', $output->fetch());
    }

    /**
     * It should start each service extension found in configuration
     *
     * @test
     */
    public function should_start_each_service_extension_found_in_configuration(): void
    {
        $builtInServerControllerBuildArgs = null;
        $builtInServerControllerStarted = false;
        $this->setClassMock(BuiltInServerController::class,
            $this->makeEmptyClass(BuiltInServerController::class, [
                '__construct' => function () use (&$builtInServerControllerBuildArgs) {
                    $builtInServerControllerBuildArgs = func_get_args();
                },
                'start' => function () use (&$builtInServerControllerStarted) {
                    $builtInServerControllerStarted = true;
                }
            ]));
        $dockerComposeControllerBuildArgs = null;
        $dockerComposeControllerStarted = false;
        $this->setClassMock(DockerComposeController::class,
            $this->makeEmptyClass(DockerComposeController::class, [
                '__construct' => function () use (&$dockerComposeControllerBuildArgs) {
                    $dockerComposeControllerBuildArgs = func_get_args();
                },
                'start' => function () use (&$dockerComposeControllerStarted) {
                    $dockerComposeControllerStarted = true;
                }
            ]));
        $chromeDriverControllerBuildArgs = null;
        $chromeDriverControllerStarted = false;
        $this->setClassMock(ChromeDriverController::class,
            $this->makeEmptyClass(ChromeDriverController::class, [
                '__construct' => function () use (&$chromeDriverControllerBuildArgs) {
                    $chromeDriverControllerBuildArgs = func_get_args();
                },
                'start' => function () use (&$chromeDriverControllerStarted) {
                    $chromeDriverControllerStarted = true;
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

        $this->setMethodReturn(Configuration::class, 'config', $mockConfig, false);
        $input = new StringInput('');
        $output = new BufferedOutput();

        $command = new DevStart();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertEquals([
            [
                'docroot' => __DIR__
            ],
            []
        ], $builtInServerControllerBuildArgs);
        $this->assertTrue($builtInServerControllerStarted);
        $this->assertEquals([
            [
                'compose-file' => __FILE__
            ],
            []
        ], $dockerComposeControllerBuildArgs);
        $this->assertTrue($dockerComposeControllerStarted);
        $this->assertEquals([
            [
                'port' => 2389
            ],
            []
        ], $chromeDriverControllerBuildArgs);
        $this->assertTrue($chromeDriverControllerStarted);
    }
}
