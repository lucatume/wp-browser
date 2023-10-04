<?php

use lucatume\WPBrowser\Process\Protocol\Request;
use lucatume\WPBrowser\Process\Protocol\Response;
use lucatume\WPBrowser\Process\SerializableThrowable;

$processSrcRoot = __DIR__ . '/..';
require_once $processSrcRoot . '/Protocol/Parser.php';
require_once $processSrcRoot . '/Protocol/Control.php';
require_once $processSrcRoot . '/Protocol/Request.php';
require_once $processSrcRoot . '/Protocol/ProtocolException.php';

try {
    if (!isset($argv[1])) {
        throw new RuntimeException('Payload empty.');
    }

    if (str_starts_with($argv[1], '$')) {
        $payload = $argv[1];
    } elseif (($payload = @file_get_contents($argv[1])) === false) {
        throw new RuntimeException("Could not read payload from file $argv[1]");
    }

    $request = Request::fromPayload($payload);
    $serializableClosure = $request->getSerializableClosure();
    $returnValue = $serializableClosure();
} catch (Throwable $throwable) {
    $returnValue = new SerializableThrowable($throwable);
}

$response = new Response($returnValue);
$responsePayload = Response::$stderrValueSeparator . $response->getPayload();

fwrite(STDERR, $responsePayload, strlen($responsePayload));

exit($response->getExitValue());
