<?php


use Codeception\Configuration;

class PluginActivationCest
{

    /**
     * @var array
     */
    protected $delete = [];

    public function _before(AcceptanceTester $I): void
    {
        $this->deleteFiles();
    }

    protected function deleteFiles(): void
    {
        foreach ($this->delete as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->deleteFiles();
    }

    public function _failed(AcceptanceTester $I): void
    {
        $this->deleteFiles();
    }

    /**
     * It should be able to activate plugins
     *
     * @test
     */
    public function be_able_to_activate_plugins(AcceptanceTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->activatePlugin('hello-dolly');
        $I->seePluginActivated('hello-dolly');

        $I->deactivatePlugin('hello-dolly');
        $I->seePluginDeactivated('hello-dolly');
    }

    /**
     * It should be able to activate plugins in a long list
     *
     * @test
     */
    public function be_able_to_activate_plugins_in_a_long_list(
        AcceptanceTester $I
    ): void {
        $this->scaffoldTestPlugins();

        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->activatePlugin('plugin-z');
        $I->seePluginActivated('plugin-z');

        $I->deactivatePlugin('plugin-z');
        $I->seePluginDeactivated('plugin-z');
    }

    protected function scaffoldTestPlugins(): void
    {
        $config = (Configuration::config());
        $wpFolder = $config['wpFolder'];
        $template
            = <<< HANDLEBARS
<?php
/*
Plugin Name: Plugin {{letter}}
Plugin URI: https://wordpress.org/plugins/{{letter}}/
Description: Plugin {{letter}} description
Version: 0.1.0
Author: Plugin {{letter}} author
Author URI: http://example.com/{{letter}}-plugin
Text Domain: {{letter}}_plugin
Domain Path: /languages
*/
HANDLEBARS;

        foreach (range('A', 'Z') as $letter) {
            $compiled = str_replace('{{letter}}', $letter, $template);
            $file     = $wpFolder . "/wp-content/plugins/{$letter}.php";
            file_put_contents($file, $compiled, LOCK_EX);
            $this->delete[] = $file;
        }
    }

    /**
     * It should be able to activate multiple plugins
     *
     * @test
     */
    public function be_able_to_activate_multiple_plugins(AcceptanceTester $I): void
    {
        $this->scaffoldTestPlugins();

        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $plugins = ['plugin-a', 'plugin-b', 'plugin-i', 'plugin-o', 'plugin-z'];

        $I->activatePlugin($plugins);
        foreach ($plugins as $plugin) {
            $I->seePluginActivated($plugin);
        }

        $I->deactivatePlugin($plugins);
        foreach ($plugins as $plugin) {
            $I->seePluginDeactivated($plugin);
        }
    }
}
