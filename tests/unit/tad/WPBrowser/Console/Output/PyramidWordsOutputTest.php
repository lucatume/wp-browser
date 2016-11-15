<?php
namespace tad\WPBrowser\Console\Output;


use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class PyramidWordsOutputTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf(PyramidWordsOutput::class, $sut);
	}

	/**
	 * @return PyramidWordsOutput
	 */
	private function make_instance()
	{
		return new PyramidWordsOutput($this->output->reveal());
	}

	/**
	 * @test
	 * it should replace the word functional with service
	 */
	public function it_should_replace_the_word_functional_with_service()
	{
		$line = 'some functional test';

		$this->output->write('some service test', Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln('some service test', Argument::any())->shouldBeCalled();


		$sut = $this->make_instance();
		$sut->writeln($line);
		$sut->write($line);
	}

	/**
	 * @test
	 * it should preserve case
	 */
	public function it_should_preserve_case()
	{
		$line = 'some functional test Functional acceptance Acceptance';

		$this->output->write('some service test Service UI UI', Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln('some service test Service UI UI', Argument::any())->shouldBeCalled();


		$sut = $this->make_instance();
		$sut->writeln($line);
		$sut->write($line);
	}

	/**
	 * @test
	 * it should replace in arrays
	 */
	public function it_should_replace_in_arrays()
	{
		$line = ['some functional test Functional', 'foo bar functional', 'foo Acceptance bar acceptance'];
		$expected = ['some service test Service', 'foo bar service', 'foo UI bar UI'];

		$this->output->write($expected, Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln($expected, Argument::any())->shouldBeCalled();


		$sut = $this->make_instance();
		$sut->writeln($line);
		$sut->write($line);
	}

	protected function _before()
	{
		$this->output = $this->prophesize(OutputInterface::class);
	}

	protected function _after()
	{
	}
}