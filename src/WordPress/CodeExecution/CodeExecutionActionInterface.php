<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;

interface CodeExecutionActionInterface
{
    public function getClosure(): Closure;
}
