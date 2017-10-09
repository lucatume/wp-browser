<?php

namespace Codeception\Module;

use cli\Arguments;
use Codeception\Lib\Driver\ExtendedMySql;
use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use tad\WPBrowser\Module\Support\DbDump;

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
	 * @var DbDump
	 */
	protected $dbDump;

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
		return new WPDb($this->moduleContainer->reveal(), $this->config, $this->dbDump->reveal());
	}

	/**
	 * It should allow specifying a dump file to import
	 *
	 * @test
	 */
	public function it_should_allow_specifying_a_dump_file_to_import() {
		$root    = vfsStream::setup('root');
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
		$root    = vfsStream::setup('root');
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
		$this->config          = [
			'dsn'         => 'some-dsn',
			'user'        => 'some-user',
			'password'    => 'some-password',
			'url'         => 'http://some-wp.dev',
			'tablePrefix' => 'wp_',
		];
		$this->dbDump = $this->prophesize(DbDump::class);
	}

	/**
	 * It should not try to replace the site url in the dump if url replacement is false
	 *
	 * @test
	 */
	public function should_not_try_to_replace_the_site_url_in_the_dump_if_url_replacement_is_false() {
		$this->config = [
			'dsn'            => 'some-dsn',
			'user'           => 'some-user',
			'password'       => 'some-password',
			'url'            => 'http://some-wp.dev',
			'tablePrefix'    => 'wp_',
			'urlReplacement' => false,
			'dump'           => 'some-sql',
			'populate'       => true,
		];

		$this->dbDump->replaceSiteDomainInSqlString(Argument::any(),Argument::any())->shouldNotBeCalled();
		$this->dbDump->replaceSiteDomainInMultisiteSqlString(Argument::any(),Argument::any())->shouldNotBeCalled();

		$sut = $this->make_instance();

		$sut->_replaceUrlInDump('foo-bar');
	}
}