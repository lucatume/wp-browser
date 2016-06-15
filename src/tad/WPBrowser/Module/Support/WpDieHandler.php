<?php

namespace tad\WPBrowser\Module\Support;


class WpDieHandler implements WpDieHandlerInterface
{

    public function handleAjaxDie()
    {
        return [$this, 'echoDieMessage'];
    }

    public function handleXmlrpcDie()
    {
        return [$this, 'echoDieMessage'];
    }

    public function handleDie()
    {
        return [$this, 'echoDieMessage'];
    }

    public function echoDieMessage($message, $title, $args)
    {
        echo $message;
    }
}