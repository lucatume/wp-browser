<?php

namespace Codeception\Module;


class ExtendedDb extends Db
{
    public function dontHaveInDatabase($table, array $criteria)
    {
        $query = $this->driver->delete($table, $criteria);
        $this->debugSection('Query', $query);

        $sth = $this->driver->getDbh()->prepare($query);
        if (!$sth) {
            $this->fail("Query '$query' can't be executed.");
        }
        $res = $sth->execute();
        if (!$res) {
            $this->fail(sprintf("Record with %s couldn't be deleted from %s", json_encode($data), $table));
        }
    }

    /**
     * Inserts or updates a database entry on duplicate key.
     *
     * @param  string $table The table name.
     * @param  array $data An associative array of the column names and values to insert.
     *
     * @return void
     */
    public function haveOrUpdateInDatabase($table, array $data)
    {
        $query = $this->driver->insertOrUpdate($table, $data);
        $this->debugSection('Query', $query);

        $sth = $this->driver->getDbh()->prepare($query);
        if (!$sth) {
            $this->fail("Query '$query' can't be executed.");
        }
        $i = 1;
        foreach ($data as $val) {
            $sth->bindValue($i, $val);
            $i++;
        }
        $res = $sth->execute();
        if (!$res) {
            $this->fail(sprintf("Record with %s couldn't be inserted into %s", json_encode($data), $table));
        }
    }
}