<?php

namespace lucatume\WPBrowser\Utils;

class PropertyAccessTestTarget
{
    private int $private = 23;
    protected int $protected = 89;
    public int $public = 2389;
    private static int $privateStatic = 23;
    protected static int $protectedStatic = 89;
    public static int $publicStatic = 2389;
}

class PropertyAccessTestTargetChild extends PropertyAccessTestTarget
{
    private int $private = 42;
    protected int $protected = 0;
    public static int $publicStatic = 0;
}

class PropertyAccessTestTargetGrandchild extends PropertyAccessTestTargetChild
{
    private int $private = 17;
    public static int $publicStatic = 1731;
}

class PropertyTest extends \Codeception\Test\Unit
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
        $this->expectException(\InvalidArgumentException::class);
        Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'privateNot');
    }

    /**
     * It should throw if trying to read undefined static object property
     *
     * @test
     */
    public function should_throw_if_trying_to_read_undefined_static_object_property(){
        $this->expectException(\InvalidArgumentException::class);
        Property::readPrivate(PropertyAccessTestTargetGrandchild::class, 'privateStaticNot');
    }
}
