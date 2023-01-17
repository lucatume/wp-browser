<?php

namespace lucatume\WPBrowser\MonkeyPatch\Patchers;

use lucatume\WPBrowser\MonkeyPatch\MonkeyPatchingException;

class FileReplacementPatcher implements PatcherInterface
{
    private string $replacementFile;

    public function __construct(string $replacementFile)
    {
        $this->replacementFile = $replacementFile;
    }

    public function patch(string $fileContents, string $pathname): array
    {
        $replacementFileContents = file_get_contents($this->replacementFile);

        if ($replacementFileContents === false) {
            throw new MonkeyPatchingException("Could not read replacement file: {$this->replacementFile}");
        }

        return [$replacementFileContents, $this->replacementFile];
    }
}
