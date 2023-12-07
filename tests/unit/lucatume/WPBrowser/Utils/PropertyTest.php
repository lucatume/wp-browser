<?php

namespace lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use InvalidArgumentException;

class PropertyAccessTestTarget
{
    /**
     * @var int
     */
    private $private = 23;
    /**
     * @var int
     */
    protected $protected = 89;
    /**
     * @var int
     */
    public $public = 2389;
    /**
     * @var int
     */
    private static $privateStatic = 23;
    /**
     * @var int
     */
    protected static $protectedStatic = 89;
    /**
     * @var int
     */
    public static $publicStatic = 2389;
}

class PropertyAccessTestTargetChild extends PropertyAccessTestTarget
{
    /**
     * @var int
     */
    private $private = 42;
    /**
     * @var int
     */
    protected $protected = 0;
    /**
     * @var int
     */
    public static $publicStatic = 0;
}

class PropertyAccessTestTargetGrandchild extends PropertyAccessTestTargetChild
{
    /**
     * @var int
     */
    private $private = 17;
    /**
     * @var int
     */
    public static $publicStatic = 1731;
}

class PropertyTest extends Unit
{
    /**
     * It should allow reading all properties of an object
     *
     * @test
     */
    public function should_allow_reading_all_properties_of_an_object()
    {
        $object = new PropertyAccessTestTarget();
        $this->assertEquals(23, Property::readPrivate($object, 'private'));
        $this->assertEquals(89, Property::readPrivate($object, 'protected'));
        $this->assertEquals(2389, Property::readPrivate($object, 'public'));
    }

    /**
     * It should allow reading all static properties of an object
     *
     * @test
     */
    public function should_allow_reading_all_static_properties_of_an_object()
    {
        $this->assertEquals(23, Property::readPrivate(PropertyAccessTestTarget::class, 'privateStatic'));
        $this->assertEquals(89, Property::readPrivate(PropertyAccessTestTarget::class, 'protectedStatic'));
        $this->assertEquals(2389, Property::readPrivate(PropertyAccessTestTarget::class, 'publicStatic'));
    }

    /**
     * It should allow reading all properties of an inherited object
     *
     * @test
     */
    public function should_allow_reading_all_properties_of_an_inherited_object()
    {
        $object = new PropertyAccessTestTargetGrandchild();
        $this->assertEquals(17, Property::readPrivate($object, 'private'));
        $this->assertEquals(0, Property::readPrivate($object, 'protected'));
        $this->assertEquals(2389, Property::readPrivate($object, 'public'));
    }


    /**
     * It should allow reading all static properties of an inherited object
     *
     * @test
     */
    public function should_allow_reading_all_static_properties_of_an_inherited_object()
    {
        $this->assertEquals(23, Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'privateStatic'));
        $this->assertEquals(89, Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'protectedStatic'));
        $this->assertEquals(1731, Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'publicStatic'));
    }

    /**
     * It should throw if trying to read undefined object property
     *
     * @test
     */
    public function should_throw_if_trying_to_read_undefined_object_property()
    {
        $this->expectException(InvalidArgumentException::class);
        Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'privateNot');
    }

    /**
     * It should throw if trying to read undefined static object property
     *
     * @test
     */
    public function should_throw_if_trying_to_read_undefined_static_object_property(){
        $this->expectException(InvalidArgumentException::class);
        Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'privateStaticNot');
    }
}
