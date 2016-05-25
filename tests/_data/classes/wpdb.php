<?php

if (!class_exists('wpdb')) {
    /**
     * A \wpdb class shim for testing purposes.
     */
    class wpdb
    {
        private $result = null;
        public $queries = [];
    }
}
