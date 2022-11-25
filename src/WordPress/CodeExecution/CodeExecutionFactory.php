<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;

class CodeExecutionFactory
{
    private string $wpRootDir;
    private FileRequestFactory $requestFactory;

    public function __construct(string $wpRootDir, string $domain)
    {
        $this->wpRootDir = rtrim($wpRootDir, '\\/');
        $this->requestFactory = new FileRequestFactory($wpRootDir, $domain);
    }

    public function toCheckIfWpIsInstalled(bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest('/', [], 0)
            ->blockHttpRequests()
            ->setTargetFile($this->wpRootDir . '/wp-load.php')
            ->addAfterLoadClosure(static function () use ($multisite) {
                return is_blog_installed() && (!$multisite || is_multisite());
            });

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
