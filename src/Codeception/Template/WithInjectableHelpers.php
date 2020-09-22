<?php
/**
 * A template trait that allows injecting the template helpers.
 *
 * @package Codeception\Template
 */

namespace Codeception\Template;

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
    /**
     * The current question helper instance the template will use to ask questions to the user.
     *
     * @var QuestionHelper|null
     */
    protected $questionHelper;

    /**
     * Returns the current template question helper.
     *
     * @return QuestionHelper|null
     */
    public function getQuestionHelper()
    {
        return $this->questionHelper;
    }

    /**
     * Sets the question helper instance the template should use to interact with the user.
     *
     * @param QuestionHelper $questionHelper The question helper instance the template should use to interact with the
     *                                       user.
     *
     * @return void
     */
    public function setQuestionHelper($questionHelper)
    {
        $this->questionHelper = $questionHelper;
    }

    /**
     * Asks a question using a pre-set question helper or a new question helper instance if none was set.
     *
     * @param string     $question The question to ask.
     * @param null|mixed $answer   The answer to the question.
     *
     * @return mixed The answer as provided by the user.
     */
    protected function ask($question, $answer = null)
    {
        $question = "? $question";
        $dialog   = $this->questionHelper ?: new QuestionHelper();
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
