<?php


use Codeception\Command\WPBootstrap;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WPBootstrapTest extends \Codeception\Test\Unit
{
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
        self::clean();
    }

    protected function _before()
    {
        $this->inputInterface = $this->prophesize(InputInterface::class);
        $this->outputInterface = $this->prophesize(OutputInterface::class);
        $this->inputInterface->getOption('namespace')->willReturn('');
        $this->inputInterface->getOption('actor')->willReturn('Tester');
        $this->inputInterface->getOption('empty')->willReturn(false);
        $this->inputInterface->getOption('build')->willReturn(false);
        $this->inputInterface->getArgument('path')->willReturn($this->testDir());
    }

    protected function _after()
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
        $wpBootstrap = new WPBootstrap('bootstrap');
        $wpBootstrap->execute($this->inputInterface->reveal(), $this->outputInterface->reveal());

        $this->assertFileExists($this->testDir('tests/acceptance.suite.yml'));
    }

}
