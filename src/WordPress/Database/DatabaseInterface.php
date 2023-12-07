<?php

namespace lucatume\WPBrowser\WordPress\Database;

use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use PDO;

interface DatabaseInterface
{
    /**
     * @throws DbException|WpConfigFileException
     */
    public static function fromWpConfigFile(WPConfigFile $wpConfigFile): DatabaseInterface;

    public function getDbName(): string;

    public function getDbUser(): string;

    public function getDbPassword(): string;

    public function getDbHost(): string;

    public function getTablePrefix(): string;

    /**
     * @throws DbException
     */
    public function getPDO(): PDO;

    /**
     * @throws DbException
     */
    public function create(): DatabaseInterface;

    /**
     * @throws DbException
     */
    public function drop(): DatabaseInterface;

    public function exists(): bool;

    /**
     * @throws DbException
     */
    public function useDb(string $dbName): DatabaseInterface;

    /**
     * @param array<string, mixed> $params
     *
     * @throws DbException
     */
    public function query(string $query, array $params = []): int;

    public function getDsn(): string;

    public function getDbUrl(): string;

    /**
     * @throws DbException
     * @param mixed $value
     */
    public function updateOption(string $name, $value): int;

    /**
     * @throws DbException
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * @throws DbException
     */
    public function import(string $dumpFilePath): int;

    /**
     * @throws DbException
     */
    public function dump(string $dumpFilePath): void;

    public function setEnvVars(): void;
}
