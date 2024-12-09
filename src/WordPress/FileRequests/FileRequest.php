<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;
use ErrorException;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\PreloadFilters;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\WPConfigFile;

abstract class FileRequest
{
    /**
     * @var string
     */
    private $domain;
    /**
     * @var string
     */
    private $requestUri;
    /**
     * @var string
     */
    private $targetFile;
    /**
     * @var array<string, (int | string | float | bool)>
     */
    private $requestVars = [];
    /**
     * @var array<string, string>
     */
    private $cookieJar = [];
    /**
     * @var array<string, string>
     */
    private $redirectFiles = [];
    /**
     * @var array<string, mixed>
     */
    private $presetGlobalVars = [];
    /**
     * @var array<string, mixed>
     */
    private $presetLocalVars = [];
    /**
     * @var array<(bool | int | string | float)>
     */
    private $constants = [];
    use WordPressChecks;

    /**
     * @var array<string,mixed>
     */
    private $serverVars = [];
    /**
     * @var array<Closure>
     */
    private $preloadClosures = [];
    /**
     * @var array<array{string, (callable(): mixed)|string, int, int}>
     */
    private $preloadFilters = [];
    /**
     * @var array<Closure>
     */
    private $afterLoadClosures = [];
    /**
     * @var int
     */
    private $errorLevel = E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE
    | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR;

    /**
     * @param array<string,int|string|float|bool> $requestVars
     * @param array<string,string> $cookieJar
     * @param array<string,mixed> $presetGlobalVars
     * @param array<string, string> $redirectFiles
     * @param array<string, mixed> $presetLocalVars
     * @param array<bool|int|string|float> $constants
     */
    public function __construct(string $domain, string $requestUri, string $targetFile, array $requestVars = [], array $cookieJar = [], array $redirectFiles = [], array $presetGlobalVars = [], array $presetLocalVars = [], array $constants = [])
    {
        $this->domain = $domain;
        $this->requestUri = $requestUri;
        $this->targetFile = $targetFile;
        $this->requestVars = $requestVars;
        $this->cookieJar = $cookieJar;
        $this->redirectFiles = $redirectFiles;
        $this->presetGlobalVars = $presetGlobalVars;
        $this->presetLocalVars = $presetLocalVars;
        $this->constants = $constants;
    }

    abstract protected function getMethod(): string;

    /**
     * @return array<int,mixed>
     * @throws FileRequestException
     * @throws ErrorException
     */
    public function execute(): array
    {
        if (count($this->presetGlobalVars) > 0) {
            foreach ($this->presetGlobalVars as $key => $value) {
                global $$key;
                $$key = $value;
            }
        }

        $method = $this->getMethod();

        if ($method === 'GET' && count($this->requestVars)) {
            $this->requestUri .= '?' . http_build_query($this->requestVars);
        } else {
            $query = parse_url($this->requestUri, PHP_URL_QUERY);

            if ($query === false) {
                throw new FileRequestException(
                    sprintf(
                        'Unable to parse query string from request URI: %s',
                        $this->requestUri
                    )
                );
            }

            parse_str((string)$query, $queryArgs);
            foreach ($queryArgs as $key => $value) {
                $_GET[$key] = $value;
            }
            $this->targetFile = str_replace('?' . $query, '', $this->targetFile);
        }

        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $this->requestUri;
        $_SERVER['HTTP_HOST'] = $this->domain;

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

        foreach ($this->preloadFilters as [$hookName, $callback, $priority, $acceptedArgs]) {
            PreloadFilters::addFilter($hookName, $callback, $priority, $acceptedArgs);
        }

        // Reveal the errors: disable the fatal error handler.
        PreloadFilters::addFilter('wp_fatal_error_handler_enabled', function () {
            return false;
        }, 1000);

        // Set up the cookie jar.
        foreach ($this->cookieJar as $key => $value) {
            $_COOKIE[$key] = $value;
        }

        foreach ($this->constants as $constant => $value) {
            defined($constant) || define($constant, $value);
        }

        // Cast all errors to exceptions.
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (($errno & $this->errorLevel) === 0) {
                return true;
            }

            // Ignore E_WARNING from `fopen` calls: they are used by some plugins to try and check if a file exists.
            if ($errno === E_WARNING && strncmp($errstr, 'fopen', strlen('fopen')) === 0) {
                return true;
            }

            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_ALL);

        foreach ($this->preloadClosures as $preLoadClosure) {
            $preLoadClosure($this->targetFile);
        }

        require $this->targetFile;

        $returnValues = [];
        foreach ($this->afterLoadClosures as $afterLoadClosures) {
            $returnValues[] = $afterLoadClosures($this->targetFile);
        }

        return $returnValues;
    }

    /**
     * @return array{
     *     afterLoadClosures: array<Closure>,
     *     constants: array<string,mixed>,
     *     cookieJar: array<string,string>,
     *     domain: string,
     *     preloadClosures: array<Closure>,
     *     preloadFilters: array<array{string, (callable(): mixed)|string, int, int}>,
     *     presetGlobalVars: array<string,mixed>,
     *     presetLocalVars: array<string,mixed>,
     *     redirectFiles: array<string,string>,
     *     requestUri: string,
     *     requestVars?: array<string,int|string|float|bool>,
     *     targetFile: string
     *     }
     */
    public function __serialize(): array
    {
        return [
            'afterLoadClosures' => $this->afterLoadClosures,
            'constants' => $this->constants,
            'cookieJar' => $this->cookieJar,
            'domain' => $this->domain ?: 'localhost',
            'preloadClosures' => $this->preloadClosures,
            'preloadFilters' => $this->preloadFilters,
            'presetGlobalVars' => $this->presetGlobalVars ?? [],
            'presetLocalVars' => $this->presetLocalVars ?? [],
            'redirectFiles' => $this->redirectFiles ?? [],
            'requestUri' => $this->requestUri,
            'requestVars' => $this->requestVars ?? [],
            'targetFile' => $this->targetFile,
        ];
    }

    /**
     * @param array{
     *     afterLoadClosures?: array<Closure>,
     *     constants?: array<string,bool|int|string|float>,
     *     cookieJar?: array<string,string>,
     *     domain?: string,
     *     preloadClosures?: array<Closure>,
     *     preloadFilters?: array<array{string, (callable(): mixed)|string, int, int}>,
     *     presetGlobalVars?: array<string,mixed>,
     *     presetLocalVars?: array<string,mixed>,
     *     redirectFiles?: array<string,string>,
     *     requestUri: string,
     *     requestVars?: array<string,int|string|float|bool>,
     *     targetFile: string
     * } $data
     *
     * @throws FileRequestException
     */
    public function __unserialize(array $data): void
    {
        $this->afterLoadClosures = $data['afterLoadClosures'] ?? [];
        $this->constants = $data['constants'] ?? [];
        $this->cookieJar = $data['cookieJar'] ?? [];
        $this->domain = $data['domain'] ?? 'localhost';
        $this->preloadClosures = $data['preloadClosures'] ?? [];
        $this->preloadFilters = $data['preloadFilters'] ?? [];
        $this->presetGlobalVars = $data['presetGlobalVars'] ?? [];
        $this->presetLocalVars = $data['presetLocalVars'] ?? [];
        $this->redirectFiles = $data['redirectFiles'] ?? [];
        $this->requestUri = $data['requestUri'];
        $this->requestVars = $data['requestVars'] ?? [];
        $this->targetFile = $data['targetFile'];

        if (!$this->targetFile) {
            throw new FileRequestException('No target file specified.');
        }
    }

    public function setServerVar(string $key, string $value): FileRequest
    {
        $this->serverVars[$key] = $value;
        return $this;
    }

    public function addPreloadClosure(Closure $preloadClosure): FileRequest
    {
        $this->preloadClosures[] = $preloadClosure;

        return $this;
    }

    public function addAfterLoadClosure(Closure $afterLoadClosure): FileRequest
    {
        $this->afterLoadClosures[] = $afterLoadClosure;

        return $this;
    }

    /**
     * @param int|string|float|bool $value
     */
    public function defineConstant(string $constant, $value): FileRequest
    {
        $this->constants[$constant] = $value;

        return $this;
    }

    /**
     * @param bool|int|float|string $value
     */
    public function setConstant(string $constant, $value): FileRequest
    {
        $this->constants[$constant] = $value;

        return $this;
    }

    public function setPreloadFilter(
        string $hookName,
        string $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): FileRequest {
        $this->preloadFilters[] = [$hookName, $callback, $priority, $acceptedArgs];

        return $this;
    }

    public function blockHttpRequests(): FileRequest
    {
        // Do not send mails.
        $this->addPreloadClosure(static function () {
            require_once dirname(__DIR__, 3) . '/includes/pluggables/function-wp-mail.php';
        });

        // Do not trigger external and internal requests.
        $this->setPreloadFilter('block_local_requests', '__return_true');

        return $this;
    }

    public function setTargetFile(string $targetFile): FileRequest
    {
        $this->targetFile = $targetFile;
        return $this;
    }

    /**
     * @param array<string, string> $redirectFiles
     */
    public function setRedirectFiles(array $redirectFiles): FileRequest
    {
        $this->redirectFiles = $redirectFiles;

        return $this;
    }

    /**
     * @param array<string,mixed> $presetGlobalVars
     */
    public function addPresetGlobalVars(array $presetGlobalVars): FileRequest
    {
        $this->presetGlobalVars = array_replace($this->presetGlobalVars, $presetGlobalVars);

        return $this;
    }

    public function runInFastMode(string $wpRootDir): FileRequest
    {
        $wpConfigFilePath = $this->findWpConfigFilePath($wpRootDir);

        if (!$wpConfigFilePath) {
            return $this;
        }

        $wpConfigFile = new WPConfigFile($wpRootDir, $wpConfigFilePath);
        $preloadConstants = [];

        if (!($wpConfigFile->getConstant('WP_HTTP_BLOCK_EXTERNAL') !== null)) {
            $preloadConstants['WP_HTTP_BLOCK_EXTERNAL'] = true;
        }

        if (!($wpConfigFile->getConstant('DISABLE_WP_CRON') !== null)) {
            $preloadConstants['DISABLE_WP_CRON'] = true;
        }

        if (!count($preloadConstants)) {
            return $this;
        }

        $this->addPreloadClosure(static function () use ($preloadConstants) {
            foreach ($preloadConstants as $key => $value) {
                defined($key) || define($key, $value);
            }
        });

        return $this;
    }
}
