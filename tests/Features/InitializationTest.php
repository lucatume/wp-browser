<?php


namespace Features;

use lucatume\WPBrowser\Traits\CommandExecution;
use lucatume\WPBrowser\Traits\WordPressInstallations;
use lucatume\WPBrowser\Utils\Codeception;


class InitializationTest extends \Codeception\Test\Unit
{
    use WordPressInstallations;
    use CommandExecution;

    private function getWordPressInstallationDbCredentials(): array
    {
        return [
            'DB_NAME' => $_ENV['WORDPRESS_DB_NAME'],
            'DB_USER' => $_ENV['WORDPRESS_DB_USER'],
            'DB_PASSWORD' => $_ENV['WORDPRESS_DB_PASSWORD'],
            'DB_HOST' => $_ENV['WORDPRESS_DB_HOST']
        ];
    }

    /**
     * It should initialize the end2end and integration suite by default for sites
     *
     * @test
     */
    public function should_initialize_the_end2end_and_integration_suite_by_default_for_sites()
    {
        $installation = $this->makeWordPressInstallation();

        $process = $this->runCommand([Codeception::bin(), 'init', 'wpbrowser'], $installation->getRootDir());
    }
}
