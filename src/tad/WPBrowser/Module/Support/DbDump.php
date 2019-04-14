<?php

namespace tad\WPBrowser\Module\Support;

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
     */
    public function replaceSiteDomainInSqlString($sql, $debug = false)
    {
        $optionsTable = $this->tablePrefix . 'options';

        $matches = [];
        preg_match("/INSERT\\s+INTO\\s+`{$optionsTable}`.*'home'\\s*,\\s*'(.*)',/uiU", $sql, $matches);

        if (empty($matches) || empty($matches[1])) {
            if ($debug) {
                codecept_debug('Dump file does not contain an `options` table INSERT instruction, not replacing');
            }

            return $sql;
        }

        $dumpSiteUrl = $matches[1];

        if (empty($dumpSiteUrl)) {
            if ($debug) {
                codecept_debug('Dump file does not contain dump of `home` option, not replacing.');
            }

            return $sql;
        }

        $thisSiteUrl = $this->url;

        if ($dumpSiteUrl === $thisSiteUrl) {
            if ($debug) {
                codecept_debug('Dump file domain not replaced as identical to the one specified in the configuration.');
            }

            return $sql;
        }

        $sql = str_replace($dumpSiteUrl, $thisSiteUrl, $sql);

        codecept_debug('Dump file domain [' . $dumpSiteUrl . '] replaced with [' . $thisSiteUrl . ']');

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
}
