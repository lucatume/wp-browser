<?php

namespace tad\WPBrowser\Module\Support;

class UriToIndexMapper
{
    /**
     * The absolute path to the WordPress installation root folder.
     *
     * @var string
     */
    protected $root;

    /**
     * A map of pre-resolved URI to paths.
     *
     * @var array<string,string>
     */
    protected $preResolvedMap = [
        '/wp-admin' => '/wp-admin/index.php',
        '/wp-login.php' => '/wp-login.php',
        '/wp-cron.php' => '/wp-cron.php'
    ];

    /**
     * UriToIndexMapper constructor.
     *
     * @param string $root The absolute path to WordPress root folder.
     */
    public function __construct($root)
    {
        $this->root = rtrim($root, '/');
    }

    /**
     * Returns the index file for a URI.
     *
     * @param string $uri The URI to return the index file for.
     *
     * @return string The index file for the URI.
     */
    public function getIndexForUri($uri)
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

        $indexFile = isset($this->preResolvedMap[$uriPath]) ? $this->preResolvedMap[$uriPath] : '/index.php';

        return $this->root . $indexFile;
    }

    /**
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Sets the root directory for the URI mapper.
     *
     * @param string $root The root directory for the URI mapper.
     *
     * @return void
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }
}
