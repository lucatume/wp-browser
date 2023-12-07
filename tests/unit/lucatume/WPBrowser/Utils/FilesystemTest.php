<?php

namespace Unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use Generator;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem;
use RuntimeException;

class FilesystemTest extends Unit
{
    use TmpFilesCleanup;

    public function resolvePathDataSet(): array
    {
        return [
            ['', null, getcwd()],
            ['/', null, '/'],
            ['~', null, getenv('HOME')],
            [__DIR__, null, __DIR__],
            [__FILE__, null, __FILE__],
            [basename(__FILE__), __DIR__, __FILE__]
        ];
    }

    /**
     * Test resolvePath
     *
     * @dataProvider resolvePathDataSet
     */
    public function test_resolvePath($path, $root, $expected): void
    {
        $this->assertEquals($expected, Filesystem::resolvePath($path, $root));
    }

    public function test_resolvePath_from_dir(): void
    {
        $tmpDir = Filesystem::tmpDir();
        Filesystem::mkdirp(
            $tmpDir,
            [
                'Some Path' => [
                    'Some File' => 'Some Content',
                ]
            ]
        );

        $path = $tmpDir . '/Some\ Path/Some\ File';
        $root = null;
        $expected = $tmpDir . '/Some Path/Some File';
        $this->assertEquals($expected, Filesystem::resolvePath($path, $root));
    }

    public function findHereOrInParentDataSet(): array
    {
        return [
            ['/foo/bar/baz', '~', false],
            [basename(__FILE__), __DIR__, __FILE__],
            ['unit', __DIR__, codecept_root_dir('tests/unit')],
            ['foo-bar', __DIR__, false],
        ];
    }

    /**
     * Test findHereOrInParent
     *
     * @dataProvider findHereOrInParentDataSet
     */
    public function test_find_here_or_in_parent($path, $root, $expected): void
    {
        $this->assertEquals($expected, Filesystem::findHereOrInParent($path, $root));
    }

    public function test_rrmdir(): void
    {
        $root = codecept_output_dir('rrmdirTest');
        $createDirStruct = static function ($key, $value) use (&$createDirStruct) {
            if (is_array($value)) {
                if (!is_dir($key) && !mkdir($key) && !is_dir($key)) {
                    throw new RuntimeException("Could not create directory {$key}");
                }
                foreach ($value as $subKey => $subValue) {
                    $createDirStruct($key . '/' . $subKey, $subValue);
                }

                return;
            }

            if (!file_put_contents($key, $value)) {
                throw new RuntimeException("Could not put file contents in file {$key}");
            }
        };
        Filesystem::mkdirp($root, [
            'sub-dir-1' => [
                'delme.txt' => 'delme'
            ],
            'sub-dir-2' => [
                'sub-dir-2-1' => [
                    'index.txt' => 'test test test'
                ],
                'sub-dir-2-2' => [
                ]
            ],
            'sub-dir-3' => [
                'sub-dir-3-1' => [
                    'index.txt' => 'test test test'
                ],
                'sub-dir-3-2' => [
                    'index.txt' => 'test test test',
                    'sub-dir-3-2-1' => [
                        'index.txt' => 'test test test'
                    ]
                ],
                'sub-dir-3-3' => [
                    'index.txt' => 'test test test',
                    'sub-dir-3-3-1' => [
                        'index.txt' => 'test test test',
                        'sub-dir-3-3-1-1' => [
                            'index.txt' => 'test test test'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertDirectoryExists($root);
        $this->assertFileExists($root . '/sub-dir-1/delme.txt');
        $this->assertDirectoryExists($root . '/sub-dir-1');
        $this->assertDirectoryExists($root . '/sub-dir-2');
        $this->assertDirectoryExists($root . '/sub-dir-2/sub-dir-2-1');
        $this->assertDirectoryExists($root . '/sub-dir-2/sub-dir-2-2');
        $this->assertDirectoryExists($root . '/sub-dir-3');
        $this->assertDirectoryExists($root . '/sub-dir-3/sub-dir-3-1');
        $this->assertDirectoryExists($root . '/sub-dir-3/sub-dir-3-2');
        $this->assertDirectoryExists($root . '/sub-dir-3//sub-dir-3-2/sub-dir-3-2-1');
        $this->assertDirectoryExists($root . '/sub-dir-3/sub-dir-3-3');
        $this->assertDirectoryExists($root . '/sub-dir-3/sub-dir-3-3/sub-dir-3-3-1');
        $this->assertDirectoryExists($root . '/sub-dir-3/sub-dir-3-3/sub-dir-3-3-1/sub-dir-3-3-1-1');

        Filesystem::rrmdir($root . '/sub-dir-1/delme.txt');

        $this->assertFileNotExists($root . '/sub-dir-1/delme.txt');

        Filesystem::rrmdir($root);

        if (method_exists($this, 'assertDirectoryDoesNotExist')) {
            $this->assertDirectoryDoesNotExist($root);
        } else {
            $this->assertDirectoryNotExists($root);
        }
    }

    public function test_mkdirp_creates_nested_trees_wo_specifying_content(): void
    {
        $dir = codecept_output_dir('one/two/three/four');

        Filesystem::mkdirp($dir);

        $this->assertDirectoryExists(codecept_output_dir('one'));
        $this->assertDirectoryExists(codecept_output_dir('one/two'));
        $this->assertDirectoryExists(codecept_output_dir('one/two/three'));
        $this->assertDirectoryExists(codecept_output_dir('one/two/three/four'));
    }

    public function relativePathDataSet(): Generator
    {
        yield 'empty' => [function () {
            return ['', '', '/', ''];
        }];
        yield 'empty from, absolute to' => [function () {
            return ['', __DIR__, '/', __DIR__];
        }];
        yield 'empty to' => [function () {
            return [__DIR__, '', '/', ''];
        }];

        $makeTmpDir = static function (): string {
            $tmpDir = Filesystem::tmpDir();
            Filesystem::mkdirp($tmpDir, [
                'dir_1' => [
                    'dir_2_1' => [
                        'foo-file' => 'test',
                    ]
                ],
                'dir_2' => [
                    'dir_2_1' => [
                        'bar-file' => 'test',
                    ],
                ],
            ]);
            return $tmpDir;
        };

        yield 'siblings' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                return [$tmpDir . '/dir_1', $tmpDir . '/dir_2', '/', '../dir_2'];
            }
        ];
        yield 'siblings to sub-dir' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                return [$tmpDir . '/dir_1', $tmpDir . '/dir_2/dir_2_1', '/', '../dir_2/dir_2_1'];
            }
        ];
        yield 'siblings from sub-dir' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                return [$tmpDir . '/dir_2/dir_2_1', $tmpDir . '/dir_1', '/', '../../dir_1'];
            }
        ];

        yield 'siblings win' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                return [$tmpDir . '/dir_1', $tmpDir . '/dir_2', '\\', '..\dir_2'];
            }
        ];
        yield 'siblings to sub-dir win' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                return [$tmpDir . '/dir_1', $tmpDir . '/dir_2/dir_2_1', '\\', '..\dir_2\dir_2_1'];
            }
        ];
        yield 'siblings from sub-dir win' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                return [$tmpDir . '/dir_2/dir_2_1', $tmpDir . '/dir_1', '\\', '..\..\dir_1'];
            }
        ];

        yield 'distant roots' => [
            static function () use ($makeTmpDir) {
                $tmpDir = $makeTmpDir();
                $tmpDir2 = Filesystem::tmpDir();
                Filesystem::mkdirp($tmpDir2, [
                    'dir_1' => [
                        'dir_2_1' => [
                            'foo-file' => 'test',
                        ]
                    ]
                ]);
                $tmpDir2DirName = basename($tmpDir2);
                return [
                    $tmpDir . '/dir_1/dir_2_1',
                    $tmpDir2 . '/dir_1/dir_2_1/foo-file',
                    '/',
                    "../../../$tmpDir2DirName/dir_1/dir_2_1/foo-file"
                ];
            }
        ];
    }

    /**
     * @dataProvider relativePathDataSet
     */
    public function test_relativePath(\Closure $fixture): void
    {
        [$from, $to, $separator, $expected] = $fixture();
        $this->assertEquals($expected, Filesystem::relativePath($from, $to, $separator));
        $fullRelPath = $from . '/' . $expected;
        $this->assertFileExists(str_replace('\\', '/', $fullRelPath));
    }
}
