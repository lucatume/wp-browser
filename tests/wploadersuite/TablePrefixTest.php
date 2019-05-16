<?php

use PHPUnit\Framework\AssertionFailedError;
use function tad\WPBrowser\Tests\Support\importDump;

class TablePrefixTest extends \Codeception\TestCase\WPTestCase
{

    public static $otherInstallationPrefix = 'foo_';

    public static function _setUpBeforeClass()
    {
        self::importOtherPrefixInstallation();
    }

    protected static function importOtherPrefixInstallation()
    {
        $dumpFile = self::getDumpFilePath();
        list($dbName, $dbUser, $dbPass, $dbHost) = self::getDbAccessCredentials();

        if (!importDump($dumpFile, $dbName, $dbUser, $dbPass, $dbHost)) {
            throw new AssertionFailedError("Test failed as MySQL import failed\nCredentials: " .
               print_r(self::getDbAccessCredentials(), true) . "\nPath: " . self::getDumpFilePath());
        }
    }

    /**
     * @return string
     */
    protected static function getDumpFilePath()
    {
        $dumpFile = codecept_data_dir('foo-installation.sql');
        return $dumpFile;
    }

    /**
     * @return array
     */
    protected static function getDbAccessCredentials()
    {
        $dbName = getenv('TEST_DB_NAME') ?: 'codeception-tests';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASSWORD') ?: '';
        $dbHost = getenv('DB_HOST') ?: 'localhost';

        return array($dbName, $dbUser, $dbPass, $dbHost);
    }

    /**
     * @test
     * it should not destroy another installation on the same database
     */
    public function it_should_not_destroy_another_installation_on_the_same_database()
    {
        list($dbName, $dbUser, $dbPass, $dbHost) = self::getDbAccessCredentials();

        try {
            $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new AssertionFailedError('Test failed as MySQL connection failed');
        }

        $tables = $db->query("SHOW TABLES LIKE 'foo_posts'");

        $this->assertNotEmpty($tables->fetch());

        $posts = $db->query("SELECT post_title FROM foo_posts WHERE post_title = 'foo'");

        $this->assertNotEmpty($posts->fetch());
    }
}
