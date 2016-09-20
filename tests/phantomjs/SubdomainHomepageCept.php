<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of a subdomain site');
$I->amOnSubdomain(getenv('wpSubdomain1') ?: 'test1');
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle(getenv('wpSubdomain1Title') ?: 'Test Subdomain 1');
$I->amOnSubdomain(getenv('wpSubdomain2') ?: 'test2');
$I->amOnPage('/');
$I->seeElement('body.home');
$I->seeInTitle(getenv('wpSubdomain2Title') ?: 'Test Subdomain 2');
