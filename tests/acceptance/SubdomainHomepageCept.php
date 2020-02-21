<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('access the homepage of a subdomain site');

$I->useBlog(2);
$I->havePostInDatabase(['post_title' => 'Post test1']);

$I->amOnSubdomain('test1');
$I->amOnPage('/');

$I->seeElement('body.home');
$I->see('Post test1');

$I->useBlog(3);
$I->havePostInDatabase(['post_title' => 'Post test2']);

$I->amOnSubdomain('test2');
$I->amOnPage('/');

$I->seeElement('body.home');
$I->see('Post test2');
