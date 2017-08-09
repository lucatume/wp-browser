<?php
namespace tad\WPBrowser\Extension;


use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use org\bovigo\vfs\vfsStream;

class CopierTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var SuiteEvent
	 */
	protected $suiteEvent;

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf(Copier::class, $sut);
	}

	/**
	 * @return Copier
	 */
	private function make_instance()
	{
		return new Copier($this->config, $this->options);
	}

	/**
	 * @test
	 * it should throw if source file does not exist
	 */
	public function it_should_throw_if_source_file_does_not_exist()
	{
		$this->config = [
			'files' => [
				__DIR__ . '/one' => __DIR__ . '/two'
			]
		];

		$this->expectException(ExtensionException::class);


		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if source file is not readable
	 */
	public function it_should_throw_if_source_file_is_not_readable()
	{
		$root = vfsStream::setup();
		$root->addChild(vfsStream::newFile('some-file', 0000));
		$this->config = [
			'files' => [
				$root->url() . '/some-file' => __DIR__ . '/two'
			]
		];

		$this->expectException(ExtensionException::class);


		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if destination folder does not exist
	 */
	public function it_should_throw_if_destination_folder_does_not_exist()
	{
		$root = vfsStream::setup();
		$root->addChild(vfsStream::newFile('some-file', 0777));
		$this->config = [
			'files' => [
				$root->url() . '/some-file' => $root->url() . '/some-folder/another-file'
			]
		];

		$this->expectException(ExtensionException::class);


		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if destination folder is not writeable
	 */
	public function it_should_throw_if_destination_folder_is_not_writeable()
	{
		$root = vfsStream::setup();
		$root->addChild(vfsStream::newFile('some-file', 0777));
		$root->addChild(vfsStream::newFile('another-file', 0777));
		$destFolder = vfsStream::newDirectory('destination', 0000);
		$root->addChild($destFolder);
		$this->config = [
			'files' => [
				$root->url() . '/some-file' => $root->url() . '/destination/some-file'
			]
		];

		$this->expectException(ExtensionException::class);


		$this->make_instance();
	}

	/**
	 * @test
	 * it should write source file to destination file
	 */
	public function it_should_write_source_file_to_destination_file()
	{
		$root = vfsStream::setup();
		$file = vfsStream::newFile('some-file', 0777);
		$file->setContent('foo bar');
		$root->addChild($file);
		$destFolder = vfsStream::newDirectory('destination', 0777);
		$root->addChild($destFolder);
		$destinationFile = $root->url() . '/destination/some-file';
		$this->config = [
			'files' => [
				$root->url() . '/some-file' => $destinationFile
			]
		];

		$sut = $this->make_instance();

		$sut->copyFiles();

		$this->assertFileExists($destinationFile);
		$this->assertEquals('foo bar', file_get_contents($destinationFile));
	}

	/**
	 * @test
	 * it should write source folder to destination folder
	 */
	public function it_should_write_source_folder_to_destination_folder()
	{
		$root = vfsStream::setup('root', 0777, [
			'folder' => [
				'some-file' => 'some file content',
				'sub-folder' => [
					'some-sub-file' => 'some sub file content',
					'sub-folder-2' => [
						'some-sub-file-2' => 'some sub file 2 content'
					]
				]
			],
			'destination' => []
		]);
		$destFolder = vfsStream::newDirectory('destination', 0777);
		$root->addChild($destFolder);
		$destination = $root->url() . '/destination/folder';
		$this->config = [
			'files' => [
				$root->url() . '/folder' => $destination
			]
		];

		$sut = $this->make_instance();

		$sut->copyFiles();

		$this->assertFileExists($destination);
		$this->assertFileExists($destination . '/some-file');
		$this->assertEquals('some file content', file_get_contents($destination . '/some-file'));
		$this->assertFileExists($destination . '/sub-folder');
		$this->assertFileExists($destination . '/sub-folder/some-sub-file');
		$this->assertEquals('some sub file content', file_get_contents($destination . '/sub-folder/some-sub-file'));
		$this->assertFileExists($destination . '/sub-folder/sub-folder-2');
		$this->assertFileExists($destination . '/sub-folder/sub-folder-2/some-sub-file-2');
		$this->assertEquals('some sub file 2 content',
			file_get_contents($destination . '/sub-folder/sub-folder-2/some-sub-file-2'));
	}

	/**
	 * @test
	 * it should copy multiple files and structures
	 */
	public function it_should_copy_multiple_files_and_structures()
	{
		$root = vfsStream::setup('root', 0777, [
			'src-folder' => [
				'some-file' => 'some file content',
				'sub-src-folder' => [
					'some-sub-file' => 'some sub file content',
					'sub-src-folder-2' => [
						'some-sub-file-2' => 'some sub file 2 content'
					]
				]
			],
			'fileTwo' => 'file 2 content',
			'src-folder-two' => [
				'some-file' => 'some file content',
				'sub-src-folder' => [
					'some-sub-file' => 'some sub file content',
					'sub-src-folder-2' => [
						'some-sub-file-2' => 'some sub file 2 content'
					]
				]
			],
			'destination' => []
		]);
		$destFolder = vfsStream::newDirectory('destination', 0777);
		$root->addChild($destFolder);
		$destination = $root->url() . '/destination/src-folder';
		$destinationTwo = $root->url() . '/destination/file2';
		$destinationThree = $root->url() . '/destination/folder2';
		$this->config = [
			'files' => [
				$root->url() . '/src-folder' => $destination,
				$root->url() . '/fileTwo' => $destinationTwo,
				$root->url() . '/src-folder-two' => $destinationThree,
			]
		];

		$sut = $this->make_instance();

		$sut->copyFiles();

		$this->assertFileExists($destinationTwo);
		$this->assertEquals('file 2 content', file_get_contents($destinationTwo));


		$this->assertFileExists($destination);
		$this->assertFileExists($destination . '/some-file');
		$this->assertEquals('some file content', file_get_contents($destination . '/some-file'));
		$this->assertFileExists($destination . '/sub-src-folder');
		$this->assertFileExists($destination . '/sub-src-folder/some-sub-file');
		$this->assertEquals('some sub file content', file_get_contents($destination . '/sub-src-folder/some-sub-file'));
		$this->assertFileExists($destination . '/sub-src-folder/sub-src-folder-2');
		$this->assertFileExists($destination . '/sub-src-folder/sub-src-folder-2/some-sub-file-2');
		$this->assertEquals('some sub file 2 content',
			file_get_contents($destination . '/sub-src-folder/sub-src-folder-2/some-sub-file-2'));

		$this->assertFileExists($destinationThree);
		$this->assertFileExists($destinationThree . '/some-file');
		$this->assertEquals('some file content', file_get_contents($destinationThree . '/some-file'));
		$this->assertFileExists($destinationThree . '/sub-src-folder');
		$this->assertFileExists($destinationThree . '/sub-src-folder/some-sub-file');
		$this->assertEquals('some sub file content',
			file_get_contents($destinationThree . '/sub-src-folder/some-sub-file'));
		$this->assertFileExists($destinationThree . '/sub-src-folder/sub-src-folder-2');
		$this->assertFileExists($destinationThree . '/sub-src-folder/sub-src-folder-2/some-sub-file-2');
		$this->assertEquals('some sub file 2 content',
			file_get_contents($destinationThree . '/sub-src-folder/sub-src-folder-2/some-sub-file-2'));
	}

	/**
	 * @test
	 * it should allow for relative path in source in respect to cwd
	 */
	public function it_should_allow_for_relative_path_in_source_in_respect_to_cwd()
	{
		$root = vfsStream::setup('root', 0777, ['destination' => []]);
		$destination = $root->url() . '/destination';
		$this->config = [
			'files' => [
				'tests/_data/some-file' => $destination . '/some-file',
				'tests/_data/some-folder' => $destination . '/some-folder',
			]
		];

		$sut = $this->make_instance();

		$sut->copyFiles();

		$this->assertFileExists($destination . '/some-file');
		$this->assertFileExists($destination . '/some-folder');
		$this->assertFileExists($destination . '/some-folder/some-file');
	}

	/**
	 * @test
	 * it should allow for relative destinations
	 */
	public function it_should_allow_for_relative_destinations()
	{
		mkdir(codecept_data_dir('destination'), 0777, true);

		$this->config = [
			'files' => [
				codecept_data_dir('some-file') => 'tests/_data/destination/some-file',
				codecept_data_dir('some-folder') => 'tests/_data/destination/some-folder',
			]
		];

		$sut = $this->make_instance();

		$sut->copyFiles();

		$destination = codecept_data_dir('destination');
		$this->assertFileExists($destination . '/some-file');
		$this->assertFileExists($destination . '/some-folder');
		$this->assertFileExists($destination . '/some-folder/some-file');
	}

	protected function _after()
	{
		$destination = codecept_data_dir('destination');
		if (file_exists($destination)) {
			rrmdir($destination);
		}
	}
}