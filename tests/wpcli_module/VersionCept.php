<?php
$I = new Wpcli_moduleTester($scenario);
$I->wantTo('get the WordPress website version using WPCLI module');

$I->assertEquals(0, $I->cli([ 'core', 'version' ]));
