<?php

namespace tad\WPBrowser\Module\Support;

interface WpDieHandlerInterface
{
    public function handleAjaxDie();

    public function handleXmlrpcDie();

    public function handleDie();

    public function echoDieMessage($message, $title, $args);
}