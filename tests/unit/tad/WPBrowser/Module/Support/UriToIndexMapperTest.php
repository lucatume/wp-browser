<?php
namespace tad\WPBrowser\Module\Support;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class UriToIndexMapperTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var vfsStreamDirectory
	 */
	protected $root;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf(UriToIndexMapper::class, $sut);
	}

	/**
	 * @return UriToIndexMapper
	 */
	private function make_instance()
	{
		return new UriToIndexMapper($this->root->url());
	}

	/**
	 * @test
	 * it should map site root to root index file
	 */
	public function it_should_map_site_root_to_root_index_file()
	{
		$sut = $this->make_instance();

		$uri = '/';
		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/index.php';
		$this->assertEquals($expected, $indexFile);
	}

	/**
	 * @test
	 * it should return the index file when passing root with params
	 */
	public function it_should_return_the_index_file_when_passing_root_with_params()
	{
		$sut = $this->make_instance();

		$uri = '/?a=b&b=c&c=d';
		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/index.php';
		$this->assertEquals($expected, $indexFile);
	}

	/**
	 * @test
	 * it should return index file when uri is empty
	 */
	public function it_should_return_index_file_when_uri_is_empty()
	{
		$sut = $this->make_instance();

		$uri = '';
		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/index.php';
		$this->assertEquals($expected, $indexFile);
	}

	/**
	 * @test
	 * it should return the login file when trying to reach wp-login.php
	 */
	public function it_should_return_the_login_file_when_trying_to_reach_wp_login_php()
	{
		$sut = $this->make_instance();

		$uri = '/wp-login.php';
		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/wp-login.php';
		$this->assertEquals($expected, $indexFile);
	}

	public function loginAndParams()
	{
		return [
			['/wp-login.php?some=param'],
			['/wp-login.php?some=param&another=param'],
			['/wp-login.php?some=param&another=param&more=param'],
			['wp-login.php/?some=param'],
			['wp-login.php/?some=param&another=param'],
			['wp-login.php/?some=param&another=param&more=param'],
			['/wp-login.php?redirect_to=%2Fwp-admin%2Fpost.php%3Fpost%3D1%26action%3Dedit&reauth=1']
		];
	}

	/**
	 * @test
	 * it should return login when params are appended to it
	 * @dataProvider loginAndParams
	 */
	public function it_should_return_login_when_params_are_appended_to_it($uri)
	{
		$sut = $this->make_instance();

		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/wp-login.php';
		$this->assertEquals($expected, $indexFile);
	}

	public function cronUris()
	{
		return [
			['wp-cron.php'],
			['/wp-cron.php'],
			['/wp-cron.php?some=param'],
			['/wp-cron.php/?some=param'],
			['/wp-cron.php?some=param&another=param'],
			['/wp-cron.php/?some=param&another=param']
		];
	}

	/**
	 * @test
	 * it should properly map wp-cron.php
	 * @dataProvider cronUris
	 */
	public function it_should_properly_map_wp_cron_php($uri)
	{
		$sut = $this->make_instance();

		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/wp-cron.php';
		$this->assertEquals($expected, $indexFile);
	}

	public function realFiles()
	{
		return [
			['some-file.php', '/some-file.php'],
			['another-file.php', '/another-file.php'],
			['/some-file.php', '/some-file.php'],
			['/another-file.php', '/another-file.php'],
			['/subfolder/file-1.php', '/subfolder/file-1.php'],
			['/subfolder/file-2.php', '/subfolder/file-2.php'],
			['subfolder/file-1.php', '/subfolder/file-1.php'],
			['subfolder/file-2.php', '/subfolder/file-2.php'],
			['some-file.php?some=param', '/some-file.php'],
			['another-file.php?some=param', '/another-file.php'],
			['/some-file.php/?some=param', '/some-file.php'],
			['/another-file.php/?some=param', '/another-file.php'],
			['/subfolder/file-1.php?some=param', '/subfolder/file-1.php'],
			['/subfolder/file-2.php?some=param', '/subfolder/file-2.php'],
			['subfolder/file-1.php/?some=param', '/subfolder/file-1.php'],
			['subfolder/file-2.php/?some=param', '/subfolder/file-2.php'],
			['some-file.php?some=param&another=param', '/some-file.php'],
			['another-file.php?some=param&another=param', '/another-file.php'],
			['/some-file.php/?some=param&another=param', '/some-file.php'],
			['/another-file.php/?some=param&another=param', '/another-file.php'],
			['/subfolder/file-1.php?some=param&another=param', '/subfolder/file-1.php'],
			['/subfolder/file-2.php?some=param&another=param', '/subfolder/file-2.php'],
			['subfolder/file-1.php/?some=param&another=param', '/subfolder/file-1.php'],
			['subfolder/file-2.php/?some=param&another=param', '/subfolder/file-2.php'],
		];
	}

	/**
	 * @test
	 * it should map existing files to the files
	 * @dataProvider realFiles
	 */
	public function it_should_map_existing_files_to_the_files($uri, $expected)
	{
		$this->root = vfsStream::copyFromFileSystem(codecept_data_dir('folder-structures/wp-root-folder-1'));

		$sut = $this->make_instance();

		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . $expected;
		$this->assertEquals($expected, $indexFile);
	}

	public function nonExistingFiles()
	{
		return [
			['foo.php'],
			['/foo.php'],
			['foo/bar.php'],
			['/foo/bar.php'],
			['foo.php?some=param'],
			['/foo.php?some=param'],
			['foo/bar.php?some=param'],
			['/foo/bar.php?some=param'],
			['foo.php/?some=param'],
			['/foo.php/?some=param'],
			['foo/bar.php/?some=param'],
			['/foo/bar.php/?some=param'],
			['foo.php?some=param&another=param'],
			['/foo.php?some=param&another=param'],
			['foo/bar.php?some=param&another=param'],
			['/foo/bar.php?some=param&another=param'],
			['foo.php/?some=param&another=param'],
			['/foo.php/?some=param&another=param'],
			['foo/bar.php/?some=param&another=param'],
			['/foo/bar.php/?some=param&another=param'],
		];
	}

	/**
	 * @test
	 * it should map non existing files to main index file
	 * @dataProvider nonExistingFiles
	 */
	public function it_should_map_non_existing_files_to_main_index_file($uri)
	{
		$this->root = vfsStream::copyFromFileSystem(codecept_data_dir('folder-structures/wp-root-folder-1'));

		$sut = $this->make_instance();

		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/index.php';
		$this->assertEquals($expected, $indexFile);
	}

	public function prettyPermalinks()
	{
		return [
			['some'],
			['/some'],
			['some/path'],
			['/some/path'],
			['some/deeper/path'],
			['/some/deeper/path'],
			['some?some=param'],
			['/some?some=param'],
			['some/path?some=param'],
			['/some/path?some=param'],
			['some/deeper/path?some=param'],
			['/some/deeper/path?some=param'],
			['some?some=param&another=param'],
			['/some?some=param&another=param'],
			['some/path?some=param&another=param'],
			['/some/path?some=param&another=param'],
			['some/deeper/path?some=param&another=param'],
			['/some/deeper/path?some=param&another=param'],
			['some/?some=param'],
			['/some/?some=param'],
			['some/path/?some=param'],
			['/some/path/?some=param'],
			['some/deeper/path/?some=param'],
			['/some/deeper/path/?some=param'],
			['some/?some=param&another=param'],
			['/some/?some=param&another=param'],
			['some/path/?some=param&another=param'],
			['/some/path/?some=param&another=param'],
			['some/deeper/path/?some=param&another=param'],
			['/some/deeper/path/?some=param&another=param'],
		];
	}

	/**
	 * @test
	 * it should map pretty permalinks to root index file
	 * @dataProvider prettyPermalinks
	 */
	public function it_should_map_pretty_permalinks_to_root_index_file($uri)
	{
		$this->root = vfsStream::copyFromFileSystem(codecept_data_dir('folder-structures/wp-root-folder-1'));

		$sut = $this->make_instance();

		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/index.php';
		$this->assertEquals($expected, $indexFile);
	}

	public function adminUris()
	{
		return [
			['/wp-admin'],
			['wp-admin'],
			['wp-admin/'],
			['/wp-admin/'],
		];
	}

	/**
	 * @test
	 * it should map wp-admin to wp-admin/index.php
	 * @dataProvider adminUris
	 */
	public function it_should_map_wp_admin_to_wp_admin_index_php($uri)
	{
		$sut = $this->make_instance();

		$indexFile = $sut->getIndexForUri($uri);

		$expected = $this->root->url() . '/wp-admin/index.php';
		$this->assertEquals($expected, $indexFile);
	}

	protected function _before()
	{
		$this->root = vfsStream::setup();
	}

	protected function _after()
	{
	}
}