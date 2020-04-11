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
}
