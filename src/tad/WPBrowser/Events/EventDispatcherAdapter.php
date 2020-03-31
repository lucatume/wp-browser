<?php
/**
 * A wrapper of Symfony Event Dispatcher to deal with different versions of the framework.
 *
 * @package tad\WPBrowser\Events
 */

namespace tad\WPBrowser\Events;

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

/**
 * Class EventDispatcherAdapter
 *
 * @package tad\WPBrowser\Events
 */
class EventDispatcherAdapter {
	/**
	 * The wrapped Symfony Event Dispatcher instance.
	 * @var SymfonyEventDispatcher
	 */
	protected $eventDispatcher;
	protected $dispatchWithObject;

	/**
	 * EventDispatcherAdapter constructor.
	 *
	 * @param SymfonyEventDispatcher $eventDispatcher The Symfony Event Dispatcher instance to wrap.
	 */
	public function __construct( SymfonyEventDispatcher $eventDispatcher ) {
		$this->eventDispatcher = $eventDispatcher;
	}

	public function addListener( $eventName, callable $listener, $priority ) {
		$this->eventDispatcher->addListener( $eventName, $listener, $priority );
	}

	public function dispatch( $eventName, $origin = null, array $context = [] ) {
		$eventObject = new WpbrowserEvent( $eventName, $origin, $context );

		if ( $this->dispatchWithObject() ) {
			$this->eventDispatcher->dispatch( $eventObject, $eventName );

			return;
		}

		$this->eventDispatcher->dispatch( $eventName, $eventObject );
	}

	protected function dispatchWithObject() {
		if ( $this->dispatchWithObject !== null ) {
			return $this->dispatchWithObject;
		}

		try {
			$methodReflection = new \ReflectionMethod( $this->eventDispatcher, 'dispatch' );
		} catch ( \ReflectionException $e ) {
			$this->dispatchWithObject = false;

			return $this->dispatchWithObject;
		}

		$methodArguments          = $methodReflection->getParameters();
		$firstArgument            = count( $methodArguments ) ? reset( $methodArguments ) : false;
		$this->dispatchWithObject = $firstArgument instanceof \ReflectionParameter
		                            && $firstArgument->getType() === 'object';

		return $this->dispatchWithObject;
	}

	public function setDispatchWithObject( $dispatchWithObject ) {
		$this->dispatchWithObject = (bool) $dispatchWithObject;
	}
}
