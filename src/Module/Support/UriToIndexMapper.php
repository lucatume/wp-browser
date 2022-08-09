<?php

namespace lucatume\WPBrowser\Module\Support;

class UriToIndexMapper
{
    /**
     * The absolute path to the WordPress installation root folder.
     */
    protected string $root;

    /**
     * A map of pre-resolved URI to paths.
     *
     * @var array<string,string>
     */
    protected array $preResolvedMap = [
        '/wp-admin' => '/wp-admin/index.php',
        '/wp-login.php' => '/wp-login.php',
        '/wp-cron.php' => '/wp-cron.php'
    ];

    /**
     * UriToIndexMapper constructor.
     *
     * @param string $root The absolute path to WordPress root folder.
     */
    public function __construct(string $root)
    {
        $this->root = rtrim((string)$root, '/');
    }

    /**
     * Returns the index file for a URI.
     *
     * @param string $uri The URI to return the index file for.
     *
     * @return string The index file for the URI.
     */
    public function getIndexForUri(string $uri): string
    {
        preg_match('~/?(.*?\\.php)$~', $uri, $matches);
        if (!empty($matches[1])) {
            $uri = '/' . $matches[1];
        }

        $uriPath = parse_url($uri, PHP_URL_PATH);

        if (false === $uriPath) {
            // Try resolving something like `?some-var=foo#frag`.
            $uriPath = '/' . $uri;
        }

        $uriPath = '/' . trim((string)$uriPath, '/');

        if (is_file($this->root . $uriPath)) {
            return $this->root . $uriPath;
        }

        $indexFile = $this->preResolvedMap[$uriPath] ?? '/index.php';

        return $this->root . $indexFile;
    }

    /**
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * Sets the root directory for the URI mapper.
     *
     * @param string $root The root directory for the URI mapper.
     */
    public function setRoot(string $root): void
    {
        $this->root = $root;
    }
}
