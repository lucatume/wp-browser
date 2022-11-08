<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\PreloadFilters;
use Serializable;
use function _PHPStan_9a6ded56a\RingCentral\Psr7\parse_query;

abstract class FileRequest implements Serializable
{
    /**
     * @var array<string,int|string|float|bool>
     */
    private array $requestVars;
    private string $targetFile;
    private string $requestUri;
    private array $cookieJar;
    /**
     * @var array<string>
     */
    private array $presetGlobalVars;
    /**
     * @var array<string,string>
     */
    private array $redirectFiles;
    /**
     * @var array<string,mixed>
     */
    private array $presetLocalVars;
    /**
     * @var array<string,mixed>
     */
    private array $serverVars = [];
    /**
     * @var array<Closure>
     */
    private array $preLoadClosures = [];

    public function __construct(
        string $requestUri,
        string $targetFile,
        array $requestVars = [],
        array $cookieJar = [],
        array $redirectFiles = [],
        array $presetLocalVars = [],
    ) {
        $this->requestUri = $requestUri;
        $this->targetFile = $targetFile;
        $this->requestVars = $requestVars;
        $this->cookieJar = $cookieJar;
        $this->redirectFiles = $redirectFiles;
        $this->presetLocalVars = $presetLocalVars;
    }

    abstract protected function getMethod(): string;

    public function execute(): void
    {
        if (count($this->presetGlobalVars) > 0) {
            foreach ($this->presetGlobalVars as $global) {
                global $$global;
            }
        }

        $method = $this->getMethod();

        if ($method === 'GET' && count($this->requestVars)) {
            $this->requestUri .= '?' . http_build_query($this->requestVars);
        } else {
            $query = parse_url($this->requestUri, PHP_URL_QUERY);
            parse_str($query, $queryArgs);
            foreach ($queryArgs as $key => $value) {
                $_GET[$key] = $value;
            }
            $this->targetFile = str_replace('?' . $query, '', $this->targetFile);
        }

        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $this->requestUri;
        $_SERVER['HTTP_HOST'] = 'localhost';

        foreach ($this->serverVars as $key => $value) {
            $_SERVER[$key] = $value;
        }

        switch ($method) {
            case 'GET':
                $_GET = array_merge($_GET, $this->requestVars);
                break;
            case 'POST':
                $_POST = $this->requestVars;
                break;
            default:
                throw new FileRequestException(sprintf('Unsupported request method: %s', $method));
        }

        foreach ($this->presetLocalVars as $key => $value) {
            $$key = $value;
        }

        foreach ($this->redirectFiles as $fromFile => $toFile) {
            MonkeyPatch::redirectFileToFile($fromFile, $toFile);
        }

        // Intercept calls to wp_die to cast them to exceptions.
        PreloadFilters::filterWpDieHandlerToExit();

        // Reveal the errors.
        define('WP_DISABLE_FATAL_ERROR_HANDLER', true);

        // Set up the cookie jar.
        foreach ($this->cookieJar as $key => $value) {
            $_COOKIE[$key] = $value;
        }

        foreach ($this->preLoadClosures as $preLoadClosure) {
            $preLoadClosure($this->targetFile);
        }

        // Preset these to avoid issues with WP expecting them being defined already.
        global /** @noinspection PhpUnusedLocalVariableInspection */
        $status, $page;

        require $this->targetFile;
    }

    public function serialize()
    {
        return serialize([
            'requestVars' => $this->requestVars ?? [],
            'presetGlobalVars' => $this->presetGlobalVars ?? [],
            'presetLocalVars' => $this->presetLocalVars ?? [],
            'redirectFiles' => $this->redirectFiles ?? [],
            'requestUri' => $this->requestUri,
            'targetFile' => $this->targetFile,
            'cookieJar' => $this->cookieJar
        ]);
    }

    public function unserialize(string $data)
    {
        $unserializedData = unserialize($data, ['allowed_classes' => false]);

        $this->presetGlobalVars = $unserializedData['presetGlobalVars'] ?? false;
        $this->presetLocalVars = $unserializedData['presetLocalVars'] ?? [];
        $this->redirectFiles = $unserializedData['redirectFiles'] ?? [];
        $this->requestUri = $unserializedData['requestUri'];
        $this->requestVars = $unserializedData['requestVars'] ?? [];
        $this->targetFile = $unserializedData['targetFile'] ?? false;
        $this->cookieJar = $unserializedData['cookieJar'] ?? false;

        if (!$this->targetFile) {
            throw new FileRequestException('No target file specified.');
        }
    }

    public function setServerVar(string $key, string $value): FileRequest
    {
        $this->serverVars[$key] = $value;
        return $this;
    }

    public function addPreloadClosure(Closure $preLoadClosure): void
    {
        $this->preLoadClosures[] = $preLoadClosure;
    }

}
