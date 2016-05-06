<?php

namespace Codeception\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class SetupLocal extends Command
{
    /**
     * @var array
     */
    protected $vars;

    protected function configure()
    {
        $this->vars = [];
        $this->setName('setup:local')
            ->setDescription('Sets up the local testing environment according to rules stored in a configuration file.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'If set, the specified configuration file will be used.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption('config');
        $configFile = empty($configFile) ? getcwd() . DIRECTORY_SEPARATOR . 'local.yml' : $configFile;

        if (!file_exists($configFile)) {
            $output->writeln('<error>Configuration file [' . $configFile . '] does not exist.</error>');
            return false;
        }

        $config = Yaml::parse(file_get_contents($configFile));

        $helper = $this->getHelper('question');

        foreach ($config as $sectionTitle => $sectionConfig) {
            $output->writeln('Configuring "' . $sectionTitle . '"');
            foreach ($sectionConfig as $key => $value) {
                switch ($key) {
                    case 'var':
                        $default = isset($value['default']) ? $value['default'] : '';
                        $defaultMessage = $default ? ' (' . $default . ')' : '';
                        $questionText = $value['question'] . $defaultMessage;
                        $question = new Question($questionText, $default);
                        $answer = $helper->ask($input, $output, $question);
                        $this->vars[$value['name']] = trim($answer);
                        break;
                    case 'message':
                        $message = $this->replaceVarsInString($value);
                        $output->writeln('<info>' . $message . '</info>');
                        break;
                    case 'command';
                        $commandArgs = explode(' ', $value);
                        $replacedArrayArgs = array_map([$this, 'replaceVarsInString'], $commandArgs);
                        $subCommand = $this->getApplication()->find(reset($commandArgs));
                        $subCommand->run(new StringInput(implode(' ', $replacedArrayArgs)), $output);
                        break;
                    case 'exec':
                        exec($this->replaceVarsInString($value));
                        break;
                }
            }
        }

        return true;
    }

    protected function replaceVarsInString($string)
    {
        array_walk($this->vars, function ($value, $key) use (&$string) {
            $string = str_replace('$' . $key, $value, $string);
        });
        return $string;
    }
}
