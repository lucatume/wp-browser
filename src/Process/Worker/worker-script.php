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
    $request = Request::fromPayload($argv[1]);
    $serializableClosure = $request->getSerializableClosure();
    $returnValue = $serializableClosure();
} catch (Throwable $throwable) {
    $returnValue = new SerializableThrowable($throwable);
}

$response = new Response($returnValue);
$responsePayload = Response::$stderrValueSeparator . $response->getPayload();

fwrite(STDERR, $responsePayload, strlen($responsePayload));

exit($response->getExitValue());
