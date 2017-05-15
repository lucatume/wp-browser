<?php
$I = new WpfunctionalTester($scenario);
$I->wantTo('use the post factory to create posts');

$I->factory()->post->create(['post_title' => 'Post 1']);
$I->factory()->post->create(['post_title' => 'Post 2']);
$I->factory()->post->create(['post_title' => 'Post 3']);

$I->amOnPage('/');
$I->see('Post 1');
$I->see('Post 2');
$I->see('Post 3');

