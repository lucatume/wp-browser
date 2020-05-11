<?php

namespace Codeception\Module;

class WPBrowserMethodsTest extends \Codeception\Test\Unit
{
    use WPBrowserMethods;
   
    protected $page;

    public function adminAjaxPageDatqProvider()
    {
        return [
            'one element array' => [['action' => 'foo_action'], '/wp-admin/admin-ajax.php?action=foo_action'],
            'multi element array' => [
                ['action' => 'foo_action', 'data' => 'bar_data', 'nonce' => 'baz_nonce'],
                '/wp-admin/admin-ajax.php?action=foo_action&data=bar_data&nonce=baz_nonce'
            ],
            'string' => [
                'action=foo_action&data=bar_data&nonce=baz_nonce',
                '/wp-admin/admin-ajax.php?action=foo_action&data=bar_data&nonce=baz_nonce'
            ],
            'string w/ question mark' => [
                '?action=foo_action&data=bar_data&nonce=baz_nonce',
                '/wp-admin/admin-ajax.php?action=foo_action&data=bar_data&nonce=baz_nonce'
            ]
        ];
    }

    public function amOnPage($page)
    {
        $this->page = $page;
    }

    /**
     * @test
     *
     * It should point to ajax file when requesting ajax page with query vars
     *
     * @dataProvider adminAjaxPageDatqProvider
     */
    public function it_should_point_to_ajax_file_when_requesting_ajax_page_with_query_vars($input, $expected)
    {
        $this->amOnAdminAjaxPage($input);

        $this->assertEquals($expected, $this->page);
    }
}
