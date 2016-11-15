<?php

namespace tad\WPBrowser\Interactions;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
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

		if (empty($input->getOption('type'))) {
			$default = $this->getOptionDefault($input, 'type', 'plugin');
			$question = new ChoiceQuestion($this->question("Are you testing a plugin or a theme? ({$default})"),
				['plugin', 'theme'], $default);
			$answers['type'] = $helper->ask($input, $output, $question);

			if ($answers['type'] === 'theme') {
				$default = $this->getOptionDefault($input, 'theme', 'my-theme');
				$question = new Question($this->question("What's the theme slug?"
					. "If testing a child theme enter the parent theme slug and the child theme slug separated by a comma like 'parent,child'. "
					. "({$default})"), $default);
				$question->setValidator($this->validator->isTheme());
				$question->setMaxAttempts(5);
				$answers['theme'] = $helper->ask($input, $output, $question);
			}
		}

		if ($verbose) {
			$output->writeln('<info>Acceptance and functional tests will need to visit a real website like "http://wordpress.dev" as a user would do. This should be a WordPress installation that\'s already running and available: you should be able to visit the address with your browser and not get any error.</info>');
			$output->writeln("\n");
			$output->writeln('<info>The "host" is the address or alias where your database server listens for connections; the "localhost" address is usually a safe assumption but it could be "127.0.0.1", an alias set in your hosts file like "mysql" or the IP address of a Vagrant or Docker instance.</info>');
			$output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file as "DB_HOST"</info>');
		}

		$default = $this->getOptionDefault($input, 'dbHost', 'localhost');
		$question = new Question($this->question("MySQL database host? ({$default})"), $default);
		$question->setValidator($this->validator->noSpaces('MySQL database host should not contain any space'));
		$question->setMaxAttempts(5);
		$answers['dbHost'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>During acceptance and functional tests a database will be emptied and refilled from a database dump before each test. That database should not contain any information you care about and be dedicated to these tests only. Furthermore the database should exist already.</info>');
			$output->writeln('<info>If it doesn\'t exist run the command <fg=blue>`mysql -e "create database IF NOT EXISTS <dbName>;" -uroot`.</> to create it.</info>');

			$output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file as "DB_NAME".</info>');
		}
		$default = $this->getOptionDefault($input, 'dbName', 'wpTests');
		$question = new Question($this->question("Test database name? ({$default})"), $default);
		$question->setValidator($this->validator->noSpaces('MySQL database name should not contain any space'));
		$question->setMaxAttempts(5);
		$answers['dbName'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>The test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
			$output->writeln('<info>If you do not know what the database user is "root" is a safe assumption.</info>');
			$output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file as "DB_USER".</info>');
		}
		$default = $this->getOptionDefault($input, 'dbUser', 'root');
		$question = new Question($this->question("Test database username? ({$default})"), $default);
		$question->setValidator($this->validator->noSpaces('MySQL database username should not contain any space'));
		$question->setMaxAttempts(5);
		$answers['dbUser'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>The test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
			$output->writeln('<info>If you do not know what the database password is "root" or "" (empty) are safe assumptions.</info>');
			$output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file as "DB_PASSWORD".</info>');
		}
		$default = $this->getOptionDefault($input, 'dbPassword', '');
		$question = new Question($this->question("Test database password? ({$default})"), $default);
		$answers['dbPassword'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>Each WordPress installation "lives" in a set of tables all prefixed by the same string.</info>');
			$output->writeln('<info>The default prefix is "wp_" but you might have a different value.</info>');
			$output->writeln('<info>This is probably the same value you have set in the WordPress installation "wp-config.php" file as "$table_prefix".</info>');
		}
		$default = $this->getOptionDefault($input, 'tablePrefix', 'wp_');
		$question = new Question($this->question("Test database table prefix for acceptance and functional testing? ({$default})"),
			$default);
		$question->setValidator($this->validator->noSpaces('MySQL database table prefix should not contain any spaces'));
		$question->setMaxAttempts(5);
		$answers['tablePrefix'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>The integration test suite will install WordPress from scratch each time it runs. This is how the PHPUnit-based testing suite from Core, and the WPLoader module, work.</info>');
			$output->writeln('<info>It should be safe to simply have the integration suite run on the same database as before specifying a different table prefix but it\'s risky and a completely different database is the safest choice.</info>');
		}
		$question = new ConfirmationQuestion($this->question("Are you using a different database to run integration tests? (yes)"),
			true);
		$answers['usingIntegrationDatabase'] = $helper->ask($input, $output, $question);

		if (!empty($answers['usingIntegrationDatabase'])) {
			if ($verbose) {
				$output->writeln("\n");
				$output->writeln('<info>During integration tests a database will be emptied and refilled from a database dump before each test. That database should not contain any information you care about and be dedicated to these tests only. Furthermore the database should exist already.</info>');
				$output->writeln('<info>If it doesn\'t exist run the command <fg=blue>`mysql -e "create database IF NOT EXISTS <dbName>;" -uroot`.</> to create it.</info>');
			}
			$question = new Question($this->question("Integration tests database name? (integrationTests)"),
				'integrationTests');
			$question->setValidator($this->validator->noSpaces('MySQL database name should not contain any space'));
			$question->setMaxAttempts(5);
			$answers['integrationDbName'] = $helper->ask($input, $output, $question);

			if ($verbose) {
				$output->writeln("\n");
				$output->writeln('<info>The integration test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
				$output->writeln('<info>If you do not know what the database user is "root" is a safe assumption.</info>');
			}
			$default = $this->getOptionDefault($input, 'dbUser', 'root');
			$question = new Question($this->question("Integration tests database username? ({$default})"), $default);
			$question->setValidator($this->validator->noSpaces('MySQL database username should not contain any space'));
			$question->setMaxAttempts(5);
			$answers['integrationDbUser'] = $helper->ask($input, $output, $question);

			if ($verbose) {
				$output->writeln("\n");
				$output->writeln('<info>The integration test suite will need to access the datbase as an authenticated user, ideally with root access.</info>');
				$output->writeln('<info>If you do not know what the database password is "root" or "" (empty) are safe assumptions.</info>');
			}
			$default = $this->getOptionDefault($input, 'dbPassword', '');
			$question = new Question($this->question("Integration tests database password? ({$default})"), $default);
			$answers['integrationDbPassword'] = $helper->ask($input, $output, $question);
		}

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>The integration tests will install WordPress in a set of tables all prefixed by the same string.</info>');
			$output->writeln('<info>The default prefix is "wp_" but you should specify a different value.</info>');
			$output->writeln('<info>If you decided not to use a different database for integration tests then this value should not be the same as the table prefix used for acceptance and functional tests.</info>');
		}
		$default = $this->getOptionDefault($input, 'tablePrefix', 'wp_');
		$question = new Question($this->question("Integration tests database table prefix? (int_{$default})"),
			'int_' . $default);
		$question->setValidator($this->validator->noSpaces('MySQL database table prefix for integration testing should not contain any spaces'));
		$question->setMaxAttempts(5);
		$answers['integrationTablePrefix'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>What\'s the URL of the WordPress installation used for the tests?</info>');
			$output->writeln('<info>WordPress stores the site URL in the database and uses it in many operations hence it\'s important to set this value to the same URL you would write in a browser to visit your local WordPress installation. It should be something like "http://wp.dev", "http://localhost:8080" or "http://192.168.10.254" depending on your local server setup.</info>');
		}
		$default = $this->getOptionDefault($input, 'url', 'http://wp.dev');
		$question = new Question($this->question("WordPress site url? ({$default})"), $default);
		$question->setValidator($this->validator->isUrl("The site url should be in the 'http://example.com' format"));
		$question->setMaxAttempts(5);
		$answers['url'] = $helper->ask($input, $output, $question);

		$host = parse_url($answers['url'], PHP_URL_HOST);
		$port = parse_url($answers['url'], PHP_URL_PORT);
		$candidateDomain = $port ? $host . ':' . $port : $host;

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>As before this information is stored in the database and it\'s usually the URL of your local WordPress installation minus the "http://" part.</info>');
		}
		$question = new Question($this->question("WordPress site domain? ($candidateDomain)"), $candidateDomain);
		$answers['domain'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>Functional and integration tests will interact with the WordPress installation on a filesystem level; as such they should be provided with the absolute path to the WordPress installation root folder. The "root folder" is the folder containing the "wp-load.php" file.</info>');
		}
		$default = $this->getOptionDefault($input, 'wpRootFolder', '/var/www/wp');
		$question = new Question($this->question("Absolute path to the WordPress root directory? ({$default})"),
			$default);
		$question->setValidator($this->validator->isWpDir());
		$question->setMaxAttempts(5);
		$answers['wpRootFolder'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>Functional and acceptance tests will interact with the WordPress installation acting as users; the administrator username is what you would enter in your local installation login form in the "Username or Email" field.</info>');
		}
		$default = $this->getOptionDefault($input, 'adminUsername', 'admin');
		$question = new Question($this->question("WP administrator username? ({$default})"), $default);
		$question->setValidator($this->validator->noSpaces('The Administrator username should not contain any spaces'));
		$question->setMaxAttempts(5);
		$answers['adminUsername'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>Functional and acceptance tests will interact with the WordPress installation acting as users; the administrator password is what you would enter in your local installation login form in the "Password" field.</info>');
		}
		$default = $this->getOptionDefault($input, 'adminPassword', 'admin');
		$question = new Question($this->question("WP Administrator password? ({$default})"), $default);
		$answers['adminPassword'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>When performing some operations WordPress could send emails to notice the administrator; this setting is not fundamental and could be a fantasy email address unless you are using something like MailCatcher (https://mailcatcher.me/) or other email capture mechanisms.</info>');
		}
		$candidateEmail = 'admin@' . $answers['domain'];
		$question = new Question($this->question("WP Administrator email? ($candidateEmail)"), $candidateEmail);
		$question->setValidator($this->validator->isEmail());
		$question->setMaxAttempts(5);
		$answers['adminEmail'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>The administration area is usually reachable at the "/wp-admin" path of a WordPress installation.</info>');
			$output->writeln('<info>This could not be the case for your local setup, set this value to the path you would append to the local WordPress installation URL to access the administration area.</info>');
		}
		$default = $this->getOptionDefault($input, 'adminPath', '/wp-admin');
		$question = new Question($this->question("Relative path (from WordPress root) to administration area? ({$default})"),
			$default);
		$question->setValidator($this->validator->isRelativeWpAdminDir($answers['wpRootFolder']));
		$question->setMaxAttempts(5);
		$answers['adminPath'] = $helper->ask($input, $output, $question);

		if ($verbose) {
			$output->writeln("\n");
			$output->writeln('<info>When running integration tests no plugin will be activated by default.</info>');
			$output->writeln('<info>To specify which plugins should be active and in which order use this setting. The stack is first-in-first-out so the first plugin you set will be activated first and so on. Plugins must be specified in their basename format: that\'s usually "folder/main-plugin-file.php".</info>');
		}
		$plugins = [];
		$default = $this->getOptionDefault($input, 'plugins', '');
		if (!empty($default)) {
			$plugins = explode(',', $default);
		}
		do {
			$active = implode(', ', $plugins);
			$questionText = empty($plugins) ?
				"Activate a plugin? (order matters, leave blank to move on)"
				: "Plugins: {$active}\nActivate another plugin? (order matters, leave blank to move on)";
			$question = new Question($this->question($questionText), '');
			$question->setValidator($this->validator->isPlugin());
			$question->setMaxAttempts(5);

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

	/**
	 * @param InputInterface $input
	 * @param $option
	 * @param $default
	 */
	protected function getOptionDefault(InputInterface $input, $option, $default)
	{
		$value = $input->getOption($option);

		return empty($value) ? $default : $value;
	}

	protected function question($questionText)
	{
		return "<fg=yellow>{$questionText}</>\t";
	}
}
