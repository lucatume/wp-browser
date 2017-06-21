<?php

namespace Codeception\Module;

use Codeception\Lib\Driver\ExtendedMySql;
use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;

class WPDbTest extends \Codeception\Test\Unit {

	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var ModuleContainer
	 */
	protected $moduleContainer;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf(WPDb::class, $sut);
	}

	/**
	 * @return WPDb
	 */
	private function make_instance() {
		return new WPDb($this->moduleContainer->reveal(), $this->config);
	}

	/**
	 * @test
	 * it should not replace the site domain if home is not set in dump
	 */
	public function it_should_not_replace_the_site_domain_if_home_is_not_set_in_dump() {
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');SQL;
SQL;

		$sql = $sut->replaceSiteDomainInSqlString($sql);

		$this->assertRegExp('~.*original.dev/wp.*~', $sql);
		$this->assertNotRegExp('/.*some-wp.dev.*/', $sql);
	}

	/**
	 * @test
	 * it should not replace the site domain if site domain is same
	 */
	public function it_should_not_replace_the_site_domain_if_site_domain_is_same() {
		$this->config['url'] = 'http://original.dev';
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

		$sql = $sut->replaceSiteDomainInSqlString($sql);

		$this->assertRegExp('~.*original.dev.*~', $sql);
		$this->assertNotRegExp('/.*some-wp.dev.*/', $sql);
	}

	/**
	 * @test
	 * it should replace the site domain in dump
	 */
	public function it_should_replace_the_site_domain_in_dump() {
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

		$sql = $sut->replaceSiteDomainInSqlString($sql);

		$this->assertRegExp('/.*some-wp.dev.*/', $sql);
		$this->assertNotRegExp('~.*original.dev/wp.*~', $sql);
	}

	/**
	 * @test
	 * it should replace https scheam with http
	 */
	public function it_should_replace_https_schema_with_http() {
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','https://original.dev/wp','yes'),(2,'home','https://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

		$sql = $sut->replaceSiteDomainInSqlString($sql);

		$this->assertRegExp('~.*http:\\/\\/some-wp.dev.*~', $sql);
		$this->assertNotRegExp('~.*https:\\/\\/original.dev/wp.*~', $sql);
	}

	/**
	 * @test
	 * it should replace http schema with https
	 */
	public function it_should_replace_http_schema_with_https() {
		$this->config['url'] = 'https://some-wp.dev';
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

		$sql = $sut->replaceSiteDomainInSqlString($sql);

		$this->assertRegExp('~.*https:\\/\\/some-wp.dev.*~', $sql);
		$this->assertNotRegExp('~.*https:\\/\\/original.dev/wp.*~', $sql);
	}

	/**
	 * @test
	 * it should not replace domain in sites and blogs table if domain is same
	 */
	public function it_should_not_replace_domain_in_sites_and_blogs_table_if_domain_is_same() {
		$this->config['url'] = 'https://original.dev/wp';
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_blogs` WRITE;
/*!40000 ALTER TABLE `wp_blogs` DISABLE KEYS */;
INSERT INTO `wp_blogs` VALUES (1,2,'original.dev/wp','/','2016-05-03 07:49:57','0000-00-00 00:00:00',1,0,0,0,0,0),(2,2,'second.original.dev/wp','/','2016-05-03 08:03:21','2016-05-03 08:03:21',1,0,0,0,0,0);
/*!40000 ALTER TABLE `wp_blogs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `wp_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_site` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`(140),`path`(51))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

		$sql = $sut->replaceSiteDomainInMultisiteSqlString($sut->replaceSiteDomainInSqlString($sql));

		$this->assertRegExp('~.*original.dev/wp.*~', $sql);
	}

	/**
	 * @test
	 * it should replace domain in sites and blogs table if domain is not same
	 */
	public function it_should_replace_domain_in_sites_and_blogs_table_if_domain_is_not_same() {
		$this->config['url'] = 'https://some-wp.dev';
		$sut = $this->make_instance();

		$sql = <<< SQL
LOCK TABLES `wp_blogs` WRITE;
/*!40000 ALTER TABLE `wp_blogs` DISABLE KEYS */;
INSERT INTO `wp_blogs` VALUES (1,2,'original.dev/wp','/','2016-05-03 07:49:57','0000-00-00 00:00:00',1,0,0,0,0,0),(2,2,'second.original.dev/wp','/','2016-05-03 08:03:21','2016-05-03 08:03:21',1,0,0,0,0,0);
/*!40000 ALTER TABLE `wp_blogs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `wp_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wp_site` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`(140),`path`(51))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

		$sql = $sut->replaceSiteDomainInMultisiteSqlString($sut->replaceSiteDomainInSqlString($sql));

		$this->assertRegExp('~.*some-wp.dev.*~', $sql);
		$this->assertNotRegExp('~.*original.dev/wp.*~', $sql);
	}

	/**
	 * It should allow specifying a dump file to import
	 *
	 * @test
	 */
	public function it_should_allow_specifying_a_dump_file_to_import() {
		$root = vfsStream::setup('root');
		$dumpFle = vfsStream::newFile('foo.sql', 0777);
		$root->addChild($dumpFle);
		$path = $root->url() . '/foo.sql';

		$driver = $this->prophesize(ExtendedMySql::class);
		$driver->load($path)->shouldBeCalled();

		$sut = $this->make_instance();
		$sut->_setDriver($driver->reveal());
		$sut->importSqlDumpFile($path);
	}

	/**
	 * It should throw if specified dump file does not exist
	 *
	 * @test
	 */
	public function it_should_throw_if_specified_dump_file_does_not_exist() {
		$path = __DIR__ . '/foo.sql';

		$driver = $this->prophesize(ExtendedMySql::class);
		$driver->load($path)->shouldNotBeCalled();

		$this->expectException(\InvalidArgumentException::class);

		$sut = $this->make_instance();
		$sut->_setDriver($driver->reveal());

		$sut->importSqlDumpFile($path);
	}

	/**
	 * It should throw is specified dump file is not readable
	 *
	 * @test
	 */
	public function it_should_throw_is_specified_dump_file_is_not_readable() {
		$root = vfsStream::setup('root');
		$dumpFle = vfsStream::newFile('foo.sql', 0000);
		$root->addChild($dumpFle);
		$path = $root->url() . '/foo.sql';

		$driver = $this->prophesize(ExtendedMySql::class);
		$driver->load($path)->shouldNotBeCalled();

		$this->expectException(\InvalidArgumentException::class);

		$sut = $this->make_instance();
		$sut->_setDriver($driver->reveal());

		$sut->importSqlDumpFile($path);
	}

	protected function _before() {
		$this->moduleContainer = $this->prophesize(ModuleContainer::class);
		$this->config = [
			'dsn'         => 'some-dsn',
			'user'        => 'some-user',
			'password'    => 'some-password',
			'url'         => 'http://some-wp.dev',
			'tablePrefix' => 'wp_',
		];
	}

	/**
	 * It should replace the site domain in an array sql dump
	 * @test
	 */
	public function it_should_replace_the_site_domain_in_an_array_sql_dump() {
		$sql = [
			"LOCK TABLES `wp_options` WRITE;",
			"/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;",
			"INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');"
		];

		$expectedSql = [
			"LOCK TABLES `wp_options` WRITE;",
			"/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;",
			"INSERT INTO `wp_options` VALUES (1,'siteurl','http://some-wp.dev','yes'),(2,'home','http://some-wp.dev','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');"
		];

		$sut      = $this->make_instance();
		$replaced = $sut->replaceSiteDomainInSqlArray( $sql );

		$this->assertInternalType( 'array', $replaced );
		$this->assertEquals( $expectedSql, $replaced );
	}

  /**
   * It should return an empty array if trying to replace site domain in empty
   * sql
   *
   * @test
   */
  public function it_should_return_an_empty_array_if_trying_to_replace_site_domain_in_empty_sql() {
    $sut = $this->make_instance();
    $this->assertEquals([], $sut->replaceSiteDomainInSqlArray([]));
  }

  /**
   * It should replace the site domain in a multisite array sql dump
   *
   * @test
   */
  public function it_should_replace_the_site_domain_in_a_multisite_array_sql_dump() {
    $sql = [
      "LOCK TABLES `wp_blogs` WRITE;",
      "/*!40000 ALTER TABLE `wp_blogs` DISABLE KEYS */;",
      "INSERT INTO `wp_blogs` VALUES (1,2,'original.dev/wp','/','2016-05-03 07:49:57','0000-00-00 00:00:00',1,0,0,0,0,0),(2,2,'second.original.dev/wp','/','2016-05-03 08:03:21','2016-05-03 08:03:21',1,0,0,0,0,0);",
      "/*!40000 ALTER TABLE `wp_blogs` ENABLE KEYS */;",
      "UNLOCK TABLES;",
      "DROP TABLE IF EXISTS `wp_site`;",
      "/*!40101 SET @saved_cs_client     = @@character_set_client */;",
      "/*!40101 SET character_set_client = utf8 */;",
      "CREATE TABLE `wp_site` (",
      "  `id` bigint(20) NOT NULL AUTO_INCREMENT,",
      "  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',",
      "  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',",
      "  PRIMARY KEY (`id`),",
      "  KEY `domain` (`domain`(140),`path`(51))",
      ") ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ];

    $expectedSql = [
      "LOCK TABLES `wp_blogs` WRITE;",
      "/*!40000 ALTER TABLE `wp_blogs` DISABLE KEYS */;",
      "INSERT INTO `wp_blogs` VALUES (1,2,'some-wp.dev','/','2016-05-03 07:49:57','0000-00-00 00:00:00',1,0,0,0,0,0),(2,2,'second.some-wp.dev','/','2016-05-03 08:03:21','2016-05-03 08:03:21',1,0,0,0,0,0);",
      "/*!40000 ALTER TABLE `wp_blogs` ENABLE KEYS */;",
      "UNLOCK TABLES;",
      "DROP TABLE IF EXISTS `wp_site`;",
      "/*!40101 SET @saved_cs_client     = @@character_set_client */;",
      "/*!40101 SET character_set_client = utf8 */;",
      "CREATE TABLE `wp_site` (",
      "  `id` bigint(20) NOT NULL AUTO_INCREMENT,",
      "  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',",
      "  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',",
      "  PRIMARY KEY (`id`),",
      "  KEY `domain` (`domain`(140),`path`(51))",
      ") ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ];

    $sut      = $this->make_instance();
    $replaced = $sut->replaceSiteDomainInMultisiteSqlArray($sql);

    $this->assertInternalType('array', $replaced);
    $this->assertEquals($expectedSql, $replaced);
  }

  /**
   * It should return an empty array if trying to replace site domain in empty
   * multisite sql
   *
   * @test
   */
  public function it_should_return_an_empty_array_if_trying_to_replace_site_domain_in_empty_multisite_sql() {
    $sut = $this->make_instance();
    $this->assertEquals([], $sut->replaceSiteDomainInMultisiteSqlArray([]));
  }
}