<?php

namespace lucatume\WPBrowser\WordPress\Database;

use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use PDO;
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
     * @throws \PDOException if the attempt to connect to the requested database fails.
     */
    public function getPDO(): PDO
    {
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
        $stmt->execute([':name' => $name, ':value' => $value, ':autoload' => 'yes']);
        return $stmt->rowCount();
    }

    /**
     * @throws DbException
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        $stmt = $this->getPDO()->prepare("SELECT option_value FROM {$this->tablePrefix}options WHERE option_name = :name");
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
        $queries = explode(';', $dump);
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

        $sql = "";

        $tables = $db->query("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%';");

        while ($table = $tables->fetchArray(SQLITE3_NUM)) {
            $sql .= $db->querySingle("SELECT sql FROM sqlite_master WHERE name = '{$table[0]}'") . ";\n\n";
            $rows = $db->query("SELECT * FROM {$table[0]}");
            $sql .= "INSERT INTO {$table[0]} (";
            $columns = $db->query("PRAGMA table_info({$table[0]})");
            $fieldnames = array();
            while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
                $fieldnames[] = $column["name"];
            }
            $sql .= implode(",", $fieldnames) . ") VALUES";
            while ($row = $rows->fetchArray(SQLITE3_ASSOC)) {
                foreach ($row as $k => $v) {
                    $row[$k] = "'" . SQLite3::escapeString($v) . "'";
                }
                $sql .= "\n(" . implode(",", $row) . "),";
            }
            $sql = rtrim($sql, ",") . ";\n\n";
        }

        if (file_put_contents($dumpFilePath, $sql) === false) {
            throw new DbException("Could not write dump file $dumpFilePath.", DbException::FAILED_DUMP);
        }
    }
}
