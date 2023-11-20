<?php

use PHPUnit\Framework\Assert;

class BeforeAfterMethodsTest extends \Codeception\TestCase\WPTestCase
{
    private static $staticCanary = true;
    private $canary = false;

    public function _before()
    {
        $this->canary = true;
    }

    public function _after()
    {
        self::$staticCanary = false;
    }

    public static function wpTearDownAfterClass()
    {
        Assert::assertFalse(self::$staticCanary);
    }

    public function test_before_method_is_loaded()
    {
        $this->assertTrue($this->canary);
    }
}
