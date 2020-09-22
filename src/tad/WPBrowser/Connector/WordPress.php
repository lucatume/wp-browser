<?php
/**
 * A framework browser.
 *
 * @package tad\WPBrowser\Connector
 */

namespace tad\WPBrowser\Connector;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Universal;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Process\Process;
use tad\WPBrowser\Module\Support\UriToIndexMapper;

// phpcs:disable
/**
 * Triggers the autoload of the InnerBrowser class, if not already loaded, to make sure the Symfony component
 * aliases are set up by the Codeception library layer.
 */
class_exists( 'Codeception\Lib\InnerBrowser', true );
// phpcs:enable

/**
 * Class WordPress
 *
 * @package tad\WPBrowser\Connector
 */
class WordPress extends Universal
{
    /**
     * Whether requests should be done in isolation or not.
     *
     * @var bool
     */
    protected $insulated = true;

    /**
     * The current URL.
     *
     * @var string
     */
    protected $url;

    /**
     * The current domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * An array of the current headers.
     *
     * @var array<string,string>
     */
    protected $headers;

    /**
     * The current document root.
     *
     * @var string
     */
    protected $rootFolder;

    /**
     * The URI ot file mapper.
     *
     * @var UriToIndexMapper
     */
    protected $uriToIndexMapper;

    /**
     * WordPress constructor.
     *
     * @param array<string,mixed>   $server           The $_SERVER input.
     * @param History|null          $history          The history input.
     * @param CookieJar|null        $cookieJar        The cookies jar.
     * @param UriToIndexMapper|null $uriToIndexMapper The URI to URL index mapper.
     */
    public function __construct(
        array $server = array(),
        History $history = null,
        CookieJar $cookieJar = null,
        UriToIndexMapper $uriToIndexMapper = null
    ) {
        parent::__construct($server, $history, $cookieJar);
        $this->uriToIndexMapper = $uriToIndexMapper ? $uriToIndexMapper : new UriToIndexMapper($this->rootFolder);
    }

    /**
     * Executes the current reques in process.
     *
     * @param Request $request The request object.
     *
     * @return Response The request response.
     *
     * @throws \RuntimeException If the request URI could not be parsed.
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

        $parseResult = parse_url($request->getUri());

        if ($parseResult === false) {
            throw new \RuntimeException('Request URI could not be parsed.');
        }

        $uri = isset($parseResult['path']) ? $parseResult['path'] : '/';
        if (array_key_exists('query', $parseResult)) {
            $uri .= '?' . $parseResult['query'];
        }

        $requestRequestArray = $this->remapRequestParameters($request->getParameters());

        $requestServer['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $requestServer['REQUEST_URI'] = $uri;
        $requestServer['HTTP_HOST'] = $this->domain;
        $requestServer['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $requestServer['SERVER_NAME'] = $this->domain;
        $requestServer['HTTP_CLIENT_IP'] = '127.0.0.1';

        $this->index = $this->uriToIndexMapper->getIndexForUri($uri);

        $phpSelf = str_replace($this->rootFolder, '', $this->index);
        $requestServer['PHP_SELF'] = $phpSelf;

        $env = [
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

        $process = new Process($command);
        $process->run();
        $rawProcessOutput = $process->getOutput();

        $unserializedResponse = @unserialize(base64_decode($rawProcessOutput));

        if (false === $unserializedResponse) {
            $message = 'Server responded with: ' . $rawProcessOutput;
            throw new ModuleException(\Codeception\Module\WordPress::class, $message);
        }

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

    /**
     * Replaces the site URL with the mock one recursively.
     *
     * @param array<string,mixed> $input The array to replace the URL in.
     * @param string              $url   The URL to replace.
     *
     * @return array<string,mixed> The input array with the URL replaced.
     */
    protected function replaceSiteUrlDeep($input, $url)
    {
        if (empty($input)) {
            return [];
        }
        $replaced = [];
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $replaced[$key] = $this->replaceSiteUrlDeep($value, $url);
            } else {
                $replaced[$key] = str_replace(urlencode($url), urldecode(''), str_replace($url, '', $value));
            }
        }

        return $replaced;
    }

    /**
     * Sets the base URL.
     *
     * @param string $url The base URL.
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Sets the index file for a URI request.
     *
     * @param string $uri The request URI.
     *
     * @return void
     */
    public function setIndexFor($uri)
    {
        $this->index = $this->rootFolder . $this->uriToIndexMapper->getIndexForUri($uri);
    }

    /**
     * Returns the current index file.
     *
     * @return string The current index file.
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns the current root directory.
     *
     * @return string The current root directory.
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Sets the root directory.
     *
     * @param string $rootFolder The new root directory.
     *
     * @return void
     */
    public function setRootFolder($rootFolder)
    {
        if (!is_dir($rootFolder)) {
            throw new \InvalidArgumentException('Root folder [' . $rootFolder . '] is not an existing folder!');
        }
        $this->rootFolder = $rootFolder;
        $this->uriToIndexMapper->setRoot($rootFolder);
    }

    /**
     * Returns the current request headers.
     *
     * @return array<string,mixed> The current request headers.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets the request headers.
     *
     * @param array<string,string> $headers The request headers.
     *
     * @return void
     */
    public function setHeaders(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * Returns the current domain.
     *
     * @return string The current domain.
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the current domain.
     *
     * @param string $domain The current domain.
     *
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Resets the cookies.
     *
     * @return void
     */
    public function resetCookies()
    {
        $this->cookieJar = new CookieJar();
    }
}
