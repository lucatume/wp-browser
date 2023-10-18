<?php

namespace lucatume\WPBrowser\WordPress\Database;

use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use lucatume\WPBrowser\Utils\Db as DbUtil;
use lucatume\WPBrowser\Utils\Serializer;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use PDO;
use PDOException;

class MysqlDatabase implements DatabaseInterface
{
    private ?PDO $pdo = null;
    private string $dbName;
    private string $dsnWithoutDbName;
    private string $dsn;
    private string $dbUrl;

    /**
     * @throws DbException
     */
    public function __construct(
        string $dbName,
        private string $dbUser,
        private string $dbPassword,
        private string $dbHost,
        private string $tablePrefix = 'wp_'
    ) {
        if (!preg_match('/^[a-zA-Z][\w_-]{0,64}$/', $dbName) || str_starts_with('ii', $dbName)) {
            throw new DbException(
                "Invalid database name: $dbName",
                DbException::INVALID_DB_NAME
            );
        }

        $this->dbName = $dbName;
        $this->dsnWithoutDbName = DbUtil::dbDsnString(DbUtil::dbDsnMap($dbHost));
        $this->dsn = $this->dsnWithoutDbName . ';dbname=' . $dbName;
        $this->dbUrl = sprintf(
            'mysql://%s:%s@%s/%s',
            $dbUser,
            $dbPassword,
            $dbHost,
            $dbName
        );
    }

    /**
     * @throws DbException|WpConfigFileException
     */
    public static function fromWpConfigFile(WPConfigFile $wpConfigFile): self
    {
        $dbName = (string)$wpConfigFile->getConstantOrThrow('DB_NAME');
        $dbUser = (string)$wpConfigFile->getConstantOrThrow('DB_USER');
        $dbPassword = (string)$wpConfigFile->getConstantOrThrow('DB_PASSWORD');
        $dbHost = (string)$wpConfigFile->getConstantOrThrow('DB_HOST');
        $tablePrefix = $wpConfigFile->getVariableOrThrow('table_prefix');

        if (!is_string($tablePrefix)) {
            throw new WpConfigFileException(
                'The table prefix is not a string.',
                WpConfigFileException::TABLE_PREFIX_NOT_STRING
            );
        }

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
    public function getPDO(): PDO
    {
        $this->setEnvVars();
        if (!$this->pdo instanceof PDO) {
            try {
                $this->pdo = new PDO($this->dsnWithoutDbName, $this->dbUser, $this->dbPassword);
            } catch (PDOException $e) {
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
        $pdo = $this->getPDO();
        if ($pdo->query("CREATE DATABASE IF NOT EXISTS `{$this->dbName}`") === false) {
            throw new DbException(
                'Could not create database ' . $this->dbName . ':' . json_encode($pdo->errorInfo()),
                DbException::FAILED_QUERY
            );
        }
        $this->useDb($this->dbName);

        return $this;
    }

    /**
     * @throws DbException
     */
    public function drop(): self
    {
        $pdo = $this->getPDO();
        if ($pdo->query("DROP DATABASE IF EXISTS `{$this->dbName}`") === false) {
            throw new DbException(
                'Could not drop database ' . $this->dbName . ': ' . json_encode($pdo->errorInfo()),
                DbException::FAILED_QUERY
            );
        }

        return $this;
    }

    public function exists(): bool
    {
        $result = $this->getPDO()->query("SHOW DATABASES LIKE '{$this->dbName}'", PDO::FETCH_COLUMN, 0);

        if ($result === false) {
            return false;
        }

        $matches = iterator_to_array($result, false);
        return !empty($matches);
    }

    /**
     * @throws DbException
     */
    public function useDb(string $dbName): self
    {
        $pdo = $this->getPDO();
        if ($pdo->query("USE `{$dbName}`") === false) {
            throw new DbException(
                'Could not use database ' . $this->dbName . ': ' . json_encode($pdo->errorInfo()),
                DbException::FAILED_QUERY
            );
        }

        return $this;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws DbException
     */
    public function query(string $query, array $params = []): int
    {
        $statement = $this->getPDO()->prepare($query);
        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }
        $executed = $statement->execute();
        if ($executed === false) {
            throw new DbException(
                'Could not execute query ' . $query . ': ' . json_encode($statement->errorInfo(), JSON_PRETTY_PRINT),
                DbException::FAILED_QUERY
            );
        }
        return $statement->rowCount();
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getDbUrl(): string
    {
        return $this->dbUrl;
    }

    /**
     * @throws DbException
     */
    public function updateOption(string $name, mixed $value): int
    {
        $table = $this->getTablePrefix() . 'options';
        return $this->query(
            "INSERT INTO $table (option_name, option_value) VALUES (:name, :value) "
            . 'ON DUPLICATE KEY UPDATE option_value = :value',
            ['value' => Serializer::maybeSerialize($value), 'name' => $name]
        );
    }

    /**
     * @throws DbException
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        $table = $this->getTablePrefix() . 'options';
        $query = "SELECT option_value FROM $table WHERE option_name = :name";

        return $this->fetchFirst($query, ['name' => $name], $default);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws DbException
     * @throws PDOException
     */
    private function fetchFirst(string $query, array $parameters = [], mixed $default = null): mixed
    {
        $statement = $this->getPDO()->prepare($query);
        $executed = $statement->execute($parameters);
        if ($executed === false) {
            throw new DbException(
                'Could not execute query ' . $query . ': ' . json_encode($statement->errorInfo(), JSON_PRETTY_PRINT),
                DbException::FAILED_QUERY
            );
        }
        $value = $statement->fetchColumn();

        return $value === false ? $default : Serializer::maybeUnserialize($value);
    }

    /**
     * @throws DbException
     */
    public function import(string $dumpFilePath): int
    {
        if (!is_file($dumpFilePath)) {
            throw new DbException("Dump file $dumpFilePath not exist.", DbException::DUMP_FILE_NOT_EXIST);
        }

        $dumpFileHandle = fopen($dumpFilePath, 'rb');

        if (!is_resource($dumpFileHandle)) {
            throw new DbException("Failed to open file $dumpFilePath.", DbException::DUMP_FILE_NOT_READABLE);
        }

        $modifiedByQuery = 0;
        $line = '';
        $ingestingMultilineComment = false;
        $pdo = $this->getPDO();
        if (!$this->exists()) {
            $this->create();
        }

        while (!feof($dumpFileHandle)) {
            $read = fgets($dumpFileHandle);

            if ($read === false) {
                break;
            }

            // Remove trailing new line.
            $read = rtrim($read, "\n\r");

            if (empty($read)) {
                continue;
            }

            if ($ingestingMultilineComment && !str_ends_with($read, '*/')) {
                continue;
            }

            // MySQL `-- ` comment.
            if ($read === '--' || str_starts_with($read, '-- ')) {
                continue;
            }

            // MySQL multi-line comment.
            if (str_starts_with($read, '/*')) {
                // Might be closed on the same line.
                $ingestingMultilineComment = str_ends_with($read, '*/');
                continue;
            }

            $line .= $read;
            if (str_ends_with($line, ';')) {
                try {
                    $modified = $pdo->exec($line);
                    if ($modified === false) {
                        throw new Exception($pdo->errorInfo()[2]);
                    }
                } catch (Exception $e) {
                    fclose($dumpFileHandle);
                    throw new DbException("Failed to execute query: " . $e->getMessage(), DbException::FAILED_QUERY);
                }
                $modifiedByQuery += (int)$modified;
                $line = '';
            }
        }

        fclose($dumpFileHandle);

        return $modifiedByQuery;
    }

    /**
     * @throws DbException
     */
    public function dump(string $dumpFile): void
    {
        try {
            $dump = new Mysqldump($this->dsn, $this->dbUser, $this->dbPassword);
            $dump->start($dumpFile);
        } catch (\Exception $e) {
            throw new  DbException("Failed to dump database: " . $e->getMessage(), DbException::FAILED_DUMP);
        }
    }

    public function setEnvVars(): void
    {
        putenv('DATABASE_TYPE=mysql');
        $_ENV['DATABASE_TYPE'] = 'mysql';
        putenv('DB_ENGINE=mysql');
        $_ENV['DB_ENGINE'] = 'mysql';
        putenv('DB_DIR=');
        $_ENV['DB_DIR'] = '';
        putenv('DB_FILE=');
        $_ENV['DB_FILE'] = '';
    }
}
