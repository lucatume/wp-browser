<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Event\FailEvent;
use Codeception\Events;
use Codeception\Extension;

/**
 * @internal
 */
class ParallelWorkerReporter extends Extension
{
    /** @var array<string,string> */
    public static $events = [
        Events::TEST_SUCCESS    => 'onSuccess',
        Events::TEST_FAIL       => 'onFail',
        Events::TEST_ERROR      => 'onError',
        Events::TEST_SKIPPED    => 'onSkipped',
        Events::TEST_INCOMPLETE => 'onIncomplete',
        Events::TEST_WARNING    => 'onWarning',
        Events::TEST_USELESS    => 'onUseless',
    ];

    /** @var resource|null */
    private $stream = null;

    /** @var resource|null */
    private $logStream = null;

    public function _initialize(): void
    {
        $this->_reconfigure(['settings' => ['silent' => true]]);

        $path = getenv('WPBROWSER_PARALLEL_EVENT_FILE');
        if ($path !== false && $path !== '') {
            $handle = @fopen($path, 'ab');
            if ($handle !== false) {
                $this->stream = $handle;
            }
        }

        $logPath = getenv('WPBROWSER_PARALLEL_LOG_FILE');
        if ($logPath !== false && $logPath !== '') {
            $handle = @fopen($logPath, 'ab');
            if ($handle !== false) {
                $this->logStream = $handle;
            }
        }
    }

    public function onSuccess(): void
    {
        $this->emit('.');
    }

    public function onFail(FailEvent $event): void
    {
        $this->emit('F');
        $this->log('[FAIL]', $event);
    }

    public function onError(FailEvent $event): void
    {
        $this->emit('E');
        $this->log('[ERROR]', $event);
    }

    public function onSkipped(FailEvent $event): void
    {
        $this->emit('S');
        $this->log('[SKIP]', $event);
    }

    public function onIncomplete(FailEvent $event): void
    {
        $this->emit('I');
        $this->log('[INCOMPLETE]', $event);
    }

    public function onWarning(): void
    {
        $this->emit('W');
    }

    public function onUseless(): void
    {
        $this->emit('U');
    }

    private function emit(string $char): void
    {
        if ($this->stream !== null) {
            fwrite($this->stream, $char);
            fflush($this->stream);
        }
    }

    private function log(string $prefix, FailEvent $event): void
    {
        if ($this->logStream === null) {
            return;
        }
        $name    = $event->getTest()->toString();
        $message = $event->getFail()->getMessage();
        fwrite($this->logStream, sprintf("%s %s\n  %s\n\n", $prefix, $name, $message));
        fflush($this->logStream);
    }
}
