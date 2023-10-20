<?php

namespace lucatume\WPBrowser\Module\Support;

use Codeception\Test\Unit;
use Codeception\Util\Debug;
use PHPUnit\Framework\Assert;

class DbDumpTest extends Unit
{
    /**
     * @var string
     */
    protected $url = 'http://some-wp.dev';

    /**
     * @test
     * it should not replace the site domain if site domain is same
     */
    public function it_should_not_replace_the_site_domain_if_site_domain_is_same(): void
    {
        $this->url = 'http://original.dev';
        $sut       = $this->make_instance();

        $sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

        $sql = $sut->replaceSiteDomainInSqlString($sql);

        $this->assertMatchesRegularExpression('~.*original.dev.*~', $sql);
        $this->assertDoesNotMatchRegularExpression('/.*some-wp.dev.*/', $sql);
    }

    /**
     * @test
     * it should replace the site domain in dump
     */
    public function it_should_replace_the_site_domain_in_dump(): void
    {
        $sut = $this->make_instance();

        $sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

        $sql = $sut->replaceSiteDomainInSqlString($sql);

        $this->assertMatchesRegularExpression('/.*some-wp.dev.*/', $sql);
        $this->assertDoesNotMatchRegularExpression('~.*original.dev/wp.*~', $sql);
    }

    /**
     * @test
     * it should replace https schema with http
     */
    public function it_should_replace_https_schema_with_http(): void
    {
        $this->url = 'http://some-wp.dev';
        $sut = $this->make_instance();
        $sut->setOriginalUrl('original.dev/wp');

        $sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','https://original.dev/wp','yes'),(2,'home','https://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

        $sql = $sut->replaceSiteDomainInSqlString($sql);

        $this->assertMatchesRegularExpression('~.*http:\\/\\/some-wp.dev.*~', $sql);
        $this->assertDoesNotMatchRegularExpression('~.*https:\\/\\/original.dev/wp.*~', $sql);
    }

    /**
     * @test
     * it should replace http schema with https
     */
    public function it_should_replace_http_schema_with_https(): void
    {
        $this->url = 'https://some-wp.dev';
        $sut       = $this->make_instance();
        $sut->setOriginalUrl('original.dev/wp');

        $sql = <<< SQL
LOCK TABLES `wp_options` WRITE;
/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;
-- noinspection SqlNoDataSourceInspection
INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');
SQL;

        $sql = $sut->replaceSiteDomainInSqlString($sql);

        $this->assertMatchesRegularExpression('~.*https:\\/\\/some-wp.dev.*~', $sql);
        $this->assertDoesNotMatchRegularExpression('~.*https:\\/\\/original.dev/wp.*~', $sql);
    }

    /**
     * @test
     * it should not replace domain in sites and blogs table if domain is same
     */
    public function it_should_not_replace_domain_in_sites_and_blogs_table_if_domain_is_same(): void
    {
        $this->url = 'https://original.dev/wp';
        $sut       = $this->make_instance();
        $sut->setOriginalUrl('original.dev/wp');

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

        $this->assertMatchesRegularExpression('~.*original.dev/wp.*~', $sql);
    }

    /**
     * @test
     * it should replace domain in sites and blogs table if domain is not same
     */
    public function it_should_replace_domain_in_sites_and_blogs_table_if_domain_is_not_same(): void
    {
        $this->url = 'https://some-wp.dev';
        $sut       = $this->make_instance();
        $sut->setOriginalUrl('https://original.dev/wp/');

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

        $this->assertMatchesRegularExpression('~.*some-wp.dev.*~', $sql);
        $this->assertDoesNotMatchRegularExpression('~.*original.dev/wp.*~', $sql);
    }

    /**
     * It should replace the site domain in an array sql dump
     *
     * @test
     */
    public function it_should_replace_the_site_domain_in_an_array_sql_dump(): void
    {
        $sql = [
            'LOCK TABLES `wp_options` WRITE;',
            '/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;',
            "INSERT INTO `wp_options` VALUES (1,'siteurl','http://original.dev/wp','yes'),(2,'home','http://original.dev/wp','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');",
        ];

        $expectedSql = [
            'LOCK TABLES `wp_options` WRITE;',
            '/*!40000 ALTER TABLE `wp_options` DISABLE KEYS */;',
            "INSERT INTO `wp_options` VALUES (1,'siteurl','http://some-wp.dev','yes'),(2,'home','http://some-wp.dev','yes'),(3,'blogname','Tribe Premium Plugins','yes'),(4,'blogdescription','Just another WordPress site','yes'),(5,'users_can_register','0','yes'),(6,'admin_email','admin@original.dev','yes'),(7,'start_of_week','1','yes'),(8,'use_balanceTags','0','yes'),(9,'use_smilies','1','yes'),(10,'require_name_email','1','yes'),(11,'comments_notify','1','yes'),(12,'posts_per_rss','10','yes'),(13,'rss_use_excerpt','0','yes'),(14,'mailserver_url','mail.example.com','yes'),(15,'mailserver_login','login@example.com','yes'),(16,'mailserver_pass','password','yes'),(17,'mailserver_port','110','yes'),(18,'default_category','1','yes'),(19,'default_comment_status','open','yes'),(20,'default_ping_status','open','yes'),(21,'default_pingback_flag','0','yes'),(22,'posts_per_page','10','yes'),(23,'date_format','F j, Y','yes'),(24,'time_format','g:i a','yes'),(25,'links_updated_date_format','F j, Y g:i a','yes'),(26,'comment_moderation','0','yes'),(27,'moderation_notify','1','yes'),(28,'permalink_structure','/%year%/%monthnum%/%day%/%postname%/','yes'),'alabar_last_save_post','1465471896','yes');",
        ];

        $sut      = $this->make_instance();
        $sut->setOriginalUrl('original.dev/wp');
        $replaced = $sut->replaceSiteDomainInSqlArray($sql);

        $this->assertEquals($expectedSql, $replaced);
    }

    /**
     * It should return an empty array if trying to replace site domain in empty
     * sql
     *
     * @test
     */
    public function it_should_return_an_empty_array_if_trying_to_replace_site_domain_in_empty_sql(): void
    {
        $sut = $this->make_instance();
        $this->assertEquals([], $sut->replaceSiteDomainInSqlArray([]));
    }

    /**
     * It should replace the site domain in a multisite array sql dump
     *
     * @test
     */
    public function it_should_replace_the_site_domain_in_a_multisite_array_sql_dump(): void
    {
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

        $this->assertEquals($expectedSql, $replaced);
    }

    /**
     * It should return an empty array if trying to replace site domain in empty
     * multisite sql
     *
     * @test
     */
    public function it_should_return_an_empty_array_if_trying_to_replace_site_domain_in_empty_multisite_sql(): void
    {
        $sut = $this->make_instance();
        $this->assertEquals([], $sut->replaceSiteDomainInMultisiteSqlArray([]));
    }

    /**
     * @return DbDump
     */
    protected function make_instance(): DbDump
    {
        $dbOperations = new DbDump($this->url, 'wp_');
        return $dbOperations;
    }

    /**
     * It should correctly replace subdomain URLs in multisite installations
     *
     * @test
     */
    public function should_correctly_replace_subdomain_urls_in_multisite_installations(): void
    {
        $inputFile = codecept_data_dir('dump-test/mu-01-input.sql');
        $inputFileHandle = fopen($inputFile, 'rb');
        $expectedFile = codecept_data_dir('dump-test/mu-01-expected.sql');
        $expectedFileExists = file_exists($expectedFile);
        $expectedFileHandle = $expectedFileExists ? fopen($expectedFile, 'rb') : fopen($expectedFile, 'wb');

        $dbDump = $this->make_instance();
        $dbDump->setUrl('http://wordpress.localhost');
        $dbDump->setOriginalUrl($dbDump->getOriginalUrlFromSqlString(file_get_contents($inputFile)));

        if (! $expectedFileExists && Debug::isEnabled()) {
            while (! feof($inputFileHandle)) {
                $inputLine = fgets($inputFileHandle);
                $replaced  = $dbDump->replaceSiteDomainInSqlString($inputLine);
                $replaced  = $dbDump->replaceSiteDomainInMultisiteSqlString($replaced);
                fwrite($expectedFileHandle, $replaced, strlen($replaced));
            }
            fclose($expectedFileHandle);
            fclose($inputFileHandle);
            return;
        }

        $lineNumber = 0;
        while (!feof($inputFileHandle)) {
            if (feof($expectedFileHandle)) {
                $this->fail('The input file has still lines while the expected output file does not.');
            }
            $lineNumber++;
            $inputLine = fgets($inputFileHandle);
            $expectedLine = fgets($expectedFileHandle);
            $replaced = $dbDump->replaceSiteDomainInSqlString($inputLine);
            $replaced = $dbDump->replaceSiteDomainInMultisiteSqlString($replaced);
            $this->assertEquals($expectedLine, $replaced, 'Error at line number ' . $lineNumber);
        }
    }

    /**
     * It should correctly replace localhost host address with pretty URL
     *
     * @test
     */
    public function should_correctly_replace_localhost_host_address_with_pretty_url(): void
    {
        $inputFile = codecept_data_dir('dump-test/url-replacement-test-01.sql');
        $sql         = file_get_contents($inputFile);

        $dbDump      = $this->make_instance();
        $dbDump->setUrl('http://some-nice-host-name');
        $originalUrl = $dbDump->getOriginalUrlFromSqlString($sql);

        $this->assertEquals('http://localhost:5100', $originalUrl);

        $replacedSql = $dbDump->replaceSiteDomainInSqlString($sql);
        $this->assertEquals('http://some-nice-host-name', $dbDump->getOriginalUrlFromSqlString($replacedSql));
    }

    public function replacementUrlsDataProvider(): array
    {
        return [
            'IP Address to URL' => ['http://1.2.3.4', 'http://wordpress.local'],
            'IP Address with port to URL' => ['http://127.0.0.1:8888', 'http://wordpress.local'],
            'IP Address with port to URL with port' => ['http://127.0.0.1:8888', 'http://wordpress.local:2111'],
            'IP Address with port to URL with same port' => ['http://127.0.0.1:8888', 'http://wordpress.local:8888'],
            'IP address to IP address' => ['http://127.0.0.1', 'http://1.2.3.4'],
            'IP address with port to IP address' => ['http://127.0.0.1:9999', 'http://1.2.3.4'],
            'IP address with port to IP address with port' => ['http://127.0.0.1:9999', 'http://1.2.3.4:2133'],
            'IP address to IP address with port' => ['http://127.0.0.1', 'http://1.2.3.4:2133'],
            'URL to IP Address' => ['http://wordpress.local', 'http://1.2.3.4:2133'],
            'URL with port to IP Address' => ['http://wordpress.local', 'http://1.2.3.4:2133'],
            'URL with port to IP Address with port' => ['http://wordpress.local:9993', 'http://1.2.3.4:2133']
        ];
    }

    /**
     * It should correctly replace local URL with IP address
     *
     * @test
     * @dataProvider replacementUrlsDataProvider
     */
    public function should_correctly_replace_local_url_with_ip_address($startUrl, $destUrl): void
    {
        $inputFile = codecept_data_dir('dump-test/url-replacement-test.sql.handlebars');
        $sql         = str_replace('{{ siteurl }}', $startUrl, file_get_contents($inputFile));

        $dbDump      = $this->make_instance();
        $dbDump->setUrl($destUrl);
        $originalUrl = $dbDump->getOriginalUrlFromSqlString($sql);

        $this->assertEquals($startUrl, $originalUrl);

        $replacedSql = $dbDump->replaceSiteDomainInSqlString($sql);
        $this->assertEquals($destUrl, $dbDump->getOriginalUrlFromSqlString($replacedSql));
    }
}
