<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;

class FileRequestFactory
{
    /**
     * @param array<string, string> $redirectFiles
     * @param array<string, mixed>  $presetGlobalVars
     */
    public function __construct(
        private string $wpRootDir,
        private string $domain,
        private array $redirectFiles = [],
        private array $presetGlobalVars = []
    ) {
    }

    /**
     * @param array<string,mixed> $queryArgs
     */
    public function buildGetRequest(string $requestUri = '/', array $queryArgs = []): FileGetRequest
    {
        return $this->buildRequest('GET', $requestUri, $queryArgs);
    }

    /**
     * @param array<string,mixed> $queryArgs
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

    /**
     * @param array<string,mixed> $queryArgs
     */
    public function buildPostRequest(string $requestUri, array $queryArgs): FilePostRequest
    {
        return $this->buildRequest('POST', $requestUri, $queryArgs);
    }

    /**
     * @param array<string,mixed> $queryArgs
     */
    protected function buildRequest(
        string $method,
        string $requestUri,
        array $queryArgs,
    ): FileGetRequest|FilePostRequest {
        $targetFile = rtrim($this->wpRootDir, '\\/') . '/' . ltrim($requestUri, '\\/');
        $cookies = [];

        $queryArgs = $this->resolveQueryArgs($queryArgs);

        $request = match ($method) {
            'GET' => new FileGetRequest(
                $this->domain,
                $requestUri,
                $targetFile,
                $queryArgs,
                $cookies,
                $this->redirectFiles,
                $this->presetGlobalVars
            ),
            'POST' => new FilePostRequest(
                $this->domain,
                $requestUri,
                $targetFile,
                $queryArgs,
                $cookies,
                $this->redirectFiles,
                $this->presetGlobalVars
            ),
            default => throw new  RuntimeException("Unsupported request method: {$method}")
        };

        return $request;
    }
}
