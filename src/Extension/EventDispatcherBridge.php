<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use lucatume\WPBrowser\Events\Dispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcher;

class EventDispatcherBridge extends Extension
{
    /**
     * @var array<string,array{0: string, 1: int}>
     */
    public static $events = [
        Events::MODULE_INIT => ['onModuleInit', 0 ],
        Events::SUITE_INIT => ['onSuiteInit', 0],
        Events::SUITE_BEFORE => ['onSuiteBefore', 0],
    ];
    /**
     * @var bool
     */
    private $eventDispatcherCaptured = false;

    public function onModuleInit(SuiteEvent $event): void
    {
        $this->captureEventDispatcher($event, Events::MODULE_INIT);
    }

    public function onSuiteInit(SuiteEvent $event): void
    {
        $this->captureEventDispatcher($event, Events::SUITE_INIT);
    }

    public function onSuiteBefore(SuiteEvent $event): void
    {
        $this->captureEventDispatcher($event, Events::SUITE_BEFORE);
    }

    private function captureEventDispatcher(SuiteEvent $event, string $eventName): void
    {
        if ($this->eventDispatcherCaptured) {
            return;
        }

        $eventDispatcher = null;
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 5);
        foreach ($trace as $traceEntry) {
            if (!(isset($traceEntry['object']) && $traceEntry['object'] instanceof SymfonyEventDispatcher)) {
                continue;
            }
            $eventDispatcher = $traceEntry['object'];
            break;
        }

        if ($eventDispatcher === null) {
            throw new ExtensionException($this, 'Could not find the application event dispatcher.');
        }

        $previousDispatcher = Dispatcher::getEventDispatcher();

        if ($previousDispatcher !== null) {
            /** @var callable[] $listeners */
            $listeners = $previousDispatcher->getListeners($eventName);

            // Call the listeners for this event now,remove them from the previous dispatcher.
            foreach ($listeners as $listener) {
                $listener($event, $eventName, $eventDispatcher);
                $previousDispatcher->removeListener($eventName, $listener);
            }
        }

        Dispatcher::setEventDispatcher($eventDispatcher);
        $this->eventDispatcherCaptured = true;
    }
}
