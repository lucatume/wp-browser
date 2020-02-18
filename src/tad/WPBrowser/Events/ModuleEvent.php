<?php
/**
 * An event fired by a Codeception Module.
 *
 * @package tad\WPBrowser\Events
 */

namespace tad\WPBrowser\Events;

use Codeception\Module;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

/**
 * Class ModuleEvent
 *
 * @package tad\WPBrowser\Events
 */
class ModuleEvent extends SymfonyEvent
{
    /**
     * The Module that generated the event.
     * @var Module
     */
    protected $module;

    /**
     * Optional data attached to the event.
     * @var mixed|null
     */
    protected $data;

    /**
     * ModuleEvent constructor.
     *
     * @param Module $module The Codeception Module that fired the event.
     * @param null|mixed   $data Additional, optional, data attached to the event.
     */
    public function __construct(Module $module, $data = null)
    {
        $this->module = $module;
        $this->data = $data;
    }

    /**
     * Returns the Codeception Module instance that fired the event.
     *
     * @return Module The Codeception Module instance that fired the event.
     */
    public function getModule()
    {
        return $this->module;
    }
}
