<?php

namespace lucatume\WPBrowser\MonkeyPatch\Patchers;

interface PatcherInterface
{
    /**
     * @return array{string, string}
     */
    public function patch(mixed $fileContents, string $pathname): array;
}
