<?php

namespace tad\WPBrowser\Console\Output;


use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WrappingOutput
 *
 * Decorates the output to wrap lines at a specified width.
 *
 * @package tad\WPBrowser\Console\Output
 */
class WrappingOutput extends OutputDecorator implements OutputInterface
{
	/**
	 * @var int The number of chars to wrap the line at.
	 */
	protected $wrapAt = 80;


	/**
	 * Writes a message to the output.
	 *
	 * @param string|array $messages The message as an array of lines or a single string
	 * @param bool $newline Whether to add a newline
	 * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
	 */
	public function write($messages, $newline = false, $options = 0)
	{
		$this->output->write($this->wrap($messages), $newline, $options);
	}

	private function wrap($messages)
	{
		$messages = (array)$messages;

		$wrappedMessages = [];
		foreach ($messages as $message) {
			$wrappedMessages[] = wordwrap($message, $this->wrapAt, "\n", false);
		}

		return count($wrappedMessages) === 1 ? $wrappedMessages[0] : $wrappedMessages;
	}

	/**
	 * Writes a message to the output and adds a newline at the end.
	 *
	 * @param string|array $messages The message as an array of lines of a single string
	 * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
	 */
	public function writeln($messages, $options = 0)
	{
		$this->output->writeln($this->wrap($messages), $options);
	}

	/**
	 * Sets the width of the line in chars.
	 *
	 * @param int $wrapAt
	 */
	public function wrapAt($wrapAt)
	{
		if (!is_int($wrapAt)) {
			throw new \InvalidArgumentException('Wrap value must be an int');
		}
		$this->wrapAt = $wrapAt;
	}
}