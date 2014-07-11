<?php

namespace Codeception\Lib\Driver;


class ExtendedMySql extends MySql
{

    public function insertOrUpdate($tableName, array &$data)
    {
        $columns = array_map(
            array($this, 'getQuotedName'),
            array_keys($data)
        );

        $updateAssignments = array();
        foreach ($data as $key => $value) {
            $updateAssignments[] = sprintf('%s="%s"', $key, $value);
        }
        $updateAssignments = implode(', ', $updateAssignments);

        return sprintf(
            "INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s",
            $this->getQuotedName($tableName),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($data), '?')),
            $updateAssignments
        );
    }
}