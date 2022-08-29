<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use lucatume\WPBrowser\Exceptions\RequestException;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\PreloadFilters;

class FileGetRequest implements \Serializable
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

    public function execute(): void
    {
        if (count($this->presetGlobalVars) > 0) {
            foreach ($this->presetGlobalVars as $global) {
                global $$global;
            }
        }

        if (count($this->requestVars)) {
            $this->requestUri .= '?' . http_build_query($this->requestVars);
        }

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $this->requestUri;

        foreach ($this->requestVars as $key => $value) {
            $_GET[$key] = $value;
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

        // Authenticate the user.
        foreach ($this->cookieJar as $key => $value) {
            $_COOKIE[$key] = $value;
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
            throw new RequestException('No target file specified.');
        }
    }
}
