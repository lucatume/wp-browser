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
}