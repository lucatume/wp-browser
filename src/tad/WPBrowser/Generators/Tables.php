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

	public static function newBlogTables() {
		return [
			'commentmeta',
			'comments',
			'links',
			'options',
			'postmeta',
			'posts',
			'term_relationships',
			'term_taxonomy',
			'termmeta',
			'terms'
		];
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
			'drop-blog-tables' => function(){
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'drop-blog-tables.handlebars' ) );
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
			},
			'new-blog'         => function () {
				return file_get_contents( $this->templatesDir . DIRECTORY_SEPARATOR . ( 'new-blog.handlebars' ) );
			}
		];

		return $map[$table]();
	}

	public function getCreateTableQuery( $table, $prefix ) {
		$data = [ 'operation' => 'CREATE TABLE IF NOT EXISTS', 'prefix' => $prefix ];
		return $this->renderQuery( $table, $data );
	}

	public function getBlogScaffoldQuery( $prefix, $blogId, array $data ) {
		$template = $this->templates( 'new-blog' );
		$data     = array_merge( [
			'prefix'  => $prefix,
			'blog_id' => $blogId,
			'scheme'  => 'http'
		], $data );

		return $this->handlebars->render( $template, $data );
	}

	public function getBlogDropQuery( $tablePrefix, $blogId ) {
		$template = $this->templates( 'drop-blog-tables' );
		$data     = [
			'prefix'  => $tablePrefix,
			'blog_id' => $blogId
		];

		return $this->handlebars->render( $template, $data );
	}

}