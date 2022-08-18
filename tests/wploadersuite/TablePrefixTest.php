<?php

use lucatume\WPBrowser\Utils\Db;
use PHPUnit\Framework\AssertionFailedError;

class TablePrefixTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{

    public static $otherInstallationPrefix = 'foo_';

    public static function wpSetUpBeforeClass(): void
    {
        self::importOtherPrefixInstallation();
    }

    protected static function importOtherPrefixInstallation(): void
    {
        $dumpFile = self::getDumpFilePath();
        [$dbName, $dbUser, $dbPass, $dbHost] = self::getDbAccessCredentials();

        Db::importDumpWithMysqlBin($dumpFile, $dbName, $dbUser, $dbPass, $dbHost);
    }

    /**
     * @return string
     */
    protected static function getDumpFilePath(): string
    {
        $dumpFile = codecept_data_dir('foo-installation.sql');
        return $dumpFile;
    }

    /**
     * @return array
     */
    protected static function getDbAccessCredentials(): array
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
    public function it_should_not_destroy_another_installation_on_the_same_database(): void
    {
        [$dbName, $dbUser, $dbPass, $dbHost] = self::getDbAccessCredentials();

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
