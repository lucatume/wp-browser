<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('activate a plugin');
$I->loginAsAdmin();
$I->amOnPluginsPage();

$I->activatePlugin('hello-dolly');
$I->seePluginActivated('hello-dolly');

$I->deactivatePlugin('hello-dolly');
$I->seePluginDeactivated('hello-dolly');
