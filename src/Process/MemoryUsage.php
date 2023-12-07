<?php

namespace lucatume\WPBrowser\Process;

trait MemoryUsage
{

    /**
     * @var int|null
     */
    protected $memoryUsage = 0;

    public function getMemoryUsage(): ?int
    {
        return $this->memoryUsage;
    }
}
