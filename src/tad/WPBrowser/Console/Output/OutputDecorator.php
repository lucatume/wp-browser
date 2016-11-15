<?php

namespace tad\WPBrowser\Console\Output;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class OutputDecorator
{
	/**
	 * @var OutputInterface The decorated output.
	 */
	protected $output;

	/**
	 * OutputDecorator constructor.
	 *
	 * @param OutputInterface $output
	 */
	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
	}

	/**
	 * Sets the verbosity of the output.
	 *
	 * @param int $level The level of verbosity (one of the VERBOSITY constants)
	 */
	public function setVerbosity($level)
	{
		$this->output->setVerbosity($level);
	}

	/**
	 * Gets the current verbosity of the output.
	 *
	 * @return int The current level of verbosity (one of the VERBOSITY constants)
	 */
	public function getVerbosity()
	{
		return $this->output->getVerbosity();
	}

	/**
	 * Sets the decorated flag.
	 *
	 * @param bool $decorated Whether to decorate the messages
	 */
	public function setDecorated($decorated)
	{
		$this->output->setDecorated($decorated);
	}

	/**
	 * Gets the decorated flag.
	 *
	 * @return bool true if the output will decorate messages, false otherwise
	 */
	public function isDecorated()
	{
		return $this->output->isDecorated();
	}

	/**
	 * Sets output formatter.
	 *
	 * @param OutputFormatterInterface $formatter
	 */
	public function setFormatter(OutputFormatterInterface $formatter)
	{
		$this->output->setFormatter($formatter);
	}

	/**
	 * Returns current output formatter instance.
	 *
	 * @return OutputFormatterInterface
	 */
	public function getFormatter()
	{
		return $this->output->getFormatter();
	}
}