<?php

namespace lucatume\WPBrowser\Project;

use Closure;

class TestEnvironment
{
    /**
     * @var string
     */
    public $wpRootDir = 'var/wordpress';
    /**
     * @var string
     */
    public $dbUrl = 'mysql://User:Pa55word@localhost:3306/test';
    /**
     * @var string
     */
    public $testTablePrefix = 'test_';
    /**
     * @var string
     */
    public $wpTablePrefix = 'wp_';
    /**
     * @var string
     */
    public $wpUrl = 'http://wordpress.test';
    /**
     * @var string
     */
    public $wpDomain = 'wordpress.test';
    /**
     * @var string
     */
    public $wpAdminUser = 'admin';
    /**
     * @var string
     */
    public $wpAdminPassword = 'password';
    /**
     * @var string
     */
    public $chromeDriverHost = 'localhost';
    /**
     * @var int
     */
    public $chromeDriverPort = 4444;
    /**
     * @var array<string,array<string,mixed>>
     */
    public $extensionsEnabled = [];
    /**
     * @var string|null
     */
    public $dumpFile;
    /**
     * @var \Closure|null
     */
    public $afterSuccess;
    /**
     * @var array<class-string>
     */
    public $customCommands = [];
    /**
     * @var string
     */
    public $extraEnvFileContents = '';

    public function runAfterSuccess(): void
    {
        if ($this->afterSuccess === null) {
            return;
        }
        ($this->afterSuccess)();
    }
}
