<?php


use Codeception\Command\WPBootstrap;
use Ofbeaton\Console\Tester\QuestionTester;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class WPBootstrapTest extends \Codeception\Test\Unit
{
    use QuestionTester;

    protected static $path;
    protected static $cwdBackup;

    /**
     * @var Application
     */
    public $application;

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
     * it should allow user to specify params through question for functional suite
     */
    public function it_should_allow_user_to_specify_params_through_question_for_functional_suite()
    {
        $app = new Application();
        $app->add(new WPBootstrap('bootstrap'));
        $command = $app->find('bootstrap');
        $commandTester = new CommandTester($command);

        $wpFolder = getenv('wpFolder') ? getenv('wpFolder') : '/Users/Luca/Sites/wordpress';

        $questionsAndAnswers = [
            'database host' => 'mysql',
            'database name' => 'wpFuncTests',
            'database user' => 'notRoot',
            'database password' => 'notRootPass',
            'table prefix' => 'func_',
            'WordPress.*url' => 'http://some.dev',
            'WordPress.*root directory' => $wpFolder,
            '(A|a)dmin.*username' => 'luca',
            '(A|a)dmin.*password' => 'dadada'
        ];

        $this->mockAnswers($command, $questionsAndAnswers);

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
        $this->assertEquals('func_', $decoded['modules']['config']['WPDb']['tablePrefix']);
        $this->assertEquals('http://some.dev', $decoded['modules']['config']['WPDb']['url']);
        $this->assertEquals($wpFolder, $decoded['modules']['config']['WordPress']['wpRootFolder']);
        $this->assertEquals('luca', $decoded['modules']['config']['WordPress']['adminUsername']);
        $this->assertEquals('dadada', $decoded['modules']['config']['WordPress']['adminPassword']);
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
                    return $value;
                }
            }

            // no question matched, fail
            throw new PHPUnit_Framework_AssertionFailedError();
        });
    }

}
