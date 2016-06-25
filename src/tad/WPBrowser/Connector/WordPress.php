<?php

namespace tad\WPBrowser\Connector;

use Codeception\Lib\Connector\Universal;
use Symfony\Component\BrowserKit\Response;

class WordPress extends Universal
{

    /**
     * @param object $request
     * @return Response
     */
    public function doRequest($request)
    {
        if ($this->mockedResponse) {
            $response = $this->mockedResponse;
            $this->mockedResponse = null;
            return $response;
        }

        $_cookie = $request->getCookies();
        $_server = $request->getServer();
        $_files = $this->remapFiles($request->getFiles());

        $uri = str_replace('http://localhost', '', $request->getUri());

        $_request = $this->remapRequestParameters($request->getParameters());

        $_server['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_server['REQUEST_URI'] = $uri;

        $env = [
            'indexFile' => $this->index,
            'headers' => headers_list(),
            'cookie' => $_cookie,
            'server' => $_server,
            'file' => $_files,
            'request' => $_request,
        ];

        if (strtoupper($request->getMethod()) == 'GET') {
            $env['get'] = $env['request'];
        } else {
            $env['post'] = $env['request'];
        }

        $requestScript = dirname(dirname(__DIR__)) . '/scripts/request.php';

        $command = PHP_BINARY .
            ' ' . escapeshellarg($requestScript) .
            ' ' . escapeshellarg($this->index) .
            ' ' . escapeshellarg(base64_encode(serialize($env)));

        $pipesDescriptor = array(
            1 => array("pipe", "w")
        );
        $requestProcess = proc_open($command, $pipesDescriptor, $pipes);

        if (!is_resource($requestProcess)) {
            throw new \RuntimeException('Could not start separate request process.');
        }

        $_COOKIE = $_COOKIE ?: [];
        $_SERVER = $_SERVER ?: [];
        $_FILES = $_FILES ?: [];
        $_REQUEST = $_REQUEST ?: [];

        $rawProcessOutput = stream_get_contents($pipes[1]);
        $unserializedResponse = unserialize(base64_decode($rawProcessOutput));
        fclose($pipes[1]);

        proc_close($requestProcess);

        $_COOKIE = empty($unserializedResponse['cookie']) ? $_COOKIE : array_merge($_COOKIE, $unserializedResponse['cookie']);
        $_SERVER = empty($unserializedResponse['server']) ? $_SERVER : array_merge($_SERVER, $unserializedResponse['server']);
        $_FILES = empty($unserializedResponse['files']) ? $_FILES : array_merge($_FILES, $unserializedResponse['files']);
        $_REQUEST = empty($unserializedResponse['request']) ? $_REQUEST : array_merge($_REQUEST, $unserializedResponse['request']);
        $_GET = empty($unserializedResponse['get']) ? $_GET : array_merge($_GET, $unserializedResponse['get']);
        $_POST = empty($unserializedResponse['post']) ? $_POST : array_merge($_POST, $unserializedResponse['post']);

        $content = $unserializedResponse['content'];
        $headers = $unserializedResponse['headers'];

        $response = new Response($content, 200, $headers);

        return $response;
    }
}
