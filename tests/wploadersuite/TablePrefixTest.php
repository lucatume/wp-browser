<?php

use PHPUnit\Framework\AssertionFailedError;
use function tad\WPBrowser\importDumpWithMysqlBin;

class TablePrefixTest extends \Codeception\TestCase\WPTestCase
{

    public static $otherInstallationPrefix = 'foo_';

    public static function wpSetUpBeforeClass()
    {
        self::importOtherPrefixInstallation();
    }

    protected static function importOtherPrefixInstallation()
    {
        $dumpFile = self::getDumpFilePath();
        list($dbName, $dbUser, $dbPass, $dbHost) = self::getDbAccessCredentials();

        $imported = importDumpWithMysqlBin($dumpFile, $dbName, $dbUser, $dbPass, $dbHost);
        if (!$imported) {
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

        $creds = [
            getenv('WORDPRESS_DB_NAME'),
            getenv('WORDPRESS_DB_USER'),
            getenv('WORDPRESS_DB_PASSWORD') ?: '',
            getenv('WORDPRESS_DB_HOST')
        ];

        if (count(array_filter($creds)) < 3) {
            throw new \RuntimeException('Could not fetch database credentials.');
        }

        return $creds;
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
