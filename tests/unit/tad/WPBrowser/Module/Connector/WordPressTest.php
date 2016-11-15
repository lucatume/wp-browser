<?php
namespace tad\WPBrowser\Module\Connector;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use tad\WPBrowser\Connector\WordPress;
use tad\WPBrowser\Module\Support\UriToIndexMapper;

class WordPressTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var array
	 */
	protected $server = [];

	/**
	 * @var History
	 */
	protected $history;

	/**
	 * @var CookieJar
	 */
	protected $cookieJar;

	/**
	 * @var UriToIndexMapper
	 */
	protected $uriToIndexMapper;

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

		$this->assertInstanceOf(WordPress::class, $sut);
	}

	/**
	 * @return WordPress
	 */
	private function make_instance()
	{
		return new WordPress($this->server, $this->history->reveal(), $this->cookieJar->reveal(),
			$this->uriToIndexMapper->reveal());
	}

	/**
	 * @test
	 * it should allow setting the url
	 */
	public function it_should_allow_setting_the_url()
	{
		$sut = $this->make_instance();

		$sut->setDomain('http://some-url.dev');

		$this->assertEquals('http://some-url.dev', $sut->getDomain());
	}

	/**
	 * @test
	 * it should allow setting the domain
	 */
	public function it_should_allow_setting_the_domain()
	{
		$sut = $this->make_instance();

		$sut->setDomain('some-domain.dev');


		$this->assertEquals('some-domain.dev', $sut->getDomain());
	}

	/**
	 * @test
	 * it should allow setting the headers
	 */
	public function it_should_allow_setting_the_headers()
	{
		$sut = $this->make_instance();

		$headers = ['foo' => 'bar', 'baz' => 'foo'];
		$sut->setHeaders($headers);

		$this->assertEquals($headers, $sut->getHeaders());
	}

	/**
	 * @test
	 * it should allow setting the root folder
	 */
	public function it_should_allow_setting_the_root_folder()
	{
		$sut = $this->make_instance();

		$sut->setRootFolder(__DIR__);

		$this->assertEquals(__DIR__, $sut->getRootFolder());
	}

	/**
	 * @test
	 * it should throw if set root folder does not exist
	 */
	public function it_should_throw_if_set_root_folder_does_not_exist()
	{
		$sut = $this->make_instance();

		$this->expectException(\InvalidArgumentException::class);

		$sut->setRootFolder('some-folder');
	}

	/**
	 * @test
	 * it should throw if set root folder is not folder
	 */
	public function it_should_throw_if_set_root_folder_is_not_folder()
	{
		$sut = $this->make_instance();

		$this->expectException(\InvalidArgumentException::class);

		$sut->setRootFolder(__FILE__);
	}

	/**
	 * @test
	 * it should set index with uri to index map when setting index for uri
	 */
	public function it_should_set_index_with_uri_to_index_map_when_setting_index_for_uri()
	{
		$uri = '/foo';
		$this->uriToIndexMapper->setRoot($this->root->url())->shouldBeCalled();
		$this->uriToIndexMapper->getIndexForUri($uri)->willReturn('/some-index.php');

		$sut = $this->make_instance();
		$sut->setRootFolder($this->root->url());
		$sut->setIndexFor($uri);

		$this->assertEquals($this->root->url() . '/some-index.php', $sut->getIndex());
	}

	protected function _before()
	{
		$this->server = [];
		$this->history = $this->prophesize(History::class);
		$this->cookieJar = $this->prophesize(CookieJar::class);
		$this->uriToIndexMapper = $this->prophesize(UriToIndexMapper::class);

		$this->root = vfsStream::setup();
	}

	protected function _after()
	{
	}
}