<?php

namespace tad\WPBrowser\Generators;

use DoctrineTest\InstantiatorTestAsset\PharAsset;
use Hautelook\Phpass\PasswordHash;

class WpPassword
{

    /**
     * @var \MikeMcLin\WpPassword\WpPassword
     */
    protected static $instance;

    /**
     * Singleton constructor for the class.
     *
     * @return \MikeMcLin\WpPassword\WpPassword
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new \MikeMcLin\WpPassword\WpPassword(new PasswordHash(8, true));
        }

        return static::$instance;
    }
}
