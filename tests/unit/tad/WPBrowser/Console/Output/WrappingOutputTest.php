<?php
namespace tad\WPBrowser\Console\Output;


use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class WrappingOutputTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf(WrappingOutput::class, $sut);
	}

	/**
	 * @return WrappingOutput
	 */
	private function make_instance()
	{
		return new WrappingOutput($this->output->reveal());
	}

	/**
	 * @test
	 * it should wrap a line before sending it to the output
	 */
	public function it_should_wrap_a_line_before_sending_it_to_the_output()
	{
		$line = 'lorem dolor';
		$wrapAt = 5;
		$expectedLines = [
			'lorem',
			'dolor'
		];

		$this->output->write(implode("\n", $expectedLines), Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln(implode("\n", $expectedLines), Argument::any())->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->wrapAt($wrapAt);
		$sut->write($line);
		$sut->writeln($line);
	}

	/**
	 * @test
	 * it should not break long words
	 */
	public function it_should_not_break_long_words()
	{
		$line = 'loremdolor';
		$wrapAt = 5;

		$this->output->write('loremdolor', Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln('loremdolor', Argument::any())->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->wrapAt($wrapAt);
		$sut->write($line);
		$sut->writeln($line);
	}

	/**
	 * @test
	 * it should break long sentences in multiple messages
	 */
	public function it_should_break_long_sentences_in_multiple_messages()
	{
		$line1 = 'lorem dolor';
		$line2 = 'ipsum amet';
		$line3 = 'foo dolor';
		$wrapAt = 5;

		$this->output->write([
			"lorem\ndolor",
			"ipsum\namet",
			"foo\ndolor",
		], Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln([
			"lorem\ndolor",
			"ipsum\namet",
			"foo\ndolor",
		], Argument::any())->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->wrapAt($wrapAt);
		$sut->write([$line1, $line2, $line3]);
		$sut->writeln([$line1, $line2, $line3]);
	}

	/**
	 * @test
	 * it should preserve lines below width
	 */
	public function it_should_preserve_lines_below_width()
	{
		$line1 = 'lorem dolor ipsum amet';
		$line2 = 'ipsum amet lorem dolor';
		$line3 = 'foo dolor baz bar';
		$wrapAt = 80;

		$this->output->write([
			'lorem dolor ipsum amet',
			'ipsum amet lorem dolor',
			'foo dolor baz bar',
		], Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln([
			'lorem dolor ipsum amet',
			'ipsum amet lorem dolor',
			'foo dolor baz bar',
		], Argument::any())->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->wrapAt($wrapAt);
		$sut->write([$line1, $line2, $line3]);
		$sut->writeln([$line1, $line2, $line3]);
	}

	/**
	 * @test
	 * it should break lines when writing line
	 */
	public function it_should_break_lines_when_writing_line()
	{
		$line = 'lorem dolor ipsum amet';
		$wrapAt = 12;

		$this->output->write("lorem dolor\nipsum amet", Argument::any(), Argument::any())->shouldBeCalled();
		$this->output->writeln("lorem dolor\nipsum amet", Argument::any())->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->wrapAt($wrapAt);
		$sut->write($line);
		$sut->writeln($line);
	}

	/**
	 * @test
	 * it should throw if trying to wrap with non int wrap
	 */
	public function it_should_throw_if_trying_to_wrap_with_non_int_wrap()
	{
		$sut = $this->make_instance();

		$this->expectException(\InvalidArgumentException::class);

		$sut->wrapAt('foo');
	}

	protected function _before()
	{
		$this->output = $this->prophesize(OutputInterface::class);
	}

	protected function _after()
	{
	}
}
