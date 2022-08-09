<?php namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\Utils\Configuration;

class ConfigurationTest extends \Codeception\Test\Unit
{
    /**
     * It should allow setting aliases
     *
     * @test
     */
    public function should_allow_setting_aliases()
    {
        $config = new Configuration([
            'foo' => 'bar'
        ]);
        $config->setAliases([
            'baz' => 'foo'
        ]);

        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals('bar', $config['baz']);
        $this->assertEquals('bar', $config->get('foo'));
        $this->assertEquals('bar', $config->get('baz'));
    }

    /**
     * It should allow setting aliases at build time
     *
     * @test
     */
    public function should_allow_setting_aliases_at_build_time()
    {
        $config = new Configuration([
            'foo' => 'bar'
        ], [
            'baz' => 'foo'
        ]);

        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals('bar', $config['baz']);
        $this->assertEquals('bar', $config->get('foo'));
        $this->assertEquals('bar', $config->get('baz'));
    }
}
