<?php

namespace unit;

use Roots\Bedrock\Autoloader;

class AutoloaderTest extends \WP_Mock\Tools\TestCase
{
    public function setUp(): void
    {
        \WP_Mock::setUp();
    }

    public function tearDown(): void
    {
        \WP_Mock::tearDown();
    }

    /*
    public function testShowInAdmin()
    {
    }
    */

    public function testLoadPlugins()
    {
        \WP_Mock::userFunction(
            'is_admin',
            ['return' => true]
        );
        \WP_Mock::userFunction(
            'get_plugins',
            [
                'args' => '/../mu-plugins',
                'return' => [
                    '10-fake/10-fake.php' => [
                        'Name' => 'UwU',
                        'Version' => '1.0.0',
                    ],
                    '20-fake/20-fake.php' => [
                        'Name' => '0w0',
                        'Version' => '1.0.0',
                    ],
                ]
            ]
        );
        \WP_Mock::userFunction(
            'get_mu_plugins',
            ['return' => []]
        );
        \WP_Mock::userFunction(
            'get_site_option',
            ['args' => 'bedrock_autoloader', 'return' => false]
        );
        \WP_Mock::userFunction(
            'update_site_option',
            ['args' => ['bedrock_autoloader', \WP_Mock\Functions::type('array')], 'return' => true]
        );

        // can't test this due to side-effects in constructor
        // https://github.com/roots/bedrock-autoloader/issues/4
        // \WP_Mock::expectFilterAdded('show_advanced_plugins', $a);

        $a = new Autoloader();
        $a->loadPlugins();

        // TODO: Testing private fields is nasty. We need to refactor Autoloader to be testable
        $reflect = new \ReflectionClass(Autoloader::class);
        $cacheProp = $reflect->getProperty('cache');
        $cacheProp->setAccessible(true);

        $cache = $cacheProp->getValue($a);
        $this->assertCount(2, $cache['plugins'], 'plugin cache is not set properly');
        $this->assertEquals(2, $cache['count'], 'plugin count is wrong');

        $this->assertTrue(defined('fake_OwO'), 'mu plugins were not loaded');
        $this->assertEquals('loaded', fake_OwO);
        $this->assertEquals('loaded', fake_UwU);

        // yuck

    }
}
