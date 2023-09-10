<?php

$I = new WebDriverTester($scenario);
$I->wantTo('access the homepage of a subdomain site');
$I->amOnUrl($I->grabBlogUrl(2));
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle('Test Subdomain 1');
$I->amOnUrl($I->grabBlogUrl(3));
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle('Test Subdomain 2');
