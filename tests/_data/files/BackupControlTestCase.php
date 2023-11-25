<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class BackupControlTestCaseStore
{
    public static $staticAttribute = 'initial_value';
    public static $staticAttributeTwo = 'initial_value';
    public static $staticAttributeThree = 'initial_value';
    public static $staticAttributeFour = 'initial_value';
}

class BackupControlTestCaseStoreTwo
{
    public static $staticAttribute = 'initial_value';
    public static $staticAttributeTwo = 'initial_value';
    public static $staticAttributeThree = 'initial_value';
    public static $staticAttributeFour = 'initial_value';
}

class BackupControlTestCase extends WPTestCase
{
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

    public function testBackupGlobalsIsTrue(): void
    {
        $this->assertTrue($this->backupGlobals);
    }

    public function testWillUpdateTheValueOfGlobalVar(): void
    {
        global $_wpbrowser_test_global_var;
        $_wpbrowser_test_global_var = 'updated_value';
        $this->assertTrue(true); // Useless assertion to avoid the test to be marked as risky.
    }

    public function testWillAlterStoreStaticAttribute(): void
    {
        BackupControlTestCaseStore::$staticAttribute = 'updated_value';
        BackupControlTestCaseStore::$staticAttributeTwo = 'updated_value';
        BackupControlTestCaseStore::$staticAttributeThree = 'updated_value';
        BackupControlTestCaseStore::$staticAttributeFour = 'updated_value';
        BackupControlTestCaseStoreTwo::$staticAttribute = 'updated_value';
        BackupControlTestCaseStoreTwo::$staticAttributeTwo = 'updated_value';
        BackupControlTestCaseStoreTwo::$staticAttributeThree = 'updated_value';
        BackupControlTestCaseStoreTwo::$staticAttributeFour = 'updated_value';
        $this->assertTrue(true); // Useless assertion to avoid the test to be marked as risky.
    }
}
