<?php

namespace lucatume\WPBrowser\Events;


use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Property;

class DispatcherTest extends Unit
{
    use UopzFunctions;

    /**
     * It should allow listening to and dispatching events
     *
     * @test
     */
    public function should_allow_listening_to_and_dispatching_events()
    {
        $callStack = [];
        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'second';
        });
        $removeThird = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'third';
        }, -10);
        $removeFirst = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'first';
        }, 100);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'new first';
        }, 200);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        $removeThird();
        $removeFirst();

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'second',
        ], $callStack);
        $callStack = [];
    }

    /**
     * It should return a purposely built Dispatcher when the Codeception instance is not available
     *
     * @test
     */
    public function should_return_a_purposely_built_dispatcher_when_the_codeception_instance_is_not_available(): void
    {
        // Partial mocking.
        $this->uopzSetStaticMethodReturn(Dispatcher::class, 'getCodecept', null);
        // And property tampering.
        Property::setPrivateProperties(Dispatcher::class, [
            'codeceptionEventDispatcherInstance' => null,
        ]);

        $callStack = [];
        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'second';
        });
        $removeThird = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'third';
        }, -10);
        $removeFirst = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'first';
        }, 100);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'new first';
        }, 200);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        $removeThird();
        $removeFirst();

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'second',
        ], $callStack);
        $callStack = [];
    }
}
