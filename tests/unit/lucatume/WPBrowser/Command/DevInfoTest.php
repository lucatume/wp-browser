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
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class DevInfoTest extends \Codeception\Test\Unit
{
    use UopzFunctions;
    use ClassStubs;
    use SnapshotAssertions;

    /**
     * It should not display any info if there are no service extensions
     *
     * @test
     */
    public function should_not_display_any_info_if_there_are_no_service_extensions(): void
    {
        $mockConfig = [];
        $this->setMethodReturn(Configuration::class, 'config', $mockConfig);
        $input = new StringInput('');
        $output = new BufferedOutput();

        $command = new DevInfo();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertStringContainsString('No services extensions found.', $output->fetch());
    }

    /**
     * It should print information about each service extension found in configuration
     *
     * @test
     */
    public function should_print_information_about_each_service_extension_found_in_configuration(): void
    {

        $builtInServerControllerBuildArgs = null;
        $this->setClassMock(BuiltInServerController::class,
            $this->makeEmptyClass(BuiltInServerController::class, [
                '__construct' => function () use (&$builtInServerControllerBuildArgs) {
                    $builtInServerControllerBuildArgs = func_get_args();
                },
                'getPrettyName' => fn() => 'BuiltInServer',
                'getInfo' => function () {
                    return [
                        'name' => 'BuiltInServerController',
                        'description' => 'A built-in PHP server controller.',
                        'version' => '1.0.0',
                        'docroot' => 'var/wordpress'
                    ];
                }
            ]));
        $dockerComposeControllerBuildArgs = null;
        $this->setClassMock(DockerComposeController::class,
            $this->makeEmptyClass(DockerComposeController::class, [
                '__construct' => function () use (&$dockerComposeControllerBuildArgs) {
                    $dockerComposeControllerBuildArgs = func_get_args();
                },
                'getPrettyName' => fn() => 'DockerCompose',
                'getInfo' => function () {
                    return [
                        'name' => 'DockerComposeController',
                        'description' => 'A Docker Compose controller.',
                        'version' => '1.0.0',
                        'compose-file' => 'docker-compose.yml'
                    ];
                }
            ]));
        $chromeDriverControllerBuildArgs = null;
        $this->setClassMock(ChromeDriverController::class,
            $this->makeEmptyClass(ChromeDriverController::class, [
                '__construct' => function () use (&$chromeDriverControllerBuildArgs) {
                    $chromeDriverControllerBuildArgs = func_get_args();
                },
                'getPrettyName' => fn() => 'ChromeDriver',
                'getInfo' => function () {
                    return [
                        'name' => 'ChromeDriverController',
                        'description' => 'A ChromeDriver controller.',
                        'version' => '1.0.0',
                        'port' => 2389
                    ];
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

        $command = new DevInfo();
        $exit = $command->run($input, $output);

        $this->assertEquals(0, $exit);
        $this->assertEquals([
            [
                'docroot' => __DIR__
            ],
            []
        ], $builtInServerControllerBuildArgs);
        $this->assertEquals([
            [
                'compose-file' => __FILE__
            ],
            []
        ], $dockerComposeControllerBuildArgs);
        $this->assertEquals([
            [
                'port' => 2389
            ],
            []
        ], $chromeDriverControllerBuildArgs);
        $this->assertMatchesStringSnapshot($output->fetch());
    }
}
