<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\Run;
use Codeception\CustomCommandInterface;

class RunOriginal extends Run implements CustomCommandInterface
{
    public static function getCommandName(): string
    {
        // Replace the Codeception `run` command with this one.
        return 'codeception:run';
    }

    public function getDescription(): string
    {
        return 'Runs the original Codeception `run` command.';
    }
}
