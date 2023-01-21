<?php

class wpdb
{
    public array $queries = [];
    public array $tables = [];

    public function __call($name, array $args)
    {
        codecept_debug("wpdb::{$name} called with args: " . json_encode($args));
    }

    public function tables()
    {
        return $this->tables;
    }

    public function query($query)
    {
    }
}
