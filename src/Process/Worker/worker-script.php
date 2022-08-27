<?php

use lucatume\WPBrowser\Exceptions\SerializableThrowable;
use lucatume\WPBrowser\Utils\ErrorHandling;
use lucatume\WPBrowser\Utils\Property;
use Opis\Closure\SerializableClosure;

[$base64EncodedControl, $base64EncodedSerializedClosure] = array_slice($argv, 1);

$serializedControl = base64_decode($base64EncodedControl);
$serializedSerializableClosure = base64_decode($base64EncodedSerializedClosure);

/** @var array{autoloadFile: string, returnValueSeparator: string} $control */
$control = array_replace(
    ['autoloadFile' => '', 'returnValueSeparator' => '~=returnValueSep=~'],
    unserialize($serializedControl, ['allowed_classes' => false])
);

if (is_file($control['autoloadFile'])) {
    require_once $control['autoloadFile'];
}

try {
    $serializableClosure = unserialize($serializedSerializableClosure, ['allowed_classes' => true]);
    $exitValue = 0;
    $returnValue = $serializableClosure();
} catch (\Throwable $throwable) {
    $exitValue = 1;
    $returnValue = new SerializableThrowable($throwable);
}

$returnValueSeparator = $control['returnValueSeparator'];
$base64EncodedReturnValueStderrPayload = $returnValueSeparator . base64_encode(
        serialize(
            (new SerializableClosure(static function () use ($returnValue) {
                return $returnValue;
            }))
        )
    ) . $returnValueSeparator . base64_encode(
        serialize([
            'memoryPeakUsage' => memory_get_peak_usage()
        ])
    );
fwrite(STDERR, $base64EncodedReturnValueStderrPayload, strlen($base64EncodedReturnValueStderrPayload));

exit($exitValue);

