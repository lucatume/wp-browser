<?php

namespace tad\WPBrowser\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;

class PyramidWordsOutput extends OutputDecorator implements OutputInterface
{
	/**
	 * Writes a message to the output.
	 *
	 * @param string|array $messages The message as an array of lines or a single string
	 * @param bool $newline Whether to add a newline
	 * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
	 */
	public function write($messages, $newline = false, $options = 0)
	{
		$this->output->write($this->replaceWords($messages), $newline, $options);
	}

	protected function replaceWords($messages)
	{
		$messages = (array)$messages;
		$patterns = [
			'/(F|f)unctional/',
			'/(A|a)cceptance/'
		];

		$messages = preg_replace_callback($patterns, [$this, 'replaceWord'], $messages);

		return count($messages) === 1 ? $messages[0] : $messages;
	}

	/**
	 * Writes a message to the output and adds a newline at the end.
	 *
	 * @param string|array $messages The message as an array of lines of a single string
	 * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
	 */
	public function writeln($messages, $options = 0)
	{
		$this->output->writeln($this->replaceWords($messages), $options);
	}

	protected function replaceWord($matches)
	{
		switch ($matches[1]) {
			case 'F':
				return 'Service';
			case 'f':
				return 'service';
			default:
				return 'UI';
		}
	}

}