<?php

namespace tad\WPBrowser\Interactions;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class WPBootsrapButler extends BaseButler implements ButlerInterface
{

    /**
     * @param mixed $helper A question helper
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param bool $verbose
     *
     * @return array
     */
    public function askQuestions($helper, InputInterface $input, OutputInterface $output, $verbose = true)
    {
        $answers = [];

        if ($verbose) {
            $output->writeln('<info>Acceptance and functional tests will need to visit (as a user would do) a real</info>');
            $output->writeln('<info>website like "http://wordpress.dev". This should be a WordPress installation that\'s</info>');
            $output->writeln('<info>already running and available: you should be able to visit the address with your browser</info>');
            $output->writeln('<info>and not get any error.</info>');
            $output->writeln("\n");
        }

        if ($verbose) {
            $output->writeln('<info>The "host" is the address or alias where your database server listens for connections.</info>');
            $output->writeln('<info>The "localhost" address is usually a safe assumption but it could be "127.0.0.1,"</info>');
            $output->writeln('<info>an alias set in your hosts file like "mysql" or the IP address of a Vagrant or Docker instance.</info>');
            $output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file</info>');
            $output->writeln('<info>as "DB_HOST".</info>');
        }
        $question = new Question($this->question("MySQL database host? (localhost)"), 'localhost');
        $question->setValidator($this->validator->noSpaces('MySQL database host should not contain any space'));
        $question->setMaxAttempts(2);
        $answers['dbHost'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>During acceptance and functional tests a database will be emptied and refilled from</info>');
            $output->writeln('<info>a database dump before each test. That database should not contain any information you care about</info>');
            $output->writeln('<info>and be dedicated to these tests only. Furthermore the database should exist already.</info>');
            $output->writeln('<info>If it does not create it with `mysql -e "create database IF NOT EXISTS <dbName>;" -uroot`.</info>');
            $output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file</info>');
            $output->writeln('<info>as "DB_NAME".</info>');
        }
        $question = new Question($this->question("Test database name? (wpTests)"), 'wpTests');
        $question->setValidator($this->validator->noSpaces('MySQL database name should not contain any space'));
        $question->setMaxAttempts(2);
        $answers['dbName'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>The test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
            $output->writeln('<info>If you do not know what the database user is "root" is a safe assumption.</info>');
            $output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file</info>');
            $output->writeln('<info>as "DB_USER".</info>');
        }
        $question = new Question($this->question("Test database username? (root)"), 'root');
        $question->setValidator($this->validator->noSpaces('MySQL database username should not contain any space'));
        $question->setMaxAttempts(2);
        $answers['dbUser'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>The test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
            $output->writeln('<info>If you do not know what the database password is "root" or "" (empty) are safe assumptions.</info>');
            $output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file</info>');
            $output->writeln('<info>as "DB_PASSWORD".</info>');
        }
        $question = new Question($this->question("Test database password? (empty)"), '');
        $answers['dbPassword'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>Each WordPress installation "lives" in a set of tables all prefixed with the same string.</info>');
            $output->writeln('<info>The default prefix is "wp_" but you might have a different value.</info>');
            $output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file</info>');
            $output->writeln('<info>as "$table_prefix".</info>');
        }
        $question = new Question($this->question("Test database table prefix for acceptance and functional testing? (wp_)"), 'wp_');
        $question->setValidator($this->validator->noSpaces('MySQL database table prefix should not contain any spaces'));
        $question->setMaxAttempts(2);
        $answers['tablePrefix'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>The integration test suite will install WordPress from scratch each time it runs.</info>');
            $output->writeln('<info>This is how the PHPUnit-based testing suite from Core, and the WPLoader module, work.</info>');
            $output->writeln('<info>It should be safe to simply have the integration suite run on the same database as before</info>');
            $output->writeln('<info>specifying a different table prefix but it\'s risky and a completely different database is the</info>');
            $output->writeln('<info>safest choice.</info>');
        }
        $question = new ConfirmationQuestion($this->question("Are you using a different database to run integration tests? (yes)"), false);
        $answers['usingIntegrationDatabase'] = $helper->ask($input, $output, $question);

        if (!empty($answers['usingIntegrationDatabase'])) {
            if ($verbose) {
                $output->writeln("\n");
                $output->writeln('<info>During integration tests a database will be emptied and refilled from</info>');
                $output->writeln('<info>a database dump before each test. That database should not contain any information you care about</info>');
                $output->writeln('<info>and be dedicated to these tests only. Furthermore the database should exist already.</info>');
                $output->writeln('<info>If it does not create it with `mysql -e "create database IF NOT EXISTS <dbName>;" -uroot`.</info>');
            }
            $question = new Question($this->question("Integration tests database name? (integrationTests)"), 'integrationTests');
            $question->setValidator($this->validator->noSpaces('MySQL database name should not contain any space'));
            $question->setMaxAttempts(2);
            $answers['integrationDbName'] = $helper->ask($input, $output, $question);

            if ($verbose) {
                $output->writeln("\n");
                $output->writeln('<info>The integration test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
                $output->writeln('<info>If you do not know what the database user is "root" is a safe assumption.</info>');
            }
            $question = new Question($this->question("Integration tests database username? (root)"), 'root');
            $question->setValidator($this->validator->noSpaces('MySQL database username should not contain any space'));
            $question->setMaxAttempts(2);
            $answers['integrationDbUser'] = $helper->ask($input, $output, $question);

            if ($verbose) {
                $output->writeln("\n");
                $output->writeln('<info>The integration test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
                $output->writeln('<info>If you do not know what the database password is "root" or "" (empty) are safe assumptions.</info>');
            }
            $question = new Question($this->question("Integration tests database password? (empty)"), '');
            $answers['integrationDbPassword'] = $helper->ask($input, $output, $question);
        }

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>The integration tests will install WordPress in a set of tables all prefixed in the same way.</info>');
            $output->writeln('<info>The default prefix is "wp_" but you should specify a different value.</info>');
            $output->writeln('<info>If you decided not to use a different database for integration tests then this value</info>');
            $output->writeln('<info>should really not be the same as the table prefix used for acceptance and functional tests.</info>');
        }
        $question = new Question($this->question("Integration tests database table prefix? (int_)"), 'int_');
        $question->setValidator($this->validator->noSpaces('MySQL database table prefix for integration testing should not contain any spaces'));
        $question->setMaxAttempts(2);
        $answers['integrationTablePrefix'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>What\'s the URL of the WordPress installation used for the tests?</info>');
            $output->writeln('<info>WordPress stores the site URL in the database and uses it in many operations</info>');
            $output->writeln('<info>hence it\'s important to set this value to the same URL you would write in a browser</info>');
            $output->writeln('<info>to visit your local WordPress installation. It should be something like "http://wp.dev",</info>');
            $output->writeln('<info>"http://localhost:8080" or "http://192.168.10.254" depending on your local server setup.</info>');
        }
        $question = new Question($this->question("WordPress site url? (http://wp.dev)"), 'http://wp.dev');
        $question->setValidator($this->validator->isUrl("The site url should be in the 'http://example.com' format"));
        $question->setMaxAttempts(2);
        $answers['url'] = $helper->ask($input, $output, $question);

        $host = parse_url($answers['url'], PHP_URL_HOST);
        $port = parse_url($answers['url'], PHP_URL_PORT);
        $candidateDomain = $port ? $host . ':' . $port : $host;

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>As before this information is stored in the database and it\'s usually</info>');
            $output->writeln('<info>the URL of your local WordPress installation minus the "http://" part.</info>');
        }
        $question = new Question($this->question("WordPress site domain? ($candidateDomain)"), $candidateDomain);
        $answers['domain'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>Functional and integration tests will interact with the WordPress installation</info>');
            $output->writeln('<info>on a filesystem level; as such they should be provided with the absolute path</info>');
            $output->writeln('<info>to the WordPress installation root folder. The "root folder" is the folder containing</info>');
            $output->writeln('<info>the "wp-load.php" file.</info>');
        }
        $question = new Question($this->question("Absolute path to the WordPress root directory? (/var/www/wp)"), '/var/www/wp');
        $question->setValidator($this->validator->isWpDir());
        $question->setMaxAttempts(2);
        $answers['wpRootFolder'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>Functional and acceptance tests will interact with the WordPress installation</info>');
            $output->writeln('<info>acting as users; the administrator username is what you would enter in your</info>');
            $output->writeln('<info>local installation login form in the "Username or Email" field.</info>');
        }
        $question = new Question($this->question("WP administrator username? (admin)"), 'admin');
        $question->setValidator($this->validator->noSpaces('The Administrator username should not contain any spaces'));
        $question->setMaxAttempts(2);
        $answers['adminUsername'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>Functional and acceptance tests will interact with the WordPress installation</info>');
            $output->writeln('<info>acting as users; the administrator password is what you would enter in your</info>');
            $output->writeln('<info>local installation login form in the "Password" field.</info>');
        }
        $question = new Question($this->question("WP Administrator password? (admin)"), 'admin');
        $answers['adminPassword'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>When performing some operations WordPress could send emails to notice the</info>');
            $output->writeln('<info>administrator; this setting is not fundamental and could very well be a fantasy email</info>');
            $output->writeln('<info>address unless you are using something like Mailcatcher or other email catching</info>');
            $output->writeln('<info>mechanisms.</info>');
        }
        $candidateEmail = 'admin@' . $answers['domain'];
        $question = new Question($this->question("WP Administrator email? ($candidateEmail)"), $candidateEmail);
        $question->setValidator($this->validator->isEmail());
        $question->setMaxAttempts(2);
        $answers['adminEmail'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>The administration area is usually reachable at the "/wp-admin" path of a WordPress installation.</info>');
            $output->writeln('<info>This could not be the case for your local setup, set this value to the path (no full URL)</info>');
            $output->writeln('<info>you would append to the local WordPress installation URL to access the administration</info>');
            $output->writeln('<info>area.</info>');
        }
        $question = new Question($this->question("Relative path (from WordPress root) to administration area? (/wp-admin)"), '/wp-admin');
        $question->setValidator($this->validator->isRelativeWpAdminDir($answers['wpRootFolder']));
        $question->setMaxAttempts(2);
        $answers['adminPath'] = $helper->ask($input, $output, $question);

        if ($verbose) {
            $output->writeln("\n");
            $output->writeln('<info>When running integration tests no plugins will be activated by default.</info>');
            $output->writeln('<info>To specify which plugins should be active and in which order those should be activated</info>');
            $output->writeln('<info>use this setting. The stack is first-in-first-out so the first plugin you set will be activated</info>');
            $output->writeln('<info>first and so on. Plugins must be specified in their basename format: that\'s usually</info>');
            $output->writeln('<info>"folder/main-plugin-file.php".</info>');
        }
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
        return "<fg=yellow>{$questionText}</>\t";
    }
}