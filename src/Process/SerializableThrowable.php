<?php

namespace lucatume\WPBrowser\Process;

use Exception;
use lucatume\WPBrowser\Utils\ErrorHandling;
use lucatume\WPBrowser\Utils\Property;
use Throwable;

class SerializableThrowable extends Exception implements \Serializable{
    private array $serializableData;
    private string $wrappedThrowableClass;

    public function __construct(Throwable $t)
    {
        $message = $t->getMessage();
        $code = $t->getCode();
        parent::__construct($message, $code, null);
        $this->serializableData = [
            'message' => $message,
            'code' => $code,
            'file' => $t->getFile(),
            'line' => $t->getLine(),
            'trace' => ErrorHandling::makeTraceSerializable($t->getTrace()),
            'traceAsString' => $t->getTraceAsString(),
            'wrappedThrowableClass' => get_class($t),
        ];
    }

    public function serialize()
    {
        return serialize($this->serializableData);
    }

    public function unserialize(string $data)
    {
        $this->serializableData = unserialize($data, ['allowed_classes' => [self::class]]);
        $this->message = $this->serializableData['message'];
        $this->code = $this->serializableData['code'];
        $this->file = $this->serializableData['file'];
        $this->line = $this->serializableData['line'];
        $this->wrappedThrowableClass = $this->serializableData['wrappedThrowableClass'];
        Property::setPrivateProperties($this, [
            'trace' => $this->serializableData['trace'],
            'traceAsString' => $this->serializableData['traceAsString']
        ]);
    }

    public function getWrappedThrowableClass(): string
    {
        return $this->wrappedThrowableClass;
    }
}
