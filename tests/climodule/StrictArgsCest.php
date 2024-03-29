<?php

use ClimoduleTester as Tester;

class StrictArgsCest
{
    /**
     * It should use strict arguments by default
     *
     * @test
     */
    public function should_use_strict_arguments_by_default(Tester $I): void
    {
        $title = 'RSS Feed ' . md5(time());

        $I->assertEquals(0, $I->cli([
            'widget',
            'add',
            'rss',
            'sidebar-1',
            "--title=$title",
            '--url=https://wordpress.org/news/feed/',
        ]));

        $widgets = $I->grabFromDatabase($I->grabPrefixedTableNameFor('options'), 'option_value', ['option_name' => 'widget_rss']);
        $I->assertNotEmpty($widgets);
        $decoded = unserialize($widgets);
        $rssWidget = array_filter($decoded, static function ($el) use ($title) {
            return isset($el['title']) && $el['title'] === $title;
        });
        $rssWidget = reset($rssWidget);
        $I->assertEquals('https://wordpress.org/news/feed/', $rssWidget['url']);
    }
}
