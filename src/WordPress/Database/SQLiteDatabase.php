<?php

namespace lucatume\WPBrowser\WordPress\Database;

use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use PDO;
use PDOException;
use PDOStatement;
use SQLite3;

class SQLiteDatabase implements DatabaseInterface
{
    public const ERR_DIR_NOT_FOUND = 1;
    public const ERR_DROP_DB_FAILED = 2;
    public const ERR_CONST_NOT_FOUND = 3;
    private string $directory;
    private string $file;
    private string $dbPathname;
    private ?PDO $pdo = null;

    /**
     * @throws DbException
     */
    public function __construct(string $directory, string $file = 'db.sqlite', private string $tablePrefix = 'wp_')
    {
        if (!(is_dir($directory) && is_writable($directory))) {
            throw new DbException(
                "Directory $directory does not exist or is not writable.",
                self::ERR_DIR_NOT_FOUND
            );
        }
        $this->directory = rtrim($directory, '\\/');
        $this->file = ltrim($file, '\\/');
        $this->dbPathname = $this->directory . DIRECTORY_SEPARATOR . $this->file;
    }

    public static function fromWpConfigFile(WPConfigFile $wpConfigFile): self
    {
        $dbDir = $wpConfigFile->getConstant('DB_DIR');
        $dbFile = $wpConfigFile->getConstant('DB_FILE');

        if (empty($dbDir) || empty($dbFile) || !(is_string($dbDir) && is_string($dbFile))) {
            throw new DbException(
                "Could not find DB_DIR or DB_FILE constants in the wp-config.php file.",
                self::ERR_CONST_NOT_FOUND
            );
        }

        $tablePrefix = $wpConfigFile->getVar('TABLE_PREFIX');
        $tablePrefix = $tablePrefix ?? 'wp_';

        if (!is_string($tablePrefix)) {
            throw new DbException(
                "Could not find `table_prefix` variable in the wp-config.php file.",
                DbException::TABLE_PREFIX_NOT_FOUND
            );
        }

        return new self($dbDir, $dbFile, $tablePrefix);
    }

    public function getDbName(): string
    {
        return $this->file;
    }

    public function getDbUser(): string
    {
        return '';
    }

    public function getDbPassword(): string
    {
        return '';
    }

    public function getDbHost(): string
    {
        return '';
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * @throws PDOException if the attempt to connect to the requested database fails.
     */
    public function getPDO(): PDO
    {
        $this->setEnvVars();
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $this->pdo = new PDO("sqlite:$this->dbPathname");

        return $this->pdo;
    }

    public function create(): DatabaseInterface
    {
        $this->getPDO();
        return $this;
    }

    /**
     * @throws DbException
     */
    public function drop(): DatabaseInterface
    {
        if (!unlink($this->dbPathname)) {
            throw new DbException(
                "Could not delete database file $this->dbPathname.",
                self::ERR_DROP_DB_FAILED
            );
        }
        return $this;
    }

    public function exists(): bool
    {
        return file_exists($this->dbPathname);
    }

    /**
     * @throws DbException
     */
    public function useDb(string $dbName): DatabaseInterface
    {
        throw new DbException('SQLite databases do not support the USE statement.');
    }

    /**
     * @throws DbException
     */
    public function query(string $query, array $params = []): int
    {
        $stmt = $this->getPDO()->prepare($query);

        if (!$stmt instanceof PDOStatement) {
            throw new DbException(
                "Could not prepare `{$query}`: " . $this->getPDO()->errorInfo()[2],
                DbException::PREPARE_FAILED
            );
        }

        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function getDsn(): string
    {
        return "sqlite:$this->dbPathname";
    }

    public function getDbUrl(): string
    {
        return 'sqlite://' . $this->dbPathname;
    }

    /**
     * @throws DbException
     */
    public function updateOption(string $name, mixed $value): int
    {
        $query = "INSERT OR REPLACE INTO {$this->tablePrefix}options (option_name, option_value, autoload)"
            . " VALUES (:name, :value, :autoload)";
        $stmt = $this->getPDO()->prepare($query);

        if (!$stmt instanceof PDOStatement) {
            throw new DbException(
                "Could not prepare `{$query}`: " . $this->getPDO()->errorInfo()[2],
                DbException::PREPARE_FAILED
            );
        }

        $stmt->execute([':name' => $name, ':value' => $value, ':autoload' => 'yes']);
        return $stmt->rowCount();
    }

    /**
     * @throws DbException
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        $query = "SELECT option_value FROM {$this->tablePrefix}options WHERE option_name = :name";
        $stmt = $this->getPDO()->prepare($query);

        if (!$stmt instanceof PDOStatement) {
            throw new DbException(
                "Could not prepare `{$query}`: " . $this->getPDO()->errorInfo()[2],
                DbException::PREPARE_FAILED
            );
        }

        $stmt->execute([':name' => $name]);
        $value = $stmt->fetchColumn();
        if ($value === false) {
            return $default;
        }
        return $value;
    }

    public function getDbDir(): string
    {
        return $this->directory;
    }

    public function getDbFile(): string
    {
        return $this->file;
    }

    /**
     * @throws DbException
     */
    public function import(string $dumpFilePath): int
    {
        if (!is_file($dumpFilePath)) {
            throw new DbException("Dump file $dumpFilePath does not exist.", DbException::DUMP_FILE_NOT_EXIST);
        }
        $dump = file_get_contents($dumpFilePath);
        if ($dump === false) {
            throw new DbException("Could not read dump file $dumpFilePath.", DbException::DUMP_FILE_NOT_READABLE);
        }

        // Break the queries down following the dump pattern of a semicolon followed by a newline.
        $queries = preg_split('/;\s*[\r\n]+/', $dump);

        if ($queries === false) {
            throw new DbException("Could not split dump file $dumpFilePath.", DbException::DUMP_FILE_NOT_READABLE);
        }

        $modified = 0;
        foreach ($queries as $query) {
            $query = trim($query);
            if (empty($query)) {
                continue;
            }
            $modified += $this->query($query);
        }
        return $modified;
    }

    /**
     * @see https://github.com/ephestione/php-sqlite-dump/blob/master/sqlite_dump.php
     *
     * @throws DbException
     */
    public function dump(string $dumpFilePath): void
    {
        $db = new SQLite3($this->dbPathname);
        $db->busyTimeout(5000);

        $sql = "PRAGMA foreign_keys = OFF;\n\n";

        $tables = $db->query("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%';");

        if ($tables === false) {
            throw new DbException("Could not read tables from database.", DbException::FAILED_DUMP);
        }

        while ($table = $tables->fetchArray(SQLITE3_NUM)) {
            $tableSql = $db->querySingle("SELECT sql FROM sqlite_master WHERE name = '$table[0]'");

            if (empty($tableSql) || !is_string($tableSql)) {
                throw new DbException("Could not read table $table[0] from database.", DbException::FAILED_DUMP);
            }

            $sql .= "DROP TABLE IF EXISTS $table[0];\n" . $tableSql . ";\n\n";
            $rows = $db->query("SELECT * FROM $table[0]");

            if ($rows === false) {
                throw new DbException("Could not read rows from table $table[0].", DbException::FAILED_DUMP);
            }

            $row = $rows->fetchArray(SQLITE3_ASSOC);

            if ($row === false) {
                continue;
            }

            $sql .= "INSERT INTO {$table[0]} (";
            $columns = $db->query("PRAGMA table_info($table[0])");

            if ($columns === false) {
                throw new DbException("Could not read columns from table $table[0].", DbException::FAILED_DUMP);
            }

            $fieldNames = [];

            while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
                $fieldNames[] = "'" . $column["name"] . "'";
            }

            $sql .= implode(",", $fieldNames) . ") VALUES";

            do {
                foreach ($row as $k => $v) {
                    $row[$k] = "'" . str_replace("\n", "' || char(10) || '", SQLite3::escapeString($v)) . "'";
                }
                $sql .= "\n(" . implode(",", $row) . "),";
            } while ($row = $rows->fetchArray(SQLITE3_ASSOC));

            $sql = rtrim($sql, ",") . ";\n\n";
        }

        $sql .= "PRAGMA foreign_keys = ON\n";

        if (file_put_contents($dumpFilePath, $sql) === false) {
            throw new DbException("Could not write dump file $dumpFilePath.", DbException::FAILED_DUMP);
        }
    }

    public function setEnvVars(): void
    {
        putenv('DATABASE_TYPE=sqlite');
        $_ENV['DATABASE_TYPE'] = 'sqlite';
        putenv('DB_ENGINE=sqlite');
        $_ENV['DB_ENGINE'] = 'sqlite';
        putenv('DB_DIR=' . $this->getDbDir());
        $_ENV['DB_DIR'] = $this->getDbDir();
        putenv('DB_FILE=' . $this->getDbFile());
        $_ENV['DB_FILE'] = $this->getDbFile();
    }
}
