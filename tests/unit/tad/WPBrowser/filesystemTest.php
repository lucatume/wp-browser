<?php namespace tad\WPBrowser;

class filesystemTest extends \Codeception\Test\Unit
{

    public function resolvePathDataSet()
    {
        return [
            [ '', null, getcwd() ],
            [ '/', null, '/' ],
            [ '~', null, getenv('HOME') ],
            [ __DIR__, null, __DIR__ ],
            [ __FILE__, null, __FILE__ ],
            [ basename(__FILE__), __DIR__, __FILE__ ]
        ];
    }

    /**
     * Test resolvePath
     *
     * @dataProvider resolvePathDataSet
     */
    public function test_resolve_path($path, $root, $expected)
    {
        $this->assertEquals($expected, resolvePath($path, $root));
    }

    public function findHereOrInParentDataSet()
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
    public function test_find_here_or_in_parent($path, $root, $expected)
    {
        $this->assertEquals($expected, findHereOrInParent($path, $root));
    }

    public function test_rrmdir()
    {
        $root            = codecept_output_dir('rrmdirTest');
        $createDirStruct = static function ($key, $value) use (&$createDirStruct) {
            if (is_array($value)) {
                if (! is_dir($key) && ! mkdir($key) && ! is_dir($key)) {
                    throw new \RuntimeException("Could not create directory {$key}");
                }
                foreach ($value as $subKey => $subValue) {
                    $createDirStruct($key . '/' . $subKey, $subValue);
                }

                return;
            }

            if (! file_put_contents($key, $value)) {
                throw new \RuntimeException("Could not put file contents in file {$key}");
            }
        };
        rmkdir($root, [
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

        rrmdir($root  .'/sub-dir-1/delme.txt');

        $this->assertFileNotExists($root  .'/sub-dir-1/delme.txt');

        rrmdir($root);

        if (method_exists($this, 'assertDirectoryDoesNotExist')) {
            $this->assertDirectoryDoesNotExist($root);
        } else {
            $this->assertDirectoryNotExists($root);
        }
    }
}
