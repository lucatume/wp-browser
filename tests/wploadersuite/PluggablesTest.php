<?php

namespace wploadersuite;

use lucatume\WPBrowser\TestCase\WPTestCase;

class PluggablesTest extends WPTestCase
{
    public function test_pluggable_function_is_loaded_from_plugin():void{
        $this->assertEquals(
        "<img class='avatar' height=23 width=89 src='https://example.com/avatar.jpg'>",
            get_avatar('luca@example.com')
        );
    }
}
