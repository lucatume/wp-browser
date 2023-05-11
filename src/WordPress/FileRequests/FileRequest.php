<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use __PHP_Incomplete_Class;
use Closure;
use ErrorException;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\PreloadFilters;
use Serializable;

abstract class FileRequest implements Serializable
{
    /**
     * @var array<string,mixed>
     */
    private array $serverVars = [];
    /**
     * @var array<Closure>
     */
    private array $preloadClosures = [];
    /**
     * @var array<array{string, (callable(): mixed)|string, int, int}>
     */
    private array $preloadFilters = [];
    /**
     * @var array<Closure>
     */
    private array $afterLoadClosures = [];

    /**
     * @param int[]|string[]|float[]|bool[] $requestVars
     * @param array<string,string> $cookieJar
     * @param string[] $presetGlobalVars
     * @param array<string, string> $redirectFiles
     * @param array<string, mixed> $presetLocalVars
     * @param bool[]|int[]|string[]|float[] $constants
     */
    public function __construct(
        private string $domain,
        private string $requestUri,
        private string $targetFile,
        private array $requestVars = [],
        private array $cookieJar = [],
        private array $redirectFiles = [],
        private array $presetGlobalVars = [],
        private array $presetLocalVars = [],
        private array $constants = []
    ) {
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
                throw new FileRequestException(sprintf('Unable to parse query string from request URI: %s',
                    $this->requestUri));
            }

            parse_str($query, $queryArgs);
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

        // Reveal the errors.
        define('WP_DISABLE_FATAL_ERROR_HANDLER', true);

        // Set up the cookie jar.
        foreach ($this->cookieJar as $key => $value) {
            $_COOKIE[$key] = $value;
        }

        foreach ($this->constants as $constant => $value) {
            defined($constant) || define($constant, $value);
        }

        // Cast all errors to exceptions.
        set_error_handler(static function ($errno, $errstr, $errfile, $errline) {
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

    public function serialize(): ?string
    {
        return serialize([
            'requestVars' => $this->requestVars ?? [],
            'presetGlobalVars' => $this->presetGlobalVars ?? [],
            'presetLocalVars' => $this->presetLocalVars ?? [],
            'redirectFiles' => $this->redirectFiles ?? [],
            'domain' => $this->domain ?: 'localhost',
            'requestUri' => $this->requestUri,
            'targetFile' => $this->targetFile,
            'cookieJar' => $this->cookieJar,
            'constants' => $this->constants,
            'preloadClosures' => $this->preloadClosures,
            'preloadFilters' => $this->preloadFilters,
            'afterLoadClosures' => $this->afterLoadClosures
        ]);
    }

    /**
     * @throws FileRequestException
     */
    public function unserialize(string $data)
    {
        $unserializedData = $this->unserializeData($data);

        $this->presetGlobalVars = $unserializedData['presetGlobalVars'] ?? false;
        $this->presetLocalVars = $unserializedData['presetLocalVars'] ?? [];
        $this->redirectFiles = $unserializedData['redirectFiles'] ?? [];
        $this->domain = $unserializedData['domain'] ?? 'localhost';
        $this->requestUri = $unserializedData['requestUri'];
        $this->requestVars = $unserializedData['requestVars'] ?? [];
        $this->targetFile = $unserializedData['targetFile'] ?? false;
        $this->cookieJar = $unserializedData['cookieJar'] ?? false;
        $this->constants = $unserializedData['constants'] ?? [];
        $this->preloadClosures = $unserializedData['preloadClosures'] ?? [];
        $this->preloadFilters = $unserializedData['preloadFilters'] ?? [];
        $this->afterLoadClosures = $unserializedData['afterLoadClosures'] ?? [];

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

    public function defineConstant(string $constant, int|string|float|bool $value): FileRequest
    {
        $this->constants[$constant] = $value;

        return $this;
    }

    /**
     * @param array<class-string> $carry
     * @return array<class-string>
     */
    private function collectIncompleteClasses(mixed $unserializedData, array &$carry = []): array
    {
        if (!is_array($unserializedData)) {
            return [];
        }

        foreach ($unserializedData as $datum) {
            if (is_array($datum)) {
                $carry = array_merge($carry, $this->collectIncompleteClasses($datum, $carry));
                continue;
            }

            if ($datum instanceof __PHP_Incomplete_Class) {
                $serialized = serialize($datum);
                $class = preg_match('/^O:\d+:"([^"]+)"/', $serialized, $matches) ? $matches[1] : null;
                $carry[] = $class;
            }
        }

        return $carry;
    }

    /**
     * @throws FileRequestException
     */
    private function unserializeData(string $data): mixed
    {
        $unserializationErorr = '';
        set_error_handler(static function (int $errno, string $errstr) use (&$unserializationErorr): bool {
            $unserializationErorr = $errstr;
            return true;
        }, E_WARNING);
        $unserializedData = unserialize($data, ['allowed_classes' => true]);
        restore_error_handler();

        if ($unserializationErorr !== '') {
            $message = $unserializationErorr;
            $incompletes = $this->collectIncompleteClasses($unserializedData);

            if (count($incompletes)) {
                $message = 'These classes are not available at unserialize time: ' . implode(', ', $incompletes);
            }

            throw new FileRequestException($message);
        }

        return $unserializedData;
    }

    public function setConstant(string $constant, bool|int|float|string $value): FileRequest
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
        $this->setConstant('WP_HTTP_BLOCK_EXTERNAL', true)
            ->setPreloadFilter('block_local_requests', '__return_true');

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
}
