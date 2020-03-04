<?php

namespace tad\WPBrowser\Module\Support;

use Symfony\Component\Yaml\Exception\DumpException;
use tad\WPBrowser\Filesystem\Utils;

class DbDump
{

    /**
     * @var string
     */
    protected $tablePrefix;

    /**
     * @var string
     */
    protected $url;
    /**
     * The site original URL, the URL of the site on single site installations, or the URL of the first site on
     * multi-site installations.
     *
     * @var string
     */
    protected $originalUrl;

    /**
     * Replaces the WordPress domains in an array of SQL dump string.
     *
     * @param array $sql The input SQL dump array.
     *
     * @return array The modified SQL array.
     */
    public function replaceSiteDomainInSqlArray(array $sql)
    {
        if (empty($sql)) {
            return [];
        }

        $delimiter = md5(uniqid('delim', true));
        $joined = implode($delimiter, $sql);
        $replaced = $this->replaceSiteDomainInSqlString($joined);

        return explode($delimiter, $replaced);
    }

    /**
     * Replaces the site domain in the multisite tables of an array of SQL dump strings.
     *
     * @param array $sql The input SQL dump array.
     *
     * @return array The modified SQL array.
     */
    public function replaceSiteDomainInMultisiteSqlArray(array $sql)
    {
        if (empty($sql)) {
            return [];
        }

        $delimiter = md5(uniqid('delim', true));
        $joined = implode($delimiter, $sql);
        $replaced = $this->replaceSiteDomainInMultisiteSqlString($joined);

        return explode($delimiter, $replaced);
    }

    /**
     * Replaces the WordPress domains in a SQL dump string.
     *
     * @param string $sql   The input SQL dump string.
     * @param bool   $debug Whether a debug message should be printed or not.
     *
     * @return string The modified SQL string.
     *
     * @throws DumpException If the original site URL is not set and cannot be parsed from the input SQL string.
     */
    public function replaceSiteDomainInSqlString($sql, $debug = false)
    {
        if ($this->originalUrl === null) {
            $this->originalUrl = $this->getOriginalUrlFromSqlString($sql);
        }

        if ($this->originalUrl === false) {
            throw new DumpException(
                'Could not find, or could not parse, the original site URL; you can set the "originalUrl" ' .
                'parameter in the module configuration to skip this step and fix this error.'
            );
        }

        $originalFrags = parse_url($this->originalUrl);
        $originalFrags = array_intersect_key($originalFrags, array_flip(['host', 'path']));
        $originalHostAndPath = implode('', array_merge(['host' => '', 'path' => ''], $originalFrags));
        $replaceScheme = parse_url($this->url, PHP_URL_SCHEME);
        $replaceHost = parse_url($this->url, PHP_URL_HOST);

        if ($originalHostAndPath === $replaceHost) {
            return $sql;
        }

        $sql = preg_replace(
            '#(?:https?)://(?<subdomain>.*?)' . preg_quote($originalHostAndPath, '#') . '(?<end>$|/|"|\')#uis',
            $replaceScheme . '://$1' . $replaceHost . '$2',
            $sql
        );

        if ($sql === null) {
            throw new DumpException(
                'There was an error while trying to replace the URL in the dump file.' .
                "\n\n" .
                'Either manually replace it and set the "urlReplacement" module parameter to "false" or check the ' .
                'dump file integrity.'
            );
        }

        codecept_debug('Dump file host [' . $originalHostAndPath . '] replaced with [' . $replaceHost . ']');

        return $sql;
    }

    /**
     * Replaces the site domain in the multisite tables of a SQL dump.
     *
     * @param string $sql
     *
     * @return string
     */
    public function replaceSiteDomainInMultisiteSqlString($sql, $debug = false)
    {
        if ($this->originalUrl === null) {
            $this->originalUrl = $this->getOriginalUrlFromSqlString($sql);
        }

        $tables = [
            'blogs' => "VALUES\\s+\\(\\d+,\\s*\\d+,\\s*'(.*)',/uiU",
            'site'  => "VALUES\\s+\\(\\d+,\\s*'(.*)',/uiU",
        ];

        $thisSiteUrl = preg_replace('~https?:\\/\\/~', '', $this->url);

        foreach ($tables as $table => $pattern) {
            $currentTable = $this->tablePrefix . $table;
            $matches      = [];
            preg_match("/INSERT\\s+INTO\\s+`{$currentTable}`\\s+{$pattern}", $sql, $matches);

            if (empty($matches) || empty($matches[1])) {
                if ($debug) {
                    codecept_debug('Dump file does not contain a table INSERT instruction for table ['.
                     $table . '], not replacing.');
                }
                continue;
            }

            $dumpSiteUrl = $matches[1];
            if (empty($dumpSiteUrl)) {
                if ($debug) {
                    codecept_debug('Dump file does not contain dump of [domain] option, not replacing.');
                }
                continue;
            }

            if ($dumpSiteUrl === $thisSiteUrl) {
                if ($debug) {
                    codecept_debug('Dump file domain identical to the one specified in the configuration ['
                                   . $dumpSiteUrl . ']; not replacing');
                }
                continue;
            }

            if ($debug) {
                codecept_debug('Dump file domain [' . $dumpSiteUrl . '] replaced with [' . $thisSiteUrl . '].');
            }

            $sql = str_replace($dumpSiteUrl, $thisSiteUrl, $sql);
        }

        return $sql;
    }

    /**
     * DbDump constructor.
     *
     * @param null $url
     * @param null $tablePrefix
     */
    public function __construct($url = null, $tablePrefix = null)
    {
        $this->url         = $url;
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * @param string $tablePrefix
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Parses a whole SQL file contents to find the URL of the site, or of the first site on multi-site installations.
     *
     * @param string $sql The entire SQL string to parse.
     *
     * @return string|false The first site URL or `false` if the first site URL could not be found.
     */
    public function getOriginalUrlFromSqlString($sql)
    {
        $matches = [];
        $urlPattern = sprintf(
            "/INSERT\\s+INTO\\s+`%soptions`.*'(home|siteurl)'\\s*,\\s*'(?<url>[^']+)'/uis",
            preg_quote($this->tablePrefix)
        );

        preg_match($urlPattern, $sql, $matches);

        return empty($matches['url']) ? false : trim($matches['url']);
    }

    /**
     * Sets the original dump URL, the one that should be replaced in the dump.
     *
     * @param string $originalUrl The site URL that should be replaced in the dump.
     */
    public function setOriginalUrl($originalUrl)
    {
        $originalUrl = trim($originalUrl);
        $originalUrlFrags = parse_url($originalUrl);
        $originalUrlFrags['scheme'] = isset($originalUrlFrags['scheme']) ? $originalUrlFrags['scheme'] : 'http';
        $originalUrlFrags['host'] = isset($originalUrlFrags['host']) ? $originalUrlFrags['host'] . '/' : '';
        $originalUrlFrags['path'] = isset($originalUrlFrags['path']) ? $originalUrlFrags['path'] : '';
        $originalUrl = $originalUrlFrags['scheme'] . '://'
            . $originalUrlFrags['host'] .
            $originalUrlFrags['path'];

        $this->originalUrl = Utils::untrailslashit($originalUrl);
    }
}
