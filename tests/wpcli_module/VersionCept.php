<?php
$I = new Wpcli_moduleTester($scenario);
$I->wantTo('get the WordPress website version using WPCLI module');

$I->assertNotEmpty(
    $I->cli([ 'core', 'version' ])
);
