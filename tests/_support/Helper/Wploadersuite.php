<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Wploadersuite extends \Codeception\Module
{
    public function create_a_post()
    {
        yield wp_insert_post([
            'post_title' => 'test',
            'post_status' => 'publish',
        ]);

        yield $this->getModule('WPLoader')->factory()->post->create();
    }
}
