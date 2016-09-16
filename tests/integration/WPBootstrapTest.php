<?php


use Codeception\Command\WPBootstrap;
use Ofbeaton\Console\Tester\QuestionTester;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use function tad\WPBrowser\Tests\Support\rrmdir;

class WPBootstrapTest extends \Codeception\Test\Unit
{
    use QuestionTester;

    protected static $path;
    protected static $cwdBackup;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var array
     */
    protected $answerShiftReg = [];

    /**
     * @var OutputInterface
     */
    protected $outputInterface;

    /**
     * @var InputInterface
     */
    protected $inputInterface;
    /**
     * @var \IntegrationTester
     */
    protected $tester;


    public static function setUpBeforeClass()
    {
        self::$cwdBackup = getcwd();
        self::$path = codecept_data_dir('folder-structures/wpbootstrap-test-root');
    }

    protected function _before()
    {
        self::clean();
    }

    public static function tearDownAfterClass()
    {
        self::clean();
        chdir(self::$cwdBackup);
    }

    protected function testDir($relative = '')
    {
        $frag = $relative ? '/' . ltrim($relative, '/') : '';
        return self::$path . $frag;
    }

    protected static function clean()
    {
        rrmdir(self::$path . '/tests');
        foreach (glob(self::$path . '/*.*') as $file) {
            unlink($file);
        }
    }

    /**
     * @test
     * it should scaffold acceptance suite
     */
    public function it_should_scaffold_acceptance_suite()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true
        ]);

        $this->assertFileExists($this->testDir('tests/acceptance.suite.yml'));
    }

    /**
     * @test
     * it should scaffold functional test suite
     */
    public function it_should_scaffold_functional_test_suite()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true
        ]);

        $this->assertFileExists($this->testDir('tests/functional.suite.yml'));
    }

    /**
     * @test
     * it should allow user to specify params through questions for functional suite
     */
    public function it_should_allow_user_to_specify_params_through_questions_for_functional_suite()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($wpFolder));

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/functional.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertContains('mysql:host=mysql', $decoded['modules']['config']['WPDb']['dsn']);
        $this->assertContains('dbname=wpFuncTests', $decoded['modules']['config']['WPDb']['dsn']);
        $this->assertEquals('notRoot', $decoded['modules']['config']['WPDb']['user']);
        $this->assertEquals('notRootPass', $decoded['modules']['config']['WPDb']['password']);
        $this->assertEquals('wordpress_', $decoded['modules']['config']['WPDb']['tablePrefix']);
        $this->assertEquals('http://some.dev', $decoded['modules']['config']['WPDb']['url']);
        $this->assertEquals($wpFolder, $decoded['modules']['config']['WordPress']['wpRootFolder']);
        $this->assertEquals('luca', $decoded['modules']['config']['WordPress']['adminUsername']);
        $this->assertEquals('dadada', $decoded['modules']['config']['WordPress']['adminPassword']);
    }

    /**
     * @test
     * it should allow users to specify params through questions for acceptance suite
     */
    public function it_should_allow_users_to_specify_params_through_questions_for_acceptance_suite()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($wpFolder));

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/acceptance.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertContains('mysql:host=mysql', $decoded['modules']['config']['WPDb']['dsn']);
        $this->assertContains('dbname=wpFuncTests', $decoded['modules']['config']['WPDb']['dsn']);
        $this->assertEquals('notRoot', $decoded['modules']['config']['WPDb']['user']);
        $this->assertEquals('notRootPass', $decoded['modules']['config']['WPDb']['password']);
        $this->assertEquals('wordpress_', $decoded['modules']['config']['WPDb']['tablePrefix']);
        $this->assertEquals('http://some.dev', $decoded['modules']['config']['WPDb']['url']);
        $this->assertEquals('http://some.dev', $decoded['modules']['config']['WPBrowser']['url']);
        $this->assertEquals('luca', $decoded['modules']['config']['WPBrowser']['adminUsername']);
        $this->assertEquals('dadada', $decoded['modules']['config']['WPBrowser']['adminPassword']);
        $this->assertEquals('/wp-admin', $decoded['modules']['config']['WPBrowser']['adminPath']);
    }

    /**
     * @test
     * it should allow the database password to be empty
     */
    public function it_should_allow_the_database_password_to_be_empty()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $this->mockAnswers($command, array_merge(
                $this->getDefaultQuestionsAndAnswers($wpFolder),
                ['database password' => '']
            )
        );

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/acceptance.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertEquals('', $decoded['modules']['config']['WPDb']['password']);
    }

    public function differentFormatUrls()
    {
        return [
            ['https://some.dev'],
            ['http://localhost'],
            ['https://localhost'],
            ['http://localhost:8080'],
            ['https://localhost:8080'],
            ['http://192.168.1.22'],
            ['https://192.168.1.22'],
            ['http://192.168.1.22:8080'],
            ['https://192.168.1.22:8080']
        ];
    }

    /**
     * @test
     * it should allow for different format urls
     * @dataProvider differentFormatUrls
     */
    public function it_should_allow_for_different_format_urls($url)
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $this->mockAnswers($command, array_merge(
                $this->getDefaultQuestionsAndAnswers($wpFolder),
                ['WordPress.*url' => $url]
            )
        );

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/acceptance.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertEquals($url, $decoded['modules']['config']['WPBrowser']['url']);
    }

    public function adminPathsFormats()
    {
        return [
            ['/wp-admin', '/wp-admin'],
            ['wp-admin', '/wp-admin'],
            ['wp-admin/', '/wp-admin'],
            ['/wp-admin/', '/wp-admin']
        ];
    }

    /**
     * @test
     * it should allow different adminPath formats
     * @dataProvider adminPathsFormats
     */
    public function it_should_allow_different_admin_path_formats($adminPath, $expectedAdminPath)
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $this->mockAnswers($command, array_merge(
                $this->getDefaultQuestionsAndAnswers($wpFolder),
                ['path.*administration' => $adminPath]
            )
        );

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/acceptance.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertEquals($expectedAdminPath, $decoded['modules']['config']['WPBrowser']['adminPath']);
    }

    /**
     * @test
     * it should scaffold the integration suite config file
     */
    public function it_should_scaffold_the_integration_suite_config_file()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true
        ]);

        $this->assertFileExists($this->testDir('tests/integration.suite.yml'));
    }

    /**
     * @test
     * it should allow setting integration configuration with user provided params
     */
    public function it_should_allow_setting_integration_configuration_with_user_provided_params()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($wpFolder));

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/integration.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertEquals($wpFolder, $decoded['modules']['config']['WPLoader']['wpRootFolder']);
        $this->assertEquals('mysql', $decoded['modules']['config']['WPLoader']['dbHost']);
        $this->assertEquals('wpFuncTests', $decoded['modules']['config']['WPLoader']['dbName']);
        $this->assertEquals('notRoot', $decoded['modules']['config']['WPLoader']['dbUser']);
        $this->assertEquals('notRootPass', $decoded['modules']['config']['WPLoader']['dbPassword']);
        $this->assertEquals('integration_', $decoded['modules']['config']['WPLoader']['tablePrefix']);
        $this->assertEquals('some.dev', $decoded['modules']['config']['WPLoader']['domain']);
        $this->assertEquals('luca@theaveragedev.com', $decoded['modules']['config']['WPLoader']['adminEmail']);
    }

    /**
     * @test
     * it should allow user to specify plugins
     */
    public function it_should_allow_user_to_specify_plugins()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $expected = [
            'required/plugin.php',
            'acme/plugin-1.php',
            'acme/plugin-2.php'
        ];
        $this->mockAnswers($command, array_merge(
            $this->getDefaultQuestionsAndAnswers($wpFolder),
            ['(A|a)ctiv.*plugin(s)*' => $expected]
        ));

        $commandTester->execute([
            'command' => $command->getName(),
            'path' => $this->testDir(),
            '--no-build' => true,
            '--interactive' => true
        ]);

        $file = $this->testDir('tests/integration.suite.yml');

        $this->assertFileExists($file);

        $fileContents = file_get_contents($file);

        $this->assertNotEmpty($fileContents);

        $decoded = Yaml::parse($fileContents);

        $this->assertEquals($expected, $decoded['modules']['config']['WPLoader']['plugins']);
        $this->assertEquals($expected, $decoded['modules']['config']['WPLoader']['activatePlugins']);
    }

    /**
     * @param $command
     * @param $questionsAndAnswers
     */
    protected function mockAnswers($command, $questionsAndAnswers)
    {
        $this->mockQuestionHelper($command, function ($text, $order, Question $question) use ($questionsAndAnswers) {
            foreach ($questionsAndAnswers as $key => $value) {
                if (preg_match('/' . $key . '/', $text)) {
                    if (is_array($value)) {
                        $index = !isset($this->answerShiftReg[$key]) ? 0 : $this->answerShiftReg[$key] + 1;
                        if ($index > count($value) - 1) {
                            $value = '';
                        } else {
                            $value = $value[$index];
                            $this->answerShiftReg[$key] = $index;
                        }
                    }
                    return $value;
                }
            }

            // no question matched, fail
            throw new PHPUnit_Framework_AssertionFailedError("No mocked answer for question '$text'");
        });
    }

    /**
     * @param $wpFolder
     * @return array
     */
    protected function getDefaultQuestionsAndAnswers($wpFolder)
    {
        $questionsAndAnswers = [
            'database host' => 'mysql',
            'database name' => 'wpFuncTests',
            'database user' => 'notRoot',
            'database password' => 'notRootPass',
            'table prefix.*integration' => 'integration_',
            'table prefix.*' => 'wordpress_',
            'WordPress.*url' => 'http://some.dev',
            'WordPress.*domain' => 'some.dev',
            'WordPress.*root directory' => $wpFolder,
            '(A|a)dmin.*username' => 'luca',
            '(A|a)dmin.*password' => 'dadada',
            '(A|a)dmin.*email' => 'luca@theaveragedev.com',
            'path.*administration' => '/wp-admin',
            '(A|a)ctiv.*plugin(s)*' => ''
        ];
        return $questionsAndAnswers;
    }

}
