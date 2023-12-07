<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;

class FileRequestFactory
{
    /**
     * @var string
     */
    private $wpRootDir;
    /**
     * @var string
     */
    private $domain;
    /**
     * @var array<string, string>
     */
    private $redirectFiles = [];
    /**
     * @var array<string>
     */
    private $presetGlobalVars = [];
    /**
     * @param array<string, string> $redirectFiles
     * @param array<string> $presetGlobalVars
     */
    public function __construct(string $wpRootDir, string $domain, array $redirectFiles = [], array $presetGlobalVars = [])
    {
        $this->wpRootDir = $wpRootDir;
        $this->domain = $domain;
        $this->redirectFiles = $redirectFiles;
        $this->presetGlobalVars = $presetGlobalVars;
    }

    /**
     * @param array<string,mixed> $queryArgs
     */
    public function buildGetRequest(string $requestUri = '/', array $queryArgs = []): FileGetRequest
    {
        $targetFile = rtrim($this->wpRootDir, '\\/') . '/' . ltrim($requestUri, '\\/');
        $cookies = [];

        $queryArgs = $this->resolveQueryArgs($queryArgs);

        return new FileGetRequest(
            $this->domain,
            $requestUri,
            $targetFile,
            $queryArgs,
            $cookies,
            $this->redirectFiles,
            $this->presetGlobalVars
        );
    }

    /**
     * @param array<string,mixed> $queryArgs
     * @return array<string,int|string|float|bool>
     * @throws RuntimeException
     */
    protected function resolveQueryArgs(array $queryArgs): array
    {
        foreach ($queryArgs as $key => $value) {
            if (!(is_numeric($value) || is_string($value) || is_bool($value))) {
                throw new RuntimeException('Key ' . $key . ' has invalid value in query args: only numeric,' .
                    ' string, and boolean values are allowed.');
            }
        }

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
        $targetFile = rtrim($this->wpRootDir, '\\/') . '/' . ltrim($requestUri, '\\/');
        $cookies = [];

        $queryArgs = $this->resolveQueryArgs($queryArgs);

        return new FilePostRequest(
            $this->domain,
            $requestUri,
            $targetFile,
            $queryArgs,
            $cookies,
            $this->redirectFiles,
            $this->presetGlobalVars
        );
    }
}
