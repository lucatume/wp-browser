<?php

use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;

class BasicOperationsCest
{
    /**
     * @var mixed[]
     */
    private $tmpCleanup = [];

    public function _after(): void
    {
        foreach ($this->tmpCleanup as $dir) {
            FS::rrmdir($dir);
        }
        $this->tmpCleanup = [];
    }

    /**
     * @test
     * it should allow using the cli method in a test
     */
    public function it_should_allow_using_the_cli_method_in_a_test(ClimoduleTester $I): void
    {
        $I->cli('core version');
        $I->cli('cli info');
    }

    /**
     * @test
     * it should allow creating a post in the WordPress installation
     */
    public function it_should_allow_creating_a_post_in_the_word_press_installation(ClimoduleTester $I): void
    {
        $I->cli(['post', 'create', '--post_title=Some Post', '--post_type=post']);
        $I->cli('post list --post_type=post');

        $I->seePostInDatabase(['post_title' => 'Some Post', 'post_type' => 'post']);
    }

    /**
     * @test
     * it should allow trashing a post
     */
    public function it_should_allow_trashing_a_post(ClimoduleTester $I): void
    {
        $id = $I->havePostInDatabase(['post_title' => 'some post', 'post_type' => 'post']);

        $I->cli('post delete ' . $id);

        $I->seePostInDatabase(['ID' => $id, 'post_status' => 'trash']);
    }

    /**
     * @test
     * it should allow deleting a post from the database
     */
    public function it_should_allow_deleting_a_post_from_the_database(ClimoduleTester $I): void
    {
        $id = $I->havePostInDatabase(['post_title' => 'some post', 'post_type' => 'post']);

        $I->cli('post delete ' . $id . ' --force');

        $I->dontSeePostInDatabase(['ID' => $id]);
    }

    /**
     * It should allow changing the path
     *
     * @test
     */
    public function should_allow_changing_the_path(ClimoduleTester $I): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        $otherInstallation = Installation::scaffold(FS::tmpDir('wpcli_'), '6.1.1')
            ->configure($db)
            ->install(
                'http://wp.local',
                'admin',
                'secret',
                'admin@admin',
                'WPCLI Module Test Site'
            );
        $this->tmpCleanup[] = $otherInstallation->getWpRootDir();
        if (!mkdir($otherInstallation->getMuPluginsDir())) {
            throw new \RuntimeException('Could not create mu-plugins dir in first installation.');
        }
        $commandOneCode = <<<PHP
<?php
/**
 * Plugin Name: WPCLI Command One
 */

WP_CLI::add_command('ping-one', function(){
    WP_CLI::success('pong-one');
});
PHP;
        file_put_contents($otherInstallation->getMuPluginsDir() . '/command-one.php', $commandOneCode, LOCK_EX);

        $I->changeWpcliPath($otherInstallation->getWpRootDir());

        $I->cli(['ping-one']);

        $I->seeInShellOutput('pong-one');
    }
}
