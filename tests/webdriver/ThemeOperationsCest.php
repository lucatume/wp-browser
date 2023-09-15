<?php

class ThemeOperationsCest
{
    public function _before(WebDriverTester $I)
    {
        $I->loginAsAdmin();
    }

    /**
     * It should support theme operations
     *
     * @test
     */
    public function should_support_theme_operations(AcceptanceTester $I)
    {
        $I->amOnThemesPage();
        $available = $I->grabAvailableThemes();
        codecept_debug($available);
        $active = $I->grabActiveTheme();
        codecept_debug($active);
        $activateTarget = array_values(array_diff($available, [$active]))[0];

        $I->activateTheme($activateTarget);

        $I->seeThemeActivated($activateTarget);
    }
}
