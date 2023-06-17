<?php
/**
 * A template trait that allows injecting the template helpers.
 *
 * @package Codeception\Template
 */

namespace lucatume\WPBrowser\Template;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Trait WithInjectableHelpers
 *
 * @package Codeception\Template
 */
trait WithInjectableHelpers
{
    protected ?QuestionHelper $questionHelper = null;

    public function setQuestionHelper(QuestionHelper $questionHelper): void
    {
        $this->questionHelper = $questionHelper;
    }

    protected function ask(string $question, $answer = null): mixed
    {
        $question = "? $question";
        $dialog = $this->questionHelper ?: new QuestionHelper();
        if (is_array($answer)) {
            $question .= " <info>(" . $answer[0] . ")</info> ";

            return $dialog->ask($this->input, $this->output, new ChoiceQuestion($question, $answer, 0));
        }
        if (is_bool($answer)) {
            $question .= " (y/n) ";

            return $dialog->ask($this->input, $this->output, new ConfirmationQuestion($question, $answer));
        }
        if ($answer) {
            $question .= " <info>($answer)</info>";
        }

        return $dialog->ask($this->input, $this->output, new Question("$question ", $answer));
    }
}
