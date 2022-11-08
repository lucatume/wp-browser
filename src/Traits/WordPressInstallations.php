<?php

namespace lucatume\WPBrowser\Traits;

use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use function Patchwork\configure;

trait WordPressInstallations
{
    /**
     * @var array<Installation>
     */
    protected array $installations = [];

    /**
     * @after
     */
    public function tearDownWordPressInstallations(): void
    {
        if ($this->hasFailed()) {
            foreach ($this->installations as $installation) {
                $dir = $installation->getRootDir();
                codecept_debug("WordPress installation at $dir not removed to allow debugging failure.");
            }
            return;
        }

        foreach ($this->installations as $installation) {
            $installation->destroy();
        }
    }

    abstract protected function getWordPressInstallationDbCredentials(): array;

    protected function makeWordPressInstallation(string $phase = 'up'): Installation
    {
        $tmpDir = FS::tmpDir('wp_');
        $dbName = basename($tmpDir);
        $dbCredentials = array_values(array_merge($this->getWordPressInstallationDbCredentials(),
            ['DB_NAME' => $dbName]));
        $db = new Db(...$dbCredentials);
        $installation = new Installation($tmpDir, 'latest', $db);
        $this->installations[] = $installation;

        return match ($phase) {
            'scaffold' => $installation->scaffold(),
            'configure' => $installation->scaffold()->configure(),
            'up' => $installation->scaffold()->configure()->install(),
            default => throw new \InvalidArgumentException("Invalid phase $phase"),
        };
    }
}
