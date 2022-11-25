<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\Db as DbUtil;
use lucatume\WPBrowser\Utils\Serializer;
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
    private array $optionsCache = [];

    /**
     * @throws DbException
     */
    public function __construct(
        string $dbName,
        string $dbUser,
        string $dbPassword,
        string $dbHost,
        string $tablePrefix = 'wp_'
    ) {
        if (!preg_match('/^[a-zA-Z][\w_]{0,23}$/', $dbName) || str_starts_with('ii', $dbName)) {
            throw new DbException(
                "Invalid database name: $dbName",
                DbException::INVALID_DB_NAME
            );
        }

        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbHost = $dbHost;
        $this->tablePrefix = $tablePrefix;
        $this->dsn = DbUtil::dbDsnString(DbUtil::dbDsnMap($dbHost));
    }

    /**
     * @throws DbException|WpConfigFileException
     */
    public static function fromWpConfigFile(WPConfigFile $wpConfigFile): self
    {
        $dbName = $wpConfigFile->getConstantOrThrow('DB_NAME');
        $dbUser = $wpConfigFile->getConstantOrThrow('DB_USER');
        $dbPassword = $wpConfigFile->getConstantOrThrow('DB_PASSWORD');
        $dbHost = $wpConfigFile->getConstantOrThrow('DB_HOST');
        $tablePrefix = $wpConfigFile->getVariableOrThrow('table_prefix');

        return new self($dbName, $dbUser, $dbPassword, $dbHost, $tablePrefix);
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

    /**
     * @throws DbException
     */
    public function pdo(): PDO
    {
        if (!$this->pdo instanceof PDO) {
            try {
                $this->pdo = new PDO($this->dsn, $this->dbUser, $this->dbPassword);
            } catch (\PDOException $e) {
                throw new DbException(
                    "Could not connect to the database: {$e->getMessage()}",
                    DbException::INVALID_CONNECTION_PARAMETERS
                );
            }

            if ($this->exists()) {
                $this->useDb($this->dbName);
            }
        }

        return $this->pdo;
    }

    /**
     * @throws DbException
     */
    public function create(): self
    {
        if ($this->pdo()->query('CREATE DATABASE IF NOT EXISTS ' . $this->dbName) === false) {
            throw new DbException(
                'Could not create database ' . $this->dbName . ':' . json_encode($this->pdo->errorInfo()),
                DbException::FAILED_QUERY
            );
        }
        $this->useDb($this->dbName);

        return $this;
    }

    /**
     * @throws DbException
     */
    public function drop(): void
    {
        if ($this->pdo()->query('DROP DATABASE IF EXISTS ' . $this->dbName) === false) {
            throw new DbException(
                'Could not drop database ' . $this->dbName . ': ' . json_encode($this->pdo->errorInfo()),
                DbException::FAILED_QUERY
            );
        }
    }

    public function exists(): bool
    {
        $result = $this->pdo()->query("SHOW DATABASES LIKE '$this->dbName'", PDO::FETCH_COLUMN, 0);
        $matches = iterator_to_array($result, false);
        return !empty($matches);
    }

    public function getOption(string $optionName, mixed $default = null): mixed
    {
        if (!isset($this->optionsCache[$optionName])) {
            $statement = $this->pdo()->prepare("SELECT option_value FROM {$this->tablePrefix}options WHERE option_name = :option_name");
            $executed = $statement->execute(['option_name' => $optionName]);
            $optionValue = $executed ? $statement->fetchColumn() : null;
            $this->optionsCache[$optionName] = Serializer::maybeUnserialize($optionValue);
        }

        return $this->optionsCache[$optionName] ?? $default;
    }

    /**
     * @throws DbException
     */
    private function useDb(string $dbName): void
    {
        if ($this->pdo->query('USE ' . $dbName) === false) {
            throw new DbException(
                'Could not use database ' . $this->dbName . ': ' . json_encode($this->pdo->errorInfo()),
                DbException::FAILED_QUERY
            );
        }
    }
}
