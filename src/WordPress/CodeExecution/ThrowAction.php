<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\Utils\Serializer;
use Throwable;

class ThrowAction implements CodeExecutionActionInterface
{

    private Throwable $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getClosure(): Closure
    {
        $serializedException = Serializer::makeThrowableSerializable($this->exception);

        return static function () use ($serializedException): mixed {
            throw $serializedException;
        };
    }
}
