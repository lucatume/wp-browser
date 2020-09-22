<?php
/**
 * Provides methods to check and connect to the WordPress database based on the currently defined constants.
 *
 * @package tad\WPBrowser\Module\Support
 */

namespace tad\WPBrowser\Module\Support;

use tad\WPBrowser\Environment\Constants;

/**
 * Class WordPressDatabase
 * @package tad\WPBrowser\Module\Support
 */
class WordPressDatabase
{
    /**
     * The current database connection error message.
     *
     * @var string|null
     */
    protected $dbConnectionError;
    /**
     * The current PDO object, if any.
     *
     * @var \PDO
     */
    protected $pdo;
    /**
     * An instance of the constants wrapper/adapter.
     *
     * @var Constants
     */
    protected $constants;

    /**
     * WordPressDatabase constructor.
     */
    public function __construct(Constants $constants)
    {
        $this->constants = $constants;
        $this->checkDbConnection();
    }

    /**
     * Checks, by attempting it, if the database credentials defined in WordPress constants allow establishing a
     * database connection.
     *
     * @param bool $force Whether to retry the connection entirely or use the cached value.
     *
     * @return bool Whether the database credentials defined in WordPress constants allow establishing a database
     *              connection or not.
     */
    public function checkDbConnection($force = false)
    {
        if (!$force && $this->dbConnectionError !== null) {
            return false;
        }

        if (!$force && $this->pdo instanceof \PDO) {
            return true;
        }

        $dbName = $this->constants->constant('DB_NAME', null);
        $dbHost = $this->constants->constant('DB_HOST', null);
        if (!isset($dbName, $dbHost)) {
            $this->dbConnectionError = sprintf(
                'DB_HOST and/or DB_NAME are not set: (DB_NAME: %s, DB_HOST: %s)',
                null !== $dbName ? $dbName : 'null',
                null !== $dbHost ? $dbHost : 'null'
            );
            return false;
        }
        $dsn = sprintf('mysql:dbname=%s;host=%s', $dbName, $dbHost);
        $dbUser = $this->constants->constant('DB_USER', null);
        $dbPassword = $this->constants->constant('DB_PASSWORD', null);

        if (!isset($dbUser)) {
            $this->dbConnectionError = 'DB_USER and/or DB_PASSWORD are not set: ' . json_encode([
                    'DB_USER' => $dbUser,
                    'DB_PASSWORD' => $dbPassword
                ]);
            return false;
        }
        try {
            $this->pdo = new \PDO($dsn, $dbUser, $dbPassword);
        } catch (\PDOException $e) {
            $this->dbConnectionError = $e->getMessage();
            return false;
        }

        $this->dbConnectionError = null;

        return true;
    }

    /**
     * Returns, by using a dedicated connection, the value of an option stored in the database.
     *
     * Differently from the WordPress `get_option` function the method will not take care of unserializing the read
     * option.
     *
     * @param string $optionName The name of the option to return.
     * @param mixed $default The default value to return for the option if not found.
     *
     * @return mixed The option value, not unserialized, if serialized.
     */
    public function getOption($optionName, $default = null)
    {
        if (!$this->checkDbConnection()) {
            return $default;
        }
        $query = $this->pdo->query(
            "SELECT option_value FROM {$this->getTable('options')} WHERE option_name = '{$optionName}'"
        );

        if (false === $query) {
            return $default;
        }

        $value = $query->fetch(\PDO::FETCH_COLUMN);
        return false === $value ? $default : $value;
    }

    /**
     * Returns the name of a table, including the WordPress prefix.
     *
     * @param string $table The name of the table to return, e.g. 'options'.
     * @param int|null $blog_id The ID of the blog to return the table for.
     *
     * @return string The table name, including prefix.
     */
    public function getTable($table, $blog_id = null)
    {
        return (int)$blog_id > 1 ?
            $this->getTablePrefix() . $blog_id . '_' . $table
            : $this->getTablePrefix() . $table;
    }

    /**
     * Returns the WordPress table prefix.
     *
     * The method will check the `$table_prefix` global.
     *
     * @param string $default The WordPress table prefix to default to if not defined.
     *
     * @return string The WordPress table prefix or the default one if not found.
     */
    public function getTablePrefix($default = 'wp_')
    {
        return isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : $default;
    }

    /**
     * Returns the PDO instance used by the class, if any.
     *
     * @return \PDO|null The PDO instance used by the class or `null` if not set.
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Sets the PDO object to use to run the checks.
     *
     * @param \PDO $pdo The PDO object to use to run the checks.
     *
     * @return void
     */
    public function setPDO(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Runs, and returns the result of, a custom query using the PDO connection to the WordPress database.
     *
     * @param string $query The custom query to run.
     *
     * @return false|\PDOStatement The statement result of the query, or `false` if the query fails.
     */
    public function query($query)
    {
        return $this->pdo->query($query);
    }

    /**
     * Returns the current database connection error, if any.
     *
     * @return string|null The current database connection error, if any.
     */
    public function getDbConnectionError()
    {
        return $this->dbConnectionError;
    }
}
