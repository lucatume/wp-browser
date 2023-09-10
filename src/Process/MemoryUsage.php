<?php

namespace lucatume\WPBrowser\Process;

trait MemoryUsage
{

    protected ?int $memoryUsage = 0;

    public function getMemoryUsage(): ?int
    {
        return $this->memoryUsage;
    }
}
