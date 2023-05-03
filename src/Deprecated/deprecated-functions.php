<?php

namespace tad\WPBrowser {

    use Codeception\Util\Debug;
    use DateTimeImmutable;
    use DateTimeInterface;
    use lucatume\WPBrowser\Events\Dispatcher;
    use lucatume\WPBrowser\Utils\Codeception;
    use lucatume\WPBrowser\Utils\Composer;
    use lucatume\WPBrowser\Utils\CorePHPUnit;
    use lucatume\WPBrowser\Utils\Dates;
    use lucatume\WPBrowser\Utils\Db;
    use lucatume\WPBrowser\Utils\Env;
    use lucatume\WPBrowser\Utils\Filesystem;
    use lucatume\WPBrowser\Deprecated\Map;
    use lucatume\WPBrowser\Utils\Process;
    use lucatume\WPBrowser\Utils\Property;
    use lucatume\WPBrowser\Utils\Strings;
    use lucatume\WPBrowser\Utils\Url;
    use lucatume\WPBrowser\Utils\WP;
    use PHPUnit\Framework\Assert;
    use PHPUnit\Runner\Version;
    use VRia\Utils\NoDiacritic;
    use wpdb;

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::importDumpWithMysqlBin instead.
     */
    function importDumpWithMysqlBin(
        string $dumpFile,
        string $dbName,
        string $dbUser = 'root',
        string $dbPass = 'root',
        string $dbHost = 'localhost'
    ): void {
        Db::importDumpWithMysqlBin($dumpFile, $dbName, $dbUser, $dbPass, $dbHost);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::mysqlBin instead.
     */
    function mysqlBin(): string
    {
        return Db::mysqlBin();
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::db instead.
     */
    function db(string $dsn, string $user, string $pass, string $dbName = null): callable
    {
        return Db::db($dsn, $user, $pass, $dbName);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::dbDsnMap instead.
     */
    function dbDsnMap(string $dbHost): Map
    {
        return Db::dbDsnToMap($dbHost);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::dbCredentials instead.
     */
    function dbCredentials(Map $dsn, string $dbuser, string $dbpass, string $dbname = null): Map
    {
        return Db::dbCredentials($dsn, $dbuser, $dbpass, $dbname);
    }

    /*
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::dbDsnString instead.
     */
    function dbDsnString(Map $dbDsnMap, bool $forDbHost = false): string
    {
        return Db::dbDsnString($dbDsnMap, $forDbHost);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::isDsnString instead.
     */
    function isDsnString(string $string): bool
    {
        return Db::isDsnString($string);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Db::dbDsnToMap instead.
     */
    function dbDsnToMap(string $dsnString): Map
    {
        return Db::dbDsnToMap($dsnString);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Env::envFile instead.
     */
    function envFile($file): Map
    {
        return Env::envFile($file);
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Env::os instead.
     */
    function os(): string
    {
        return Env::os();
    }

    /**
     * @deprecated Use \lucatume\WPBrowser\Utils\Env::loadEnvMap instead.
     */
    function loadEnvMap(Map $map, $overwrite = true): void
    {
        Env::loadEnvMap($map, $overwrite);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::rrmdir` instead.
     */
    function rrmdir(string $src): bool
    {
        return Filesystem::rrmdir($src);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::homeDir` instead.
     */
    function homeDir(string $path = ''): string
    {
        return Filesystem::homeDir($path);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::resolvePath` instead.
     */
    function resolvePath(string $path, string $root = null): bool|string
    {
        return Filesystem::resolvePath($path, $root);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::untrailslashit` instead.
     */
    function untrailslashit(string $path): string
    {
        return Filesystem::untrailslashit($path);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::unleadslashit` instead.
     */
    function unleadslashit(string $path): string
    {
        return Filesystem::unleadslashit($path);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::recurseCopy` instead.
     */
    function recurseCopy(string $source, string $destination): bool
    {
        return Filesystem::recurseCopy($source, $destination);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::rrmdir` instead.
     */
    function recurseRemoveDir(string $target): bool
    {
        return Filesystem::rrmdir($target);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::findHereOrInParentrmdir` instead.
     */
    function findHereOrInParent(string $path, string $root): bool|string
    {
        return Filesystem::findHereOrInParent($path, $root);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::realpathish` instead.
     */
    function realpathish(string $path): bool|string
    {
        return Filesystem::realpath($path);
    }

    /*
     * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::mkdirp` instead.
     */
    function mkdirp(string $pathname, array|string $contents = [], int $mode = 0777): void
    {
        Filesystem::mkdirp($pathname, $contents, $mode);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Dates::immutable` instead.
     */
    function buildDate(DateTimeInterface|int|string $date): DateTimeImmutable
    {
        return Dates::immutable($date);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Events\Dispatcher::addListener` instead.
     */
    function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        Dispatcher::addListener($eventName, $listener, $priority);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Events\Dispatcher::dispatch` instead.
     */
    function dispatch(string $eventName, mixed $origin = null, ?array $context = []): void
    {
        Dispatcher::dispatch($eventName, $origin, $context);
    }


    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Property::setPropertiesForClass` instead.
     */
    function setPropertiesForClass(object $object, string $class, array $props): object
    {
        return Property::setPropertiesForClass($object, $class, $props);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Property::setPrivateProperties` instead.
     */
    function setPrivateProperties(object|string $object, array $props): void
    {
        Property::setPrivateProperties($object, $props);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Property::readPrivate` instead.
     */
    function readPrivateProperty(object|string $object, string $prop): mixed
    {
        return Property::readPrivate($object, $prop);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::normalizeNewLine` instead.
     */
    function normalizeNewLine($str): string
    {
        return Strings::normalizeNewLine($str);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::slug` instead.
     */
    function slug($string, $sep = '-', $let = false): string
    {
        return Strings::slug($string, $sep, $let);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::renderString` instead.
     */
    function renderString($template, array $data = [], array $fnArgs = []): string
    {
        return Strings::renderString($template, $data, $fnArgs);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Url::parseUrl` instead.
     */
    function parseUrl(string $url): array
    {
        return Url::parseUrl($url);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Url::isRegex` instead.
     */
    function isRegex(string $string): bool
    {
        return Strings::isRegex($string);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::andList` instead.
     */
    function andList(array $elements): string|false
    {
        return Strings::andList($elements);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Url::getDomain` instead.
     */
    function urlDomain($fullUrl): string
    {
        return Url::getDomain($fullUrl);
    }

    /**
     * @deprecated Use `Codeception\Util\Debug::isEnabled` instead.
     */
    function isDebug($activate = null): bool
    {
        return Debug::isEnabled();
    }

    /**
     * @deprecated Use `codecept_debug` instead.
     */
    function debug($message): void
    {
        codecept_debug($message);
    }

    /**
     * @deprecated Use `PHPUnit\Framework\Assert:assertTrue` instead.
     */
    function ensure($condition, $message): void
    {
        Assert::assertTrue((bool)$condition);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Composer::vendorDir` instead.
     */
    function vendorDir(string $path = ''): string
    {
        return Composer::vendorDir($path);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\CorePHPUnit::path` instead.
     */
    function includesDir(string $path = ''): string
    {
        return CorePHPUnit::path($path);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Codeception::checkModuleRequirements` instead.
     */
    function requireCodeceptionModules(string $module, array $requiredModules = []): void
    {
        Codeception::checkModuleRequirements($module, $requiredModules);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Codeception::identifySuite` instead.
     */
    function identifySuiteFromTrace(): string
    {
        return Codeception::identifySuite();
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::stripTags` instead.
     */
    function strip_all_tags($string, $removeBreaks = false): string
    {
        return Strings::stripTags($string, $removeBreaks);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::removeAccents` instead.
     */
    function remove_accents(string $string): string
    {
        return Strings::removeAccents($string);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\Strings::sanitizeUsername` instead.
     */
    function sanitize_user(string $username, bool $strict = false): string
    {
        return Strings::sanitizeUsername($username, $strict);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\WP::dropWpTables` instead.
     */
    function dropWpTables(wpdb $wpdb, array $tables = null): array
    {
        return WP::dropWpTables($wpdb, $tables);
    }

    /**
     * @deprecated Use `lucatume\WPBrowser\Utils\WP::emptyWpTables` instead.
     */
    function emptyWpTables(wpdb $wpdb, array $tables = null): array
    {
        return WP::emptyWpTables($wpdb, $tables);
    }

    /**
     * @deprecated Use `\PHPUnit\Runner\Version::id` instead.
     */
    function phpunitVersion(): string
    {
        return Version::id();
    }

    function pregpregErrorMessage(int $pregLastError)
    {

    }
}

