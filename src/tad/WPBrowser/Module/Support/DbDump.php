<?php

namespace tad\WPBrowser\Module\Support;

use Symfony\Component\Yaml\Exception\DumpException;
use tad\WPBrowser\Filesystem\Utils;
use function tad\WPBrowser\pregErrorMessage;
use function tad\WPBrowser\untrailslashit;

class DbDump
{
    /**
     * A static array cache implementation to store database dumps replaced in the context of this request.
     *
     * @var array<string,string>
     */
    protected static $urlReplacementCache = [];

    /**
     * @var string|null
     */
    protected $tablePrefix;

    /**
     * @var string|null
     */
    protected $url;
    /**
     * The site original URL, the URL of the site on single site installations, or the URL of the first site on
     * multi-site installations.
     *
     * @var string|null
     */
    protected $originalUrl;

    /**
     * Replaces the WordPress domains in an array of SQL dump string.
     *
     * @param array<string> $sql The input SQL dump array.
     *
     * @return array<string> The modified SQL array.
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
     * @param array<string> $sql The input SQL dump array.
     *
     * @return array<string> The modified SQL array.
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
        $cacheKey = md5($sql) . '-' . md5($this->url) . '-single' ;

        if (isset(static::$urlReplacementCache[$cacheKey])) {
            return static::$urlReplacementCache[$cacheKey];
        }

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
        $originalFrags = array_intersect_key($originalFrags, array_flip(['host', 'path','port']));
        if (!empty($originalFrags['port'])) {
            $originalFrags['port'] = ':' . $originalFrags['port'];
        }
        $originalHostAndPath = rtrim(
            implode('', array_merge(['host' => '', 'path' => '', 'port' => ''], $originalFrags)),
            '/'
        );
        $replaceScheme = parse_url($this->url, PHP_URL_SCHEME);
        $replaceHost = parse_url($this->url, PHP_URL_HOST);

        if ($originalHostAndPath === $replaceHost) {
            return $sql;
        }

        $urlPattern = '~' .
            '(?<scheme>https?)://' .
            '(?<subdomain>[A-z0-9_-]+\\.)*' .
            preg_quote($originalHostAndPath, '~') . '(?!\\.)' .
            '(?<path>/+[A-z0-9/_-]*)*' .
            '~u';
        $replacement = $replaceScheme . '://$2' . $replaceHost . '$3';

        $sql = preg_replace($urlPattern, $replacement, $sql);

        preg_match($urlPattern, $sql, $m);

        if ($sql === null) {
            throw new DumpException(
                'There was an error while trying to replace the URL in the dump file: ' .
                pregErrorMessage(preg_last_error()) .
                "\n\n" .
                'Either manually replace it and set the "urlReplacement" module parameter to "false" or check the ' .
                'dump file integrity.'
            );
        }

        static::$urlReplacementCache[$cacheKey] = $sql;

        codecept_debug('Dump file URL [' . $originalHostAndPath . '] replaced with [' . $replaceHost . ']');

        return $sql;
    }

    /**
     * Replaces the site domain in the multisite tables of a SQL dump.
     *
     * @param string $sql   The SQL code to apply the replacements to.
     * @param bool   $debug Whether to debug the replacement operation or not.
     *
     * @return string The SQL code, with the URL replaced in it.
     */
    public function replaceSiteDomainInMultisiteSqlString($sql, $debug = false)
    {
        $cacheKey = md5($sql) . '-' . md5($this->url) . '-multisite' ;

        if (isset(static::$urlReplacementCache[$cacheKey])) {
            return static::$urlReplacementCache[$cacheKey];
        }

        if ($this->originalUrl === null) {
            $this->originalUrl = $this->getOriginalUrlFromSqlString($sql);
        }

        $tables = [
            'blogs' => "VALUES\\s+\\(\\d+,\\s*\\d+,\\s*)'(.*)',/uiU",
            'site'  => "VALUES\\s+\\(\\d+,\\s*)'(.*)',/uiU",
        ];

        $thisSiteUrl = preg_replace('~https?:\\/\\/~', '', $this->url);

        foreach ($tables as $table => $pattern) {
            $currentTable = $this->tablePrefix . $table;
            $matches      = [];
            $fullPattern = "/(INSERT\\s+INTO\\s+`{$currentTable}`\\s+{$pattern}";
            preg_match($fullPattern, $sql, $matches);

            if (empty($matches) || empty($matches[1])) {
                if ($debug) {
                    codecept_debug('Dump file does not contain a table INSERT instruction for table ['.
                     $table . '], not replacing.');
                }
                continue;
            }

            $dumpSiteUrl = $matches[2];
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
                codecept_debug('Dump file URL [' . $dumpSiteUrl . '] replaced with [' . $thisSiteUrl . '].');
            }

            $sql = preg_replace(
                '#([\'"])([A-z0-9_-]+\\.)*' . preg_quote($dumpSiteUrl, '#') . '(/[^\'"])*([\'"])#',
                '$1$2' . $thisSiteUrl . '$3$4',
                $sql
            );
        }

        static::$urlReplacementCache[$cacheKey] = $sql;

        return $sql;
    }

    /**
     * DbDump constructor.
     *
     * @param string|null $url The URL to replace, `null` to have it inferred.
     * @param string|null $tablePrefix The table prefix to use.
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
     * Sets the table prefix.
     *
     * @param string $tablePrefix The table prefix to use.
     *
     * @return void
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Returns the current dump URL.
     *
     * @return string The current dump URL.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the URL that should replace the original URL in the SQL dump file.
     *
     * @param string $url The URL that should replace the original URL in the SQL dump file.
     *
     * @return void
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
     * @param string|null $originalUrl The site URL that should be replaced in the dump, or `null` to unset the
     *                                 property.
     *
     * @return void
     */
    public function setOriginalUrl($originalUrl = null)
    {
        if ($originalUrl === null) {
            $this->originalUrl = null;

            return;
        }

        $originalUrl = trim($originalUrl);
        $parsed      = parse_url($originalUrl);

        if ($parsed === false || ! is_array($parsed)) {
            return;
        }

        $originalUrlFrags = array_replace([ 'scheme' => 'http', 'host' => '', 'path' => '' ], $parsed);
        $originalUrl      = $originalUrlFrags['scheme'] . '://' . $originalUrlFrags['host'] . $originalUrlFrags['path'];

        $this->originalUrl = untrailslashit($originalUrl);
    }
}
