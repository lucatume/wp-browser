<?php

namespace Codeception\Module;


use Codeception\Module;
use WP_CLI\Configurator;

class WPCLI extends Module

{
    protected $wpCliRoot;

    public function cli()
    {
        if (empty($this->wpCliRoot)) {
            $this->initWpCliRoot();
        }

        include $this->wpCliRoot . '/php/wp-cli.php';
    }

    protected function initWpCliRoot()
    {
        $ref = new \ReflectionClass(Configurator::class);
        $this->wpCliRoot = dirname(dirname(dirname($ref->getFileName())));
        if (!defined('WP_CLI_ROOT')) {
            define('WP_CLI_ROOT', $this->wpCliRoot);
        }
    }
}