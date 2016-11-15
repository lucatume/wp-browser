<?php

namespace Codeception\Command\Tests\Unit;


use Codeception\Command\DbSnapshot;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DbSnapshotTest extends \PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;

	/**
	 * @test
	 * it should require the database name
	 */
	public function it_should_require_the_database_name()
	{
		$application = new Application();
		$application->add(new DbSnapshot());

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$this->expectException('Symfony\Component\Console\Exception\RuntimeException');

		$commandTester->execute([
			'command' => $command->getName(),
		]);
	}

	/**
	 * @test
	 * it should require a snapshot name
	 */
	public function it_should_require_a_snapshot_name()
	{
		$application = new Application();
		$application->add(new DbSnapshot());

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$this->expectException('Symfony\Component\Console\Exception\RuntimeException');

		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'someDb'
		]);
	}

	/**
	 * @test
	 * it should use default values to connect to the database
	 */
	public function it_should_use_default_values_to_connect_to_the_database()
	{
		$application = new Application();
		/** @var \tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface $pdoFactory */
		$pdoFactory = $this->prophesize('\tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface');
		$expectedHost = 'localhost';
		$expectedUser = 'root';
		$expectedPassword = 'root';
		$expectedDb = 'db';
		$pdoFactory->makeDump($expectedHost, $expectedUser, $expectedPassword, $expectedDb)->willReturn(false);
		$application->add(new DbSnapshot(null, $pdoFactory->reveal()));

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'db',
			'snapshot' => 'someSnapshot'
		]);
	}

	/**
	 * @test
	 * it should throw if db connection fails
	 */
	public function it_should_throw_if_pdo_connection_fails()
	{
		$application = new Application();
		/** @var \tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface $factory */
		$factory = $this->prophesize('\tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface');
		$factory->makeDump(Argument::type('string'), Argument::type('string'), Argument::type('string'),
			Argument::type('string'))->willThrow(new \PDOException());
		$application->add(new DbSnapshot(null, $factory->reveal()));

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$this->expectException('\Symfony\Component\Console\Exception\RuntimeException');

		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'db',
			'snapshot' => 'someSnapshot'
		]);
	}

	/**
	 * @test
	 * it should dump the database to the data folder by default
	 */
	public function it_should_dump_the_database_to_the_data_folder_by_default()
	{
		$expectedDump = codecept_data_dir('issue4455.sql');
		$expectedDistDump = codecept_data_dir('issue4455.dist.sql');

		$application = new Application();
		/** @var \tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface $factory */
		$factory = $this->prophesize('\tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface');
		$dump = $this->prophesize('MySQLDump');
		$dump->write(Argument::any())->shouldBeCalled();
		$filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		$filesystem->file_put_contents($expectedDump, Argument::type('string'))->willReturn(true);
		$filesystem->file_put_contents($expectedDistDump, Argument::type('string'))->willReturn(true);
		$factory->makeDump(Argument::type('string'), Argument::type('string'), Argument::type('string'),
			Argument::type('string'))->willReturn($dump->reveal());
		$application->add(new DbSnapshot(null, $factory->reveal(), $filesystem->reveal()));

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'db',
			'snapshot' => 'issue4455'
		]);
	}

	/**
	 * @test
	 * it should allow specifying dump and dist file names
	 */
	public function it_should_allow_specifying_dump_and_dist_file_names()
	{
		$root = vfsStream::setup('dumps');
		$root->addChild(new vfsStreamFile('dump.sql'));
		$root->addChild(new vfsStreamFile('dump.dist.sql'));

		$expectedDump = $root->url() . '/dump.sql';
		$expectedDistDump = $root->url() . '/dump.dist.sql';

		$application = new Application();
		/** @var \tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface $factory */
		$factory = $this->prophesize('\tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface');
		$dump = $this->prophesize('MySQLDump');
		$dump->write(Argument::any())->shouldBeCalled();
		$filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		$filesystem->file_put_contents($expectedDump, Argument::type('string'))->willReturn(true);
		$filesystem->file_put_contents($expectedDistDump, Argument::type('string'))->willReturn(true);
		$factory->makeDump(Argument::type('string'), Argument::type('string'), Argument::type('string'),
			Argument::type('string'))->willReturn($dump->reveal());
		$application->add(new DbSnapshot(null, $factory->reveal(), $filesystem->reveal()));

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'db',
			'snapshot' => 'issue4455',
			'--dump-file' => $expectedDump,
			'--dist-dump-file' => $expectedDistDump
		]);
	}

	/**
	 * @test
	 * it should allow specifying the tables to skip in the dump
	 */
	public function it_should_allow_specifying_the_tables_to_skip_in_the_dump()
	{
		$root = vfsStream::setup('dumps');
		$root->addChild(new vfsStreamFile('dump.sql'));
		$root->addChild(new vfsStreamFile('dump.dist.sql'));

		$expectedDump = $root->url() . '/dump.sql';
		$expectedDistDump = $root->url() . '/dump.dist.sql';

		$application = new Application();
		/** @var \tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface $factory */
		$factory = $this->prophesize('\tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface');
		$dump = $this->prophesize('MySQLDump');
		$dump->write(Argument::any())->shouldBeCalled();
		$filesystem = $this->prophesize('tad\WPBrowser\Filesystem\Filesystem');
		$filesystem->file_put_contents(Argument::type('string'), Argument::type('string'))->willReturn(true);
		$factory->makeDump(Argument::type('string'), Argument::type('string'), Argument::type('string'),
			Argument::type('string'))->willReturn($dump->reveal());
		$application->add(new DbSnapshot(null, $factory->reveal(), $filesystem->reveal()));

		$command = $application->find('db:snapshot');
		$commandTester = new CommandTester($command);

		$commandTester->execute([
			'command' => $command->getName(),
			'name' => 'db',
			'snapshot' => 'issue4455',
			'--dump-file' => $expectedDump,
			'--dist-dump-file' => $expectedDistDump,
			'--skip-tables' => 'foo,bar,baz'
		]);

		$dumpTables = $application->get('db:snapshot')->_getDumpTables();
		$this->assertArrayHasKey('foo', $dumpTables);
		$this->assertArrayHasKey('bar', $dumpTables);
		$this->assertArrayHasKey('baz', $dumpTables);
		$this->assertEquals(\MySQLDump::NONE, $dumpTables['foo']);
		$this->assertEquals(\MySQLDump::NONE, $dumpTables['bar']);
		$this->assertEquals(\MySQLDump::NONE, $dumpTables['baz']);
	}

}
