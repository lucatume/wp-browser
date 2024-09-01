<?php


class PluginActivationCest
{

    /**
     * @var array
     */
    protected $delete = [];

    public function _before(WebDriverTester $I): void
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

    public function _after(WebDriverTester $I): void
    {
        $this->deleteFiles();
    }

    public function _failed(WebDriverTester $I): void
    {
        $this->deleteFiles();
    }

    /**
     * It should be able to activate plugins
     *
     * @test
     */
    public function be_able_to_activate_plugins(WebDriverTester $I): void
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
    public function be_able_to_activate_plugins_in_a_long_list(WebDriverTester $I): void
    {
        $this->scaffoldTestPlugins($I);

        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->activatePlugin('plugin-z');
        $I->seePluginActivated('plugin-z');

        $I->deactivatePlugin('plugin-z');
        $I->seePluginDeactivated('plugin-z');
    }

    protected function scaffoldTestPlugins(WebDriverTester $I): void
    {
        foreach (range('A', 'Z') as $letter) {
            $compiled = "function {$letter}_main(){}";
            $I->havePlugin("plugin-{$letter}/plugin-{$letter}.php", $compiled);
        }
    }

    /**
     * It should be able to activate multiple plugins
     *
     * @test
     */
    public function be_able_to_activate_multiple_plugins(WebDriverTester $I): void
    {
        $this->scaffoldTestPlugins($I);

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
