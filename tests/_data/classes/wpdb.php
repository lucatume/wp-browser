<?php

if (!class_exists('wpdb')) {
    class wpdb
    {
        public $queries = [];
    }
}
