<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\Db as DbUtil;
use PDO;

class Db
{
    private ?PDO $pdo = null;
    private string $dbHost;
    private string $dbName;
    private string $dbPassword;
    private string $dbUser;
    private string $dsn;
    private string $tablePrefix;

    public function __construct(
        string $dbName,
        string $dbUser,
        string $dbPassword,
        string $dbHost,
        string $tablePrefix = 'wp_'
    ) {
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbHost = $dbHost;
        $this->tablePrefix = $tablePrefix;
        $this->dsn = DbUtil::dbDsnString(DbUtil::dbDsnMap($dbHost));
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getDbUser(): string
    {
        return $this->dbUser;
    }

    public function getDbPassword(): string
    {
        return $this->dbPassword;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function pdo(): PDO
    {
        if (!$this->pdo instanceof PDO) {
            $this->pdo = new PDO($this->dsn, $this->dbUser, $this->dbPassword);
        }

        return $this->pdo;
    }

    public function create(): void
    {
        if ($this->pdo()->query('CREATE DATABASE IF NOT EXISTS ' . $this->dbName) === false) {
            throw new DbException('Could not create database ' . $this->dbName);
        }
    }

    public function drop(): void
    {
        if ($this->pdo()->query('DROP DATABASE IF EXISTS ' . $this->dbName) === false) {
            throw new DbException('Could not drop database ' . $this->dbName);
        }
    }
}
