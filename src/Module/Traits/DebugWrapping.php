<?php

namespace lucatume\WPBrowser\Module\Traits;

use Closure;

trait DebugWrapping
{
    private function relayOutputToDebug(string $title): Closure
    {
        return function (string $buffer) use ($title): string {
            foreach (array_filter(preg_split("/(\r\n|\n|\r)/", $buffer)) as $line) {
                $this->debugSection($title, $line);
            }

            return '';
        };
    }
}
