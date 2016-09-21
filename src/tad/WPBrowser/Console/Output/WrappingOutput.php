<?php

namespace tad\WPBrowser\Console\Output;


use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WrappingOutput
 *
 * Decorates the output to wrap lines at a specified width.
 *
 * @package tad\WPBrowser\Console\Output
 */
class WrappingOutput implements OutputInterface
{
    /**
     * @var int The number of chars to wrap the line at.
     */
    protected $wrapAt = 80;

    /**
     * @var OutputInterface The decorated output.
     */
    protected $output;

    /**
     * Writes a message to the output.
     *
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool $newline Whether to add a newline
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function write($messages, $newline = false, $options = 0)
    {
        $this->output->write($this->wrap($messages));
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln($messages, $options = 0)
    {
        $this->output->writeln($this->wrap($messages));
    }

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

    private function wrap($messages)
    {
        $messages = (array)$messages;

        $wrappedMessages = [];
        foreach ($messages as $message) {
            $wrappedMessages[] = wordwrap($message, $this->wrapAt, "\n", false);
        }

        return count($wrappedMessages) === 1 ? $wrappedMessages[0] : $wrappedMessages;
    }
}