<?php

namespace tad\WPBrowser\Generators;


use Handlebars\Handlebars;

class Tables {

	/**
	 * @var Handlebars
	 */
	protected $handlebars;

	/**
	 * @var string
	 */
	protected $templatesDir;

	/**
	 * Tables constructor.
	 *
	 * @param Handlebars|null $handlebars
	 */
	public function __construct( Handlebars $handlebars = null ) {
		$this->handlebars   = $handlebars ?: new Handlebars();
		$this->templatesDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'templates';
	}

	public static function multisiteScaffold() {
		return <<< SQL
CREATE TABLE IF NOT EXISTS `{{prefix}}blog_versions` (
  `blog_id` bigint(20) NOT NULL DEFAULT '0',
  `db_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`blog_id`),
  KEY `db_version` (`db_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `{{prefix}}blog_versions` WRITE;
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS`{{prefix}}blogs` (
  `blog_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `site_id` bigint(20) NOT NULL DEFAULT '0',
  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `public` tinyint(2) NOT NULL DEFAULT '1',
  `archived` tinyint(2) NOT NULL DEFAULT '0',
  `mature` tinyint(2) NOT NULL DEFAULT '0',
  `spam` tinyint(2) NOT NULL DEFAULT '0',
  `deleted` tinyint(2) NOT NULL DEFAULT '0',
  `lang_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`),
  KEY `domain` (`domain`(50),`path`(5)),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `{{prefix}}blogs` WRITE;
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `{{prefix}}registration_log` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `IP` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `blog_id` bigint(20) NOT NULL DEFAULT '0',
  `date_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `IP` (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `{{prefix}}registration_log` WRITE;
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `{{prefix}}signups` (
  `signup_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `activation_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `meta` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`signup_id`),
  KEY `activation_key` (`activation_key`),
  KEY `user_email` (`user_email`),
  KEY `user_login_email` (`user_login`,`user_email`),
  KEY `domain_path` (`domain`(140),`path`(51))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `{{prefix}}signups` WRITE;
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `{{prefix}}site` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `domain` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`(140),`path`(51))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `{{prefix}}site` WRITE;
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `{{prefix}}sitemeta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `site_id` bigint(20) NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`meta_id`),
  KEY `meta_key` (`meta_key`(191)),
  KEY `site_id` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `{{prefix}}sitemeta` WRITE;
UNLOCK TABLES;
SQL;
	}

	public function getAlterTableQuery( $table, $prefix ) {
		$data = [ 'operation' => 'ALTER TABLE', 'prefix' => $prefix ];
		return in_array( $table, $this->alterableTables() ) ? $this->renderQuery( $table, $data ) : '';
	}

	private function alterableTables() {
		return [
			'users'
		];
	}

	/**
	 * @param $table
	 * @param $data
	 */
	protected function renderQuery( $table, $data ) {
		if ( !in_array( $table, $this->tables() ) ) {
			throw new \InvalidArgumentException( 'Table ' . $table . ' is not a multisite table name' );
		}

		$template = $this->templates( $table );
		return $this->handlebars->render( $template, $data );
	}

	private function tables() {
		return array_merge( [ ], $this->multisiteTables() );
	}

	public static function multisiteTables() {
		return [
			'blogs',
			'blog_versions',
			'sitemeta',
			'site',
			'signups',
			'registration_log'
		];
	}

	private function templates( $table ) {
		$map = [
			'blogs'            => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'blogs.handlebars' ) );
			},
			'blog_versions'    => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'blog_versions.handlebars' ) );
			},
			'registration_log' => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'registration_log.handlebars' ) );
			},
			'signups'          => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'signups.handlebars' ) );
			},
			'site'             => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'site.handlebars' ) );
			},
			'sitemeta'         => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'site_meta.handlebars' ) );
			},
			'users'            => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'users.handlebars' ) );
			}
		];

		return $map[$table]();
	}

	public function getCreateTableQuery( $table, $prefix ) {
		$data = [ 'operation' => 'CREATE TABLE IF NOT EXISTS', 'prefix' => $prefix ];
		return $this->renderQuery( $table, $data );
	}

}