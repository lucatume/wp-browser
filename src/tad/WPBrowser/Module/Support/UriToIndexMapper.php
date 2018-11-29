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
     * @var array
     */
    protected $map = [
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

    public function getIndexForUri($uri)
    {
        preg_match('~\\/?(.*?\\.php)~', $uri, $matches);
        if (!empty($matches[1])) {
            $uri = '/' . $matches[1];
        }

        if (file_exists($this->root . $uri) && !is_dir($this->root . $uri)) {
            return $this->root . $uri;
        }

        $uri = '/' . trim($uri, '/');
        $indexFile = isset($this->map[$uri]) ? $this->map[$uri] : '/index.php';
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
     * @param string $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }
}
