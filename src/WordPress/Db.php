<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Process\ProcessException;
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

    /**
     * @throws InstallationException
     */
    public static function fromRootDir(string $rootDir): self
    {
        try {
            $wpConfig = new WpConfigInclude($rootDir);

            if (
                !(
                    $wpConfig->isDefinedConst('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST')
                    && $wpConfig->issetVar('table_prefix')
                )
            ) {
                throw new InstallationException('Could not find all required database credentials in the wp-config.php file.');
            }

            $dbName = $wpConfig->getConstant('DB_NAME');
            $dbUser = $wpConfig->getConstant('DB_USER');
            $dbPassword = $wpConfig->getConstant('DB_PASSWORD');
            $dbHost = $wpConfig->getConstant('DB_HOST');
            $tablePrefix = $wpConfig->getVariable('table_prefix');

            $db = new self($dbName, $dbUser, $dbPassword, $dbHost, $tablePrefix);

            return $db;
        } catch (ProcessException $e) {
            throw new InstallationException(
                "Parsing of wp-config.php file failed: {$e->getMessage()}", $e->getCode(), $e
            );
        }
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
            if ($this->exists()) {
                $this->pdo->query('USE ' . $this->dbName);
            }
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
}
