<?php

namespace Unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use Generator;
use lucatume\WPBrowser\Utils\Filesystem;
use RuntimeException;

class FilesystemTest extends Unit
{
    public function resolvePathDataSet(): array
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
        $pathWithSpace =  $tmpDir . '/Some\ Path/Some\ File';

        return [
            [ '', null, getcwd() ],
            [ '/', null, '/' ],
            [ '~', null, getenv('HOME') ],
            [ __DIR__, null, __DIR__ ],
            [ __FILE__, null, __FILE__ ],
            [ basename(__FILE__), __DIR__, __FILE__ ],
            [$tmpDir . '/Some\ Path/Some\ File',null, $tmpDir . '/Some Path/Some File']
        ];
    }

    /**
     * Test resolvePath
     *
     * @dataProvider resolvePathDataSet
     */
    public function test_resolve_path($path, $root, $expected): void
    {
        $this->assertEquals($expected, Filesystem::resolvePath($path, $root));
    }

    public function findHereOrInParentDataSet(): array
    {
        return [
            [ '/foo/bar/baz', '~', false ],
            [ basename(__FILE__),__DIR__, __FILE__ ],
            [ 'unit',__DIR__, codecept_root_dir('tests/unit') ],
            [ 'foo-bar',__DIR__, false ],
        ];
    }

    /**
     * Test findHereOrInParent
     * @dataProvider findHereOrInParentDataSet
     */
    public function test_find_here_or_in_parent($path, $root, $expected): void
    {
        $this->assertEquals($expected, Filesystem::findHereOrInParent($path, $root));
    }

    public function test_rrmdir(): void
    {
        $root            = codecept_output_dir('rrmdirTest');
        $createDirStruct = static function ($key, $value) use (&$createDirStruct) {
            if (is_array($value)) {
                if (! is_dir($key) && ! mkdir($key) && ! is_dir($key)) {
                    throw new RuntimeException("Could not create directory {$key}");
                }
                foreach ($value as $subKey => $subValue) {
                    $createDirStruct($key . '/' . $subKey, $subValue);
                }

                return;
            }

            if (! file_put_contents($key, $value)) {
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
                    'index.txt'     => 'test test test',
                    'sub-dir-3-2-1' => [
                        'index.txt' => 'test test test'
                    ]
                ],
                'sub-dir-3-3' => [
                    'index.txt'     => 'test test test',
                    'sub-dir-3-3-1' => [
                        'index.txt'       => 'test test test',
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

        Filesystem::rrmdir($root  .'/sub-dir-1/delme.txt');

        $this->assertFileNotExists($root  .'/sub-dir-1/delme.txt');

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
//        yield 'empty' => ['', '', '/', ''];
        yield 'empty from, absolute to' => ['', __DIR__, '/', __DIR__];
        yield 'empty to' => [__DIR__, '', '/', ''];

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

        yield 'siblings' => [$tmpDir . '/dir_1', $tmpDir . '/dir_2', '/', '../dir_2'];
        yield 'siblings to sub-dir' => [$tmpDir . '/dir_1', $tmpDir . '/dir_2/dir_2_1', '/', '../dir_2/dir_2_1'];
        yield 'siblings from sub-dir' => [$tmpDir . '/dir_2/dir_2_1', $tmpDir . '/dir_1', '/', '../../dir_1'];

        yield 'siblings win' => [$tmpDir . '/dir_1', $tmpDir . '/dir_2', '\\', '..\dir_2'];
        yield 'siblings to sub-dir win' => [$tmpDir . '/dir_1', $tmpDir . '/dir_2/dir_2_1', '\\', '..\dir_2\dir_2_1'];
        yield 'siblings from sub-dir win' => [$tmpDir . '/dir_2/dir_2_1', $tmpDir . '/dir_1', '\\', '..\..\dir_1'];

        $tmpDir2 = Filesystem::tmpDir();
        Filesystem::mkdirp($tmpDir2, [
            'dir_1' => [
                'dir_2_1' => [
                    'foo-file' => 'test',
                ]
            ]
        ]);
        $tmpDir2DirName = basename($tmpDir2);
        yield 'distant roots' => [
            $tmpDir . '/dir_1/dir_2_1',
            $tmpDir2 . '/dir_1/dir_2_1/foo-file',
            '/',
            "../../../$tmpDir2DirName/dir_1/dir_2_1/foo-file"
        ];
    }

    /**
     * @dataProvider relativePathDataSet
     */
    public function test_relativePath(string $from, string $to, string $separator, string $expected): void
    {
        $this->assertEquals($expected, Filesystem::relativePath($from, $to, $separator));
        $fullRelPath = $from . '/' . $expected;
        $this->assertFileExists(str_replace('\\', '/', $fullRelPath));
    }
}
