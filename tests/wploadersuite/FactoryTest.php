<?php

use tad\WPBrowser\Module\WPLoader\FactoryStore;

class FactoryTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \WploaderTester
     */
    protected $tester;

    /**
     * It should expose the factory property on the tester property
     *
     * @test
     */
    public function should_expose_the_factory_property_on_the_tester_property()
    {
        $this->assertInstanceOf(FactoryStore::class, $this->tester->factory());
    }

    public function factoryTypesAndClasses()
    {
        return [
            'post'       => [ 'post', WP_UnitTest_Factory_For_Post::class ],
            'bookmark'   => [ 'bookmark', WP_UnitTest_Factory_For_Bookmark::class ],
            'attachment' => [ 'attachment', WP_UnitTest_Factory_For_Attachment::class ],
            'user'       => [ 'user', WP_UnitTest_Factory_For_User::class ],
            'comment'    => [ 'comment', WP_UnitTest_Factory_For_Comment::class ],
            'blog'       => [ 'blog', WP_UnitTest_Factory_For_Blog::class ],
            'network'    => [ 'network', WP_UnitTest_Factory_For_Network::class ],
            'term'       => [ 'term', WP_UnitTest_Factory_For_Term::class ]
        ];
    }
    /**
     * It should expose each factory type on the factory property
     *
     * @test
     * @dataProvider factoryTypesAndClasses
     */
    public function should_expose_each_factory_type_on_the_factory_property($factoryType, $factoryClass)
    {
        $this->assertInstanceOf($factoryClass, $this->tester->factory()->{$factoryType});
    }
}
