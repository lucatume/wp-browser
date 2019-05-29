<?php
/**
 * Provides methods to check and connect to the WordPress database based on the currently defined constants.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

use tad\WPBrowser\Module\Support\WPHealthcheck;

/**
 * Trait WordPressDatabase
 * @package tad\WPBrowser\Traits
 */
trait WordPressDatabase
{
    /**
     * The current database connection error message.
     *
     * @var string
     */
    protected $dbConnectionError;
    /**
     * The current PDO object, if any.
     *
     * @var \PDO
     */
    protected $pdo;

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
    public function getOption($optionName, $default)
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
     * Returns the WordPress table prefix.
     *
     * The method will check the `$table_prefix` global.
     *
     * @param string $default The WordPress table prefix to default to if not defined.
     *
     * @return string The WordPress table prefix or the default one if not found.
     */
    protected function getTablePrefix($default = 'wp_')
    {
        return isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : $default;
    }

    /**
     * Checks, by attempting it, if the database credentials defined in WordPress constants allow establishing a
     * database connection.
     *
     * @return bool Whether the database credentials defined in WordPress constants allow establishing a database
     *              connection or not.
     */
    public function checkDbConnection()
    {
        if ($this->dbConnectionError !== null) {
            return false;
        }

        if ($this->pdo instanceof \PDO) {
            return true;
        }

        $dbName = $this->constants->constant('DB_NAME', null);
        $dbHost = $this->constants->constant('DB_HOST', null);
        if (!isset($dbName, $dbHost)) {
            $this->dbConnectionError = 'DB_HOST and/or DB_NAME are not set: ' . json_encode([
                    'DB_NAME' => $dbName,
                    'DB_HOST' => $dbHost
                ]);
            return false;
        }
        $dsn = sprintf('mysql:dbname=%s;host=%s', $dbName, $dbHost);
        $dbUser = $this->constants->constant('DB_USER', null);
        $dbPassword = $this->constants->constant('DB_PASSWORD', null);

        if (!isset($dbUser, $dbPassword)) {
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
        return true;
    }
}
