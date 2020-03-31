<?php
/**
 * Models an event fired by the wp-browser package.
 *
 * @package tad\WPBrowser\Events
 */

namespace tad\WPBrowser\Events;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Class WpbrowserEvent
 *
 * @package tad\WPBrowser\Events
 */
class WpbrowserEvent extends SymfonyEvent
{

    /**
     * The event name or handle.
     * @var string
     */
    protected $name;
    /**
     * The event dispatcher.
     *
     * @var object|null
     */
    protected $dispatcher;

    /**
     * Additional context or data for the event.
     *
     * @var array
     */
    protected $context;

    /**
     * WpbrowserEvent constructor.
     *
     * @param string      $name       The event name or handle.
     * @param object|null $dispatcher The event dispatcher.
     * @param array       $context    Additional context or data for the event.
     */
    public function __construct($name, $dispatcher = null, array $context = [])
    {
        $this->name       = $name;
        $this->dispatcher = $dispatcher;
        $this->context    = $context;
    }
}
