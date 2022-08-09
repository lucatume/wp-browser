<?php namespace Codeception\Template;

use lucatume\WPBrowser\Template\Wpbrowser;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Map;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class WpbrowserTest extends \Codeception\Test\Unit
{

    public function dbInstallationDataProvider()
    {
        yield 'default' => [
            [],
            [
                'TEST_SITE_DB_DSN'      => 'mysql:host=localhost;dbname=test',
                'TEST_SITE_DB_HOST'     => 'localhost',
                'TEST_SITE_DB_NAME'     => 'test',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => 'password',
                'TEST_DB_HOST'          => 'localhost',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => 'password'
            ]
        ];

        yield 'MySQL on localhost' => [
            [
                'testSiteDbHost'     => 'localhost',
                'testSiteDbName'     => 'wp',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => '',
                'testDbHost'         => 'localhost',
                'testDbName'         => 'wpTests',
                'testDbUser'         => 'root',
                'testDbPassword'     => '',
            ],
            [
                'TEST_SITE_DB_DSN'      => 'mysql:host=localhost;dbname=wp',
                'TEST_SITE_DB_HOST'     => 'localhost',
                'TEST_SITE_DB_NAME'     => 'wp',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => '',
                'TEST_DB_HOST'          => 'localhost',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => ''
            ]
        ];

        yield 'MySQL on IP Address' => [
            [
                'testSiteDbHost'     => '1.2.3.4',
                'testSiteDbName'     => 'wp',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => '',
                'testDbHost'         => '1.2.3.4',
                'testDbName'         => 'wpTests',
                'testDbUser'         => 'root',
                'testDbPassword'     => '',
            ],
            [
                'TEST_SITE_DB_DSN'      => 'mysql:host=1.2.3.4;dbname=wp',
                'TEST_SITE_DB_HOST'     => '1.2.3.4',
                'TEST_SITE_DB_NAME'     => 'wp',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => '',
                'TEST_DB_HOST'          => '1.2.3.4',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => ''
            ]
        ];

        yield 'MySQL on IP Address w/ port' => [
            [
                'testSiteDbHost'     => '1.2.3.4:8989',
                'testSiteDbName'     => 'wp',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => 'password',
                'testDbHost'         => '1.2.3.4:8989',
                'testDbName'         => 'wpTests',
                'testDbUser'         => 'root',
                'testDbPassword'     => 'password',
            ],
            [
                'TEST_SITE_DB_DSN'      => 'mysql:host=1.2.3.4;port=8989;dbname=wp',
                'TEST_SITE_DB_HOST'     => '1.2.3.4:8989',
                'TEST_SITE_DB_NAME'     => 'wp',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => 'password',
                'TEST_DB_HOST'          => '1.2.3.4:8989',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => 'password'
            ]
        ];

        yield 'MySQL on unix socket' => [
            [
                'testSiteDbHost'     => '/var/mysql.sock',
                'testSiteDbName'     => 'tests',
                'testSiteDbUser'     => 'root',
                'testSiteDbPassword' => 'password',
                'testDbHost'         => '/var/mysql.sock',
                'testDbName'         => 'tests',
                'testDbUser'         => 'root',
                'testDbPassword'     => 'password',
            ],
            [
                'TEST_SITE_DB_DSN'      => 'mysql:unix_socket=/var/mysql.sock;dbname=tests',
                'TEST_SITE_DB_HOST'     => 'localhost:/var/mysql.sock',
                'TEST_SITE_DB_NAME'     => 'tests',
                'TEST_SITE_DB_USER'     => 'root',
                'TEST_SITE_DB_PASSWORD' => 'password',
                'TEST_DB_HOST'          => 'localhost:/var/mysql.sock',
                'TEST_DB_USER'          => 'root',
                'TEST_DB_PASSWORD'      => 'password'
            ]
        ];
    }

    /**
     * It should correctly scaffold db vars
     *
     * @test
     * @dataProvider dbInstallationDataProvider
     */
    public function should_correctly_scaffold_db_vars(
        $installationDataOverrides,
        $expected
    ) {
        $template         = new Wpbrowser(new ArrayInput([]), new NullOutput());
        $installationData = ( array_merge($template->getDefaultInstallationData(), $installationDataOverrides) );

        $envVars = $template->getEnvFileVars(new Map($installationData));

        foreach ($expected as $key => $value) {
            $this->assertEquals(
                $value,
                $envVars[ $key ],
                "Expected {$key} value: '{$value}', got '{$envVars[$key]}' instead."
            );
        }
    }

    public function projectTypes()
    {
        $base = [
            'one/plugin.php',
            'two/plugin.php',
            'three/plugin.php'
        ];

        return [
            'plugin' => [ 'plugin', array_merge($base, ['some/plugin.php']) ],
            'theme'  => [ 'theme', $base],
            'site'   => [ 'both', $base ],
        ];
    }

    /**
     * It should correctly use and scaffold required plugins
     *
     * @test
     * @dataProvider projectTypes
     */
    public function should_correctly_use_and_scaffold_required_plugins($projectType, array $expectedPlugins)
    {
        $questionHelper = $this->make(QuestionHelper::class, array(
            'ask' => static function (
                InputInterface $input,
                OutputInterface $output,
                Question $question
            ) use (
                $projectType
            ) {
                static $i;
                $i            = $i ?: 0;
                $pluginMap    = array( 'one/plugin.php', 'two/plugin.php', 'three/plugin.php' );
                $questionText = $question->getQuestion();

                if (stripos($questionText, 'I acknowledge') !== false) {
                    return 'yes';
                }

                if (stripos($questionText, 'plugin, a theme or a combination of both') !== false) {
                    return $projectType;
                }

                if (stripos($questionText, 'project needs additional plugins') !== false) {
                    return "y";
                }

                if (stripos($questionText, 'name of the plugin?') !== false) {
                    return 'some/plugin.php';
                }

                if (stripos($questionText, 'enter the plugin') !== false) {
                    return isset($pluginMap[ $i ]) ? $pluginMap[ $i ++ ] . "\n" : "";
                }

                return "";
            }
        ));
        $workDir        = codecept_output_dir('Wpbrowser/' . __FUNCTION__);
        if (is_dir($workDir)) {
            FS::rrmdir($workDir);
        }
        if (! ( mkdir($workDir, 0777, true) && is_dir($workDir) )) {
            throw new \RuntimeException('Could not create test output directory.');
        }
        $input = $this->makeEmpty(InputInterface::class);

        $template = new Wpbrowser($input, new NullOutput());
        $template->setQuestionHelper($questionHelper);
        $template->setCreateHelpers(false);
        $template->setCreateActors(false);
        $template->setCheckComposerConfig(false);
        $template->setCreateSuiteConfigFiles(true);
        $template->setWorkDir($workDir);
        $template->setup(true);

        $wpunitSuiteConfigFile = $workDir . '/tests/wpunit.suite.yml';
        $this->assertFileExists($wpunitSuiteConfigFile);
        $parsed = Yaml::parse(file_get_contents($wpunitSuiteConfigFile));
        $this->assertTrue(isset($parsed['modules']['config']['WPLoader']['plugins']));
        $this->assertTrue(isset($parsed['modules']['config']['WPLoader']['activatePlugins']));
        $this->assertEquals($expectedPlugins, $parsed['modules']['config']['WPLoader']['plugins']);
        $this->assertEquals($expectedPlugins, $parsed['modules']['config']['WPLoader']['activatePlugins']);
    }
}
