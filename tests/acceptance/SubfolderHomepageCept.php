<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of a subfolder site');

$I->haveBlogInDatabase('test1', [], false);

$I->amOnPage('/' . getenv('WP_SUBFOLDER_1'));
$I->seeElement('body.blog');
$I->seeInTitle(getenv('WP_SUBFOLDER_1_TITLE') ?: 'Test Subdomain 1');

$I->haveBlogInDatabase('test2', [], false);

$I->amOnPage('/' . getenv('WP_SUBFOLDER_2'));
$I->seeElement('body.blog');
$I->seeInTitle(getenv('WP_SUBFOLDER_2_TITLE') ?: 'Test Subdomain 2');
