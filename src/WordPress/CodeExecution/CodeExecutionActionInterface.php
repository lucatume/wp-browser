<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

interface CodeExecutionActionInterface
{
    public function getClosure(): \Closure;
}
