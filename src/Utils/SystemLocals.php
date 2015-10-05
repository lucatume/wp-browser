<?php

namespace tad\WPBrowser\Utils;


class SystemLocals
{
    /**
     * @var string the absolute path to the user home folder
     */
    protected $home;

    /**
     * SystemLocals constructor.
     */
    public function __construct()
    {
    }

    public function setHome($home)
    {
        $this->home = $home;
    }

    public function home()
    {
        return empty($this->home) ? getenv('HOME') : $this->home;
    }
}