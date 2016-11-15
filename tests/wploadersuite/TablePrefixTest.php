<?php

use function tad\WPBrowser\Tests\Support\importDump;

class TablePrefixTest extends \Codeception\TestCase\WPTestCase
{

	public static $otherInstallationPrefix = 'foo_';

	public static function setUpBeforeClass()
	{
		self::importOtherPrefixInstallation();

		parent::setUpBeforeClass();
	}

	protected static function importOtherPrefixInstallation()
	{
		$dumpFile = self::getDumpFilePath();
		list($dbName, $dbUser, $dbPass, $dbHost) = self::getDbAccessCredentials();

		$output = [];
		if (!importDump($dumpFile, $dbName, $dbUser, $dbPass, $dbHost)) {
			throw new PHPUnit_Framework_AssertionFailedError("Test failed as MySQL import failed\nCredentials: " .
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
		$dbName = getenv('wpLoaderDbName') ?: 'codeception-tests';
		$dbUser = 'root';
		$dbPass = getenv('TRAVIS') ? '' : 'root';
		$dbHost = 'localhost';

		return array($dbName, $dbUser, $dbPass, $dbHost);
	}

	public function setUp()
	{
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown()
	{
		// your tear down methods here

		// then
		parent::tearDown();
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
			throw new PHPUnit_Framework_AssertionFailedError('Test failed as MySQL connection failed');
		}

		$tables = $db->query("show tables like 'foo_posts'");

		$this->assertNotEmpty($tables->fetch());

		$posts = $db->query("select post_title from foo_posts where post_title = 'foo'");

		$this->assertNotEmpty($posts->fetch());
	}

}