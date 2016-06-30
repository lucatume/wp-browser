<?php

namespace tad\WPBrowser\Connector;

use Codeception\Lib\Connector\Universal;
use Symfony\Component\BrowserKit\Response;

class WordPress extends Universal
{
    /**
     * @var bool
     */
    protected $insulated = true;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $wpRootFolder;

    /**
     * @param object $request
     * @return Response
     */
    public function doRequestInProcess($request)
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
        $_server['HTTP_HOST'] = $this->domain;
        $_server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_server['PHP_SELF'] = $this->index;

        $this->updateIndexFromUri($uri);

        $env = [
            'indexFile' => $this->index,
            'headers' => $this->headers,
            'cookie' => $_cookie,
            'server' => $_server,
            'files' => $_files,
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
            1 => array('pipe', 'w')
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

        $_COOKIE = empty($unserializedResponse['cookie']) ? [] : $unserializedResponse['cookie'];
        $_SERVER = empty($unserializedResponse['server']) ? [] : $unserializedResponse['server'];
        $_FILES = empty($unserializedResponse['files']) ? [] : $unserializedResponse['files'];
        $_REQUEST = empty($unserializedResponse['request']) ? [] : $unserializedResponse['request'];
        $_GET = empty($unserializedResponse['get']) ? [] : $unserializedResponse['get'];
        $_POST = empty($unserializedResponse['post']) ? [] : $unserializedResponse['post'];

        $content = $unserializedResponse['content'];
        $headers = $this->replaceSiteUrlDeep($unserializedResponse['headers'], $this->url);

        $response = new Response($content, $unserializedResponse['status'], $headers);

        return $response;
    }

    private function updateIndexFromUri($uri)
    {
        preg_match("/(^\\/?.*\\.php)/uiU", $uri, $matches);
        if (!empty($matches[1])) {
            $candidateIndex = $this->wpRootFolder . '/' . ltrim($matches[1], '/');
            if (!file_exists($candidateIndex)) {
                // could be a pretty link
                return;
            }

            $this->index = $candidateIndex;
        }
    }

    private function replaceSiteUrlDeep($array, $url)
    {
        if (empty($array)) {
            return [];
        }
        $replaced = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $replaced[$key] = $this->replaceSiteUrlDeep($value, $url);
            } else {
                $replaced[$key] = str_replace($url, 'http://localhost', $value);
            }
        }

        return $replaced;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function setRootFolder($wpRootFolder)
    {
        $this->wpRootFolder = $wpRootFolder;
    }
}
