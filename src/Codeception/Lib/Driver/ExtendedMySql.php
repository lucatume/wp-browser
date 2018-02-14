<?php

namespace Codeception\Lib\Driver;

/**
 * An extension of Codeception MySql driver.
 */
class ExtendedMySql extends MySql
{
    /**
     * Returns the statement to insert or update an entry in the database.
     *
     * @param string $tableName The table name to use
     * @param array  $data      Key/value pairs of the data to insert/update.
     *
     * @return string The query string ready to be prepared.
     */
    public function insertOrUpdate($tableName, array $data)
    {
        $this->executeQuery("SET SESSION sql_mode='ALLOW_INVALID_DATES'", []);

        $columns = array_map(
            [$this, 'getQuotedName'],
            array_keys($data)
        );
        $updateAssignments = $this->getAssignmentsFor($data);

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $this->getQuotedName($tableName),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($data), '?')),
            $updateAssignments
        );
    }

    protected function getAssignmentsFor(array $data, $glue = ', ')
    {
        $assignments = [];
        foreach ($data as $key => $value) {
            $assignments[] = sprintf('%s=\'%s\'', $key, $value);
        }
        $assignments = implode($glue, $assignments);

        return $assignments;
    }
}
