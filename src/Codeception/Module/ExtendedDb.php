<?php

namespace Codeception\Module;

use Codeception\Lib\Driver\ExtendedDbDriver;
use Codeception\Lib\Driver\ExtendedMySql;

class ExtendedDb extends Db
{

    /**
     * Deletes a database entry.
     *
     * @param  string $table The table name.
     * @param  array $criteria An associative array of the column names and values to use as deletion criteria.
     *
     * @return void
     */
    public function dontHaveInDatabase($table, array $criteria)
    {
        $this->driver->deleteQueryByCriteria($table, $criteria);
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
        $this->extendDriver();

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

    protected function extendDriver()
    {
        if (!$this->driver instanceof ExtendedMySql) {
            try {
                $this->driver = ExtendedDbDriver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
            } catch (\PDOException $e) {
                $message = $e->getMessage();
                if ($message === 'could not find driver') {
                    list ($missingDriver,) = explode(':', $this->config['dsn'], 2);
                    $message = "could not find $missingDriver driver";
                }

                throw new ModuleException(__CLASS__, $message . ' while creating PDO connection');
            }
            $this->debugSection('WPDb', 'Connected to ' . $this->driver->getDb());
            $this->dbh = $this->driver->getDbh();
        }
    }
}