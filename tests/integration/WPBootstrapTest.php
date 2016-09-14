<?php


use Codeception\Command\WPBootstrap;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WPBootstrapTest extends \Codeception\Test\Unit
{
    /**
     * @var vfsStreamDirectory
     */
    protected $root;
    protected $rootPath;
    protected $outputInterface;
    protected $inputInterface;
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function _before()
    {
        $this->inputInterface = $this->prophesize(InputInterface::class);
        $this->outputInterface = $this->prophesize(OutputInterface::class);

        $this->root = vfsStream::setup();
        $this->rootPath = $this->root->url();

        chdir($this->rootPath);
    }

    protected function _after()
    {
    }

    /**
     * @test
     * it should scaffold acceptance suite
     */
    public function it_should_scaffold_acceptance_suite()
    {
        $wpBootstrap = new WPBootstrap();
        $wpBootstrap->execute($this->inputInterface->reveal(), $this->outputInterface->reveal());

        $this->assertFileExists($this->rootPath .'/tests/acceptance.suite.yml');
    }
}