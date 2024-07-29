<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class BackupControlTestCaseOverridingStore
{
    public static $staticAttribute = 'initial_value';
}

class BackupControlTestCaseOverridingTestCase extends WPTestCase
{
    protected $backupGlobals = false;
    protected $backupStaticProperties = false;

    /**
     * Override the method to avoid issues with Codeception specific meta data.
     */
    protected function _setUp()
    {
        $this->_before();
    }

    public function testBackupGlobalsIsFalse(): void
    {
        $this->assertFalse($this->backupGlobals);
    }

    public function testWillAlterStoreStaticAttribute(): void
    {
        BackupControlTestCaseOverridingStore::$staticAttribute = 'updated_value';
        $this->assertTrue(true); // Useless assertion to avoid the test to be marked as risky. }
    }
}
