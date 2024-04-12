<?php

namespace lucatume\WPBrowser\Project;

use Closure;

class TestEnvironment
{
    public string $wpRootDir = 'var/wordpress';
    public string $dbUrl = 'mysql://User:Pa55word@localhost:3306/test';
    public string $testTablePrefix = 'test_';
    public string $wpTablePrefix = 'wp_';
    public string $wpUrl = 'http://wordpress.test';
    public string $wpDomain = 'wordpress.test';
    public string $wpAdminPath = '/wp-admin';
    public string $wpAdminUser = 'admin';
    public string $wpAdminPassword = 'password';
    public string $chromeDriverHost = 'localhost';
    public int $chromeDriverPort = 4444;
    /**
     * @var array<string,array<string,mixed>>
     */
    public array $extensionsEnabled = [];
    public ?string $dumpFile = null;
    public ?Closure $afterSuccess = null;
    /**
     * @var array<class-string>
     */
    public array $customCommands = [];
    public string $extraEnvFileContents = '';

    public function runAfterSuccess(): void
    {
        if ($this->afterSuccess === null) {
            return;
        }
        ($this->afterSuccess)();
    }
}
