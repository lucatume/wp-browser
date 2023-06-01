<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use UnitTester;


class CopierTest extends Unit
{
    use UopzFunctions;

    protected $backupGlobals = false;
    /**
     * @var UnitTester
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
    public function it_should_be_instantiatable(): void
    {
        $sut = $this->makeInstance();

        $this->assertInstanceOf(Copier::class, $sut);
    }

    /**
     * @return Copier
     */
    private function makeInstance(): Copier
    {
        return new Copier($this->config, $this->options);
    }

    /**
     * @test
     * it should throw if source file does not exist
     */
    public function it_should_throw_if_source_file_does_not_exist(): void
    {
        $this->config = [
            'files' => [
                __DIR__ . '/one' => __DIR__ . '/two'
            ]
        ];

        $this->expectException(ExtensionException::class);


        $this->makeInstance();
    }

    /**
     * @test
     * it should throw if source file is not readable
     */
    public function it_should_throw_if_source_file_is_not_readable(): void
    {
        $root = FS::tmpDir('copier_', [
            'some-file' => 'some content'
        ]);
        $filePath = $root . '/some-file';
        $this->uopzSetFunctionReturn('is_readable', static function (string $file) use ($filePath) {
            return $file !== $filePath && is_readable($file);
        }, true);
        $this->config = [
            'files' => [
                $filePath => __DIR__ . '/two'
            ]
        ];

        $this->expectException(ExtensionException::class);

        $this->makeInstance();
    }

    /**
     * @test
     * it should throw if destination folder does not exist
     */
    public function it_should_throw_if_destination_folder_does_not_exist(): void
    {
        $root = FS::tmpDir('copier_', [
            'some-file' => 'some content'
        ]);
        $filePath = $root . '/some-file';
        $this->config = [
            'files' => [
                $filePath => $root . '/some-folder/another-file'
            ]
        ];

        $this->expectException(ExtensionException::class);

        $this->makeInstance();
    }

    /**
     * @test
     * it should throw if destination folder is not writeable
     */
    public function it_should_throw_if_destination_folder_is_not_writeable(): void
    {
        $root = FS::tmpDir('copier_', [
            'some-file' => 'some content',
            'some-other-file' => 'some other content',
            'destination-dir' => []
        ]);
        $this->uopzSetFunctionReturn('is_writable', static function (string $file) use ($root) {
            return $file !== $root . '/destination-dir' && is_writable($file);
        }, true);
        $this->config = [
            'files' => [
                $root . '/some-file' => $root . '/destination-dir/some-file'
            ]
        ];

        $this->expectException(ExtensionException::class);

        $this->makeInstance();
    }

    /**
     * @test
     * it should write source file to destination file
     */
    public function it_should_write_source_file_to_destination_file(): void
    {
        $root = FS::tmpDir('copier_', [
            'some-file' => 'foo bar',
            'destination-dir' => []
        ]);
        $destinationFile = $root . '/destination-dir/some-file';
        $this->config = [
            'files' => [
                $root . '/some-file' => $destinationFile
            ]
        ];

        $sut = $this->makeInstance();

        $sut->copyFiles();

        $this->assertFileExists($destinationFile);
        $this->assertEquals('foo bar', file_get_contents($destinationFile));
    }

    /**
     * @test
     * it should write source folder to destination folder
     */
    public function it_should_write_source_folder_to_destination_folder(): void
    {
        $root = FS::tmpDir('copier_', [
            'some-dir' => [
                'some-file' => 'some file content',
                'sub-folder' => [
                    'some-sub-file' => 'some sub file content',
                    'sub-folder-2' => [
                        'some-sub-file-2' => 'some sub file 2 content'
                    ]
                ]
            ],
            'destination-dir' => []
        ]);
        $destination = $root . '/destination-dir/some-dir';
        $this->config = [
            'files' => [
                $root . '/some-dir' => $destination
            ]
        ];

        $sut = $this->makeInstance();

        $sut->copyFiles();

        $this->assertFileExists($destination);
        $this->assertFileExists($destination . '/some-file');
        $this->assertEquals('some file content', file_get_contents($destination . '/some-file'));
        $this->assertFileExists($destination . '/sub-folder');
        $this->assertFileExists($destination . '/sub-folder/some-sub-file');
        $this->assertEquals('some sub file content', file_get_contents($destination . '/sub-folder/some-sub-file'));
        $this->assertFileExists($destination . '/sub-folder/sub-folder-2');
        $this->assertFileExists($destination . '/sub-folder/sub-folder-2/some-sub-file-2');
        $this->assertEquals(
            'some sub file 2 content',
            file_get_contents($destination . '/sub-folder/sub-folder-2/some-sub-file-2')
        );
    }

    /**
     * @test
     * it should copy multiple files and structures
     */
    public function it_should_copy_multiple_files_and_structures(): void
    {
        $root = FS::tmpDir('copier_', [
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
        $destinationOne = $root . '/destination/src-folder';
        $destinationTwo = $root . '/destination/file2';
        $destinationThree = $root . '/destination/folder2';
        $this->config = [
            'files' => [
                $root . '/src-folder' => $destinationOne,
                $root . '/fileTwo' => $destinationTwo,
                $root . '/src-folder-two' => $destinationThree,
            ]
        ];

        $sut = $this->makeInstance();

        $sut->copyFiles();

        $this->assertFileExists($destinationTwo);
        $this->assertEquals('file 2 content', file_get_contents($destinationTwo));


        $this->assertFileExists($destinationOne);
        $this->assertFileExists($destinationOne . '/some-file');
        $this->assertEquals('some file content', file_get_contents($destinationOne . '/some-file'));
        $this->assertFileExists($destinationOne . '/sub-src-folder');
        $this->assertFileExists($destinationOne . '/sub-src-folder/some-sub-file');
        $this->assertEquals('some sub file content',
            file_get_contents($destinationOne . '/sub-src-folder/some-sub-file'));
        $this->assertFileExists($destinationOne . '/sub-src-folder/sub-src-folder-2');
        $this->assertFileExists($destinationOne . '/sub-src-folder/sub-src-folder-2/some-sub-file-2');
        $this->assertEquals(
            'some sub file 2 content',
            file_get_contents($destinationOne . '/sub-src-folder/sub-src-folder-2/some-sub-file-2')
        );

        $this->assertFileExists($destinationThree);
        $this->assertFileExists($destinationThree . '/some-file');
        $this->assertEquals('some file content', file_get_contents($destinationThree . '/some-file'));
        $this->assertFileExists($destinationThree . '/sub-src-folder');
        $this->assertFileExists($destinationThree . '/sub-src-folder/some-sub-file');
        $this->assertEquals(
            'some sub file content',
            file_get_contents($destinationThree . '/sub-src-folder/some-sub-file')
        );
        $this->assertFileExists($destinationThree . '/sub-src-folder/sub-src-folder-2');
        $this->assertFileExists($destinationThree . '/sub-src-folder/sub-src-folder-2/some-sub-file-2');
        $this->assertEquals(
            'some sub file 2 content',
            file_get_contents($destinationThree . '/sub-src-folder/sub-src-folder-2/some-sub-file-2')
        );
    }

    /**
     * @test
     * it should allow for relative path in source in respect to cwd
     */
    public function it_should_allow_for_relative_path_in_source_in_respect_to_cwd(): void
    {
        $root = FS::tmpDir('copier_', [
            'destination' => []
        ]);
        $destination = $root . '/destination';
        $this->config = [
            'files' => [
                'tests/_data/some-file' => $destination . '/some-file',
                'tests/_data/some-folder' => $destination . '/some-folder',
            ]
        ];

        $sut = $this->makeInstance();

        $sut->copyFiles();

        $this->assertFileExists($destination . '/some-file');
        $this->assertFileExists($destination . '/some-folder');
        $this->assertFileExists($destination . '/some-folder/some-file');
    }

    /**
     * @test
     * it should allow for relative destinations
     */
    public function it_should_allow_for_relative_destinations(): void
    {
        $root = FS::tmpDir('copier_', [
            'some-file' => 'some file content',
            'some-folder' => [
                'some-file' => 'some other file content'
            ],
            'destination' => []
        ]);
        $rootRelativePath = FS::relativePath(getcwd(), $root);

        $this->config = [
            'files' => [
                $root . '/some-file' => $rootRelativePath . '/destination/some-file',
                $root . '/some-folder' => $rootRelativePath . '/destination/some-folder',
            ]
        ];

        $sut = $this->makeInstance();

        $sut->copyFiles();

        $destination = $root . '/destination';
        $this->assertFileExists($destination . '/some-file');
        $this->assertFileExists($destination . '/some-folder');
        $this->assertFileExists($destination . '/some-folder/some-file');
    }
}
