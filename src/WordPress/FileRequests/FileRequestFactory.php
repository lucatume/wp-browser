<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;

class FileRequestFactory
{
    private string $domain;
    private string $wpRootDir;
    /**
     * @var array<string,string>
     */
    private array $redirectFiles;
    /**
     * @var array<string,mixed>
     */
    private array $presetGlobalVars;

    public function __construct(
        string $wpRootDir,
        string $domain,
        array $redirectFiles = [],
        array $presetGlobalVars = [],
    ) {
        $this->wpRootDir = $wpRootDir;
        $this->domain = $domain;
        $this->redirectFiles = $redirectFiles;
        $this->presetGlobalVars = $presetGlobalVars;
    }

    public function buildGetRequest(string $requestUri = '/', array $queryArgs = []): FileGetRequest
    {
        return $this->buildRequest('GET', $requestUri, $queryArgs);
    }

    /**
     * @return array<string,mixed>
     */
    protected function resolveQueryArgs(array $queryArgs): array
    {
        $resolved = [];

        foreach ($queryArgs as $key => $value) {
            $resolved[$key] = $value instanceof Closure ? $value() : $value;
        }

        return $resolved;
    }

    public function buildPostRequest(string $requestUri, array $queryArgs): FilePostRequest
    {
        return $this->buildRequest('POST', $requestUri, $queryArgs);
    }

    protected function buildRequest(
        string $method,
        string $requestUri,
        array $queryArgs,
    ): FileGetRequest|FilePostRequest {
        $targetFile = rtrim($this->wpRootDir, '\\/') . '/' . ltrim($requestUri, '\\/');
        $cookies = [];

        $queryArgs = $this->resolveQueryArgs($queryArgs);

        switch ($method) {
            default:
            case 'GET':
                $request = new FileGetRequest(
                    $this->domain,
                    $requestUri,
                    $targetFile,
                    $queryArgs,
                    $cookies,
                    $this->redirectFiles,
                    $this->presetGlobalVars
                );
                break;
            case 'POST':
                $request = new FilePostRequest(
                    $this->domain,
                    $requestUri,
                    $targetFile,
                    $queryArgs,
                    $cookies,
                    $this->redirectFiles,
                    $this->presetGlobalVars
                );
                break;
        }

        return $request;
    }
}
