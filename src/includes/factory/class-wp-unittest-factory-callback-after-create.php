<?php

class WP_UnitTest_Factory_Callback_After_Create
{
    public $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function call($object)
    {
        return call_user_func($this->callback, $object);
    }
}
