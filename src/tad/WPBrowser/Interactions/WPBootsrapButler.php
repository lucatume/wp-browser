<?php

namespace tad\WPBrowser\Interactions;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class WPBootsrapButler extends BaseButler implements ButlerInterface
{

    /**
     * @param mixed $helper A question helper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    public function askQuestions($helper, InputInterface $input, OutputInterface $output)
    {
        $answers = [];

        $question = new Question($this->question("MySQL database host? (localhost)"), 'localhost');
        $question->setValidator($this->validator->noSpaces('MySQL database host should not contain any space'));
        $question->setMaxAttempts(2);
        $answers['dbHost'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("MySQL database name? (wpTests)"), 'wpTests');
        $question->setValidator($this->validator->noSpaces('MySQL database name should not contain any space'));
        $question->setMaxAttempts(2);
        $answers['dbName'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("MySQL database username? (root)"), 'root');
        $question->setValidator($this->validator->noSpaces('MySQL database username should not contain any space'));
        $question->setMaxAttempts(2);
        $answers['dbUser'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("MySQL database password? (empty)"), '');
        $answers['dbPassword'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("MySQL database table prefix for acceptance and functional testing? (wp_)"), 'wp_');
        $question->setValidator($this->validator->noSpaces('MySQL database table prefix should not contain any spaces'));
        $question->setMaxAttempts(2);
        $answers['tablePrefix'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("MySQL database table prefix for integration testing? (int_)"), 'int_');
        $question->setValidator($this->validator->noSpaces('MySQL database table prefix for integration testing should not contain any spaces'));
        $question->setMaxAttempts(2);
        $answers['integrationTablePrefix'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("WordPress site url? (http://wp.dev)"), 'http://wp.dev');
        $question->setValidator($this->validator->isUrl("The site url should be in the 'http://example.com' format"));
        $question->setMaxAttempts(2);
        $answers['url'] = $helper->ask($input, $output, $question);

        $host = parse_url($answers['url'], PHP_URL_HOST);
        $port = parse_url($answers['url'], PHP_URL_PORT);
        $candidateDomain = $port ? $host . ':' . $port : $host;

        $question = new Question($this->question("WordPress site domain? ($candidateDomain)"), $candidateDomain);
        $answers['domain'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("Absolute path to the WordPress root directory? (/var/www/wp)"), '/var/www/wp');
        $question->setValidator($this->validator->isWpDir());
        $question->setMaxAttempts(2);
        $answers['wpRootFolder'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("WP administrator username? (admin)"), 'admin');
        $question->setValidator($this->validator->noSpaces('The Administrator username should not contain any spaces'));
        $question->setMaxAttempts(2);
        $answers['adminUsername'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("WP Administrator password? (admin)"), 'admin');
        $answers['adminPassword'] = $helper->ask($input, $output, $question);

        $candidateEmail = 'admin@' . $answers['domain'];
        $question = new Question($this->question("WP Administrator email? ($candidateEmail)"), $candidateEmail);
        $question->setValidator($this->validator->isEmail());
        $question->setMaxAttempts(2);
        $answers['adminEmail'] = $helper->ask($input, $output, $question);

        $question = new Question($this->question("Relative path (from WordPress root) to administration area? (/wp-admin)"), '/wp-admin');
        $question->setValidator($this->validator->isRelativeWpAdminDir($answers['wpRootFolder']));
        $question->setMaxAttempts(2);
        $answers['adminPath'] = $helper->ask($input, $output, $question);

        $plugins = [];
        do {
            $questionText = empty($plugins) ?
                "Activate a plugin? (order matters, leave blank to move on)"
                : "Activate another plugin? (order matters, leave blank to move on)";
            $question = new Question($this->question($questionText), '');
            $question->setValidator($this->validator->isPlugin());
            $question->setMaxAttempts(2);

            $plugin = $helper->ask($input, $output, $question);

            if (!empty($plugin)) {
                $plugins[] = $plugin;
            }
        } while (!empty($plugin));

        $yamlPlugins = Yaml::dump($plugins, 0);

        $answers['plugins'] = $yamlPlugins;
        $answers['activatePlugins'] = $yamlPlugins;

        return $answers;
    }

    protected function question($questionText)
    {
        return "<question>{$questionText}</question>\t";
    }
}