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
    /**
     * @var string
     */
    private $dbUser;
    /**
     * @var string
     */
    private $dbPassword;
    /**
     * @var string
     */
    private $dbHost;
    /**
     * @var string
     */
    private $tablePrefix = 'wp_';
    /**
     * @var \PDO|null
     */
    private $pdo;
    /**
     * @var string
     */
    private $dbName;
    /**
     * @var string
     */
    private $dsnWithoutDbName;
    /**
     * @var string
     */
    private $dsn;
    /**
     * @var string
     */
    private $dbUrl;

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
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbHost = $dbHost;
        $this->tablePrefix = $tablePrefix;
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
     * @return $this
     */
    public static function fromWpConfigFile(WPConfigFile $wpConfigFile): \lucatume\WPBrowser\WordPress\Database\DatabaseInterface
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
     * @return $this
     */
    public function create(): \lucatume\WPBrowser\WordPress\Database\DatabaseInterface
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
     * @return $this
     */
    public function drop(): \lucatume\WPBrowser\WordPress\Database\DatabaseInterface
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
        $query = "SHOW DATABASES WHERE `Database` = '{$this->dbName}'";
        $result = $this->getPDO()->query($query, PDO::FETCH_COLUMN, 0);

        if ($result === false) {
            return false;
        }

        $matches = iterator_to_array($result, false);
        return !empty($matches);
    }

    /**
     * @throws DbException
     * @return $this
     */
    public function useDb(string $dbName): \lucatume\WPBrowser\WordPress\Database\DatabaseInterface
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
     * @param mixed $value
     */
    public function updateOption(string $name, $value): int
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
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $name, $default = null)
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
     * @param mixed $default
     * @return mixed
     */
    private function fetchFirst(string $query, array $parameters = [], $default = null)
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

            if ($ingestingMultilineComment && substr_compare($read, '*/', -strlen('*/')) !== 0) {
                continue;
            }

            // MySQL `-- ` comment.
            if ($read === '--' || strncmp($read, '-- ', strlen('-- ')) === 0) {
                continue;
            }

            // MySQL multi-line comment.
            if (strncmp($read, '/*', strlen('/*')) === 0) {
                // Might be closed on the same line.
                $ingestingMultilineComment = substr_compare($read, '*/', -strlen('*/')) === 0;
                continue;
            }

            $line .= $read;
            if (substr_compare($line, ';', -strlen(';')) === 0) {
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
            $dump = new class($this->dsn, $this->dbUser, $this->dbPassword) extends Mysqldump {
                public function start($filename = '')
                {
                    $this->dumpSettings['add-drop-table'] = true;
                    $this->dumpSettings['add-drop-database'] = true;
                    return parent::start($filename);
                }
            };
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
