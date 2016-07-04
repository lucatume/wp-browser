<?php

namespace tad\WPBrowser\Connector;

use Codeception\Lib\Connector\Universal;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\Response;
use tad\WPBrowser\Module\Support\UriToIndexMapper;

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
    protected $rootFolder;
    /**
     * @var UriToIndexMapper
     */
    protected $uriToIndexMapper;

    public function __construct(array $server = array(), History $history = null, CookieJar $cookieJar = null, UriToIndexMapper $uriToIndexMapper = null)
    {
        parent::__construct($server, $history, $cookieJar);
        $this->uriToIndexMapper = $uriToIndexMapper ? $uriToIndexMapper : new UriToIndexMapper($this->rootFolder);
    }

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

        $requestCookie = $request->getCookies();
        $requestServer = $request->getServer();
        $requestFiles = $this->remapFiles($request->getFiles());

        $uri = str_replace('http://localhost', '', $request->getUri());

        $requestRequestArray = $this->remapRequestParameters($request->getParameters());

        $requestServer['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $requestServer['REQUEST_URI'] = $uri;
        $requestServer['HTTP_HOST'] = $this->domain;
        $requestServer['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $this->index = $this->uriToIndexMapper->getIndexForUri($uri);

        $phpSelf = str_replace($this->rootFolder, '', $this->index);
        $requestServer['PHP_SELF'] = $phpSelf;

        $env = [
            'indexFile' => $this->index,
            'headers' => $this->headers,
            'cookie' => $requestCookie,
            'server' => $requestServer,
            'files' => $requestFiles,
            'request' => $requestRequestArray,
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

    public function setIndexFor($uri)
    {
        $this->index = $this->rootFolder . $this->uriToIndexMapper->getIndexForUri($uri);
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * @param string $rootFolder
     */
    public function setRootFolder($rootFolder)
    {
        if (!is_dir($rootFolder)) {
            throw new \InvalidArgumentException('Root folder [' . $rootFolder . '] is not an existing folder!');
        }
        $this->rootFolder = $rootFolder;
        $this->uriToIndexMapper->setRoot($rootFolder);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
}
