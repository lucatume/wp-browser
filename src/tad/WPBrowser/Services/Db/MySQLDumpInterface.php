<?php

namespace tad\WPBrowser\Services\Db;

interface MySQLDumpInterface
{
    /**
     * Saves dump to the file.
     * @param string $file
     * @return void
     */
    public function save($file);

    /**
     * Writes dump to logical file.
     * @param  resource
     * @return void
     */
    public function write($handle = null);

    /**
     * Dumps table to logical file.
     * @param  resource
     * @return void
     */
    public function dumpTable($handle, $table);
}
