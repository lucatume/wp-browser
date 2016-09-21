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

    protected function replaceWords($messages)
    {
        $messages = (array)$messages;
        $patterns = [
            '/(F|f)unctional/' => [$this, 'replaceFunctional'],
            '/(A|a)cceptance/' => [$this, 'replaceAcceptance'],
        ];
        $messages = preg_replace_callback_array($patterns, $messages);

        return count($messages) === 1 ? $messages[0] : $messages;
    }

    protected function replaceFunctional($matches)
    {
        return $matches[1] === 'F' ? 'Service' : 'service';
    }

    protected function replaceAcceptance()
    {
        return 'UI';
    }
}