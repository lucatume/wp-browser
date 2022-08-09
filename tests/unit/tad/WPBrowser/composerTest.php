<?php namespace lucatume\WPBrowser;

use lucatume\WPBrowser\Utils\Map;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use function lucatume\WPBrowser\checkComposerDependencies;
use function lucatume\WPBrowser\composerFile;

class composerTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;

    /**
     * Test composerFile will throw if file does not exist
     */
    public function test_composer_file_will_throw_if_file_does_not_exist()
    {
        $this->expectException(\InvalidArgumentException::class);

        composerFile(__DIR__ . '/foo.json');
    }

    /**
     * Test composerFile will throw if file is not valid JSON
     */
    public function test_composer_file_will_throw_if_file_is_not_valid_json()
    {
        $this->expectException(\InvalidArgumentException::class);

        composerFile(__FILE__);
    }

    /**
     * Test composerFile will return map of file contents
     */
    public function test_composer_file_will_return_map_of_file_contents()
    {
        $map = composerFile(codecept_data_dir('composer-files/1.json'));

        $this->assertInstanceOf(Map::class, $map);
        $this->assertMatchesJsonSnapshot(json_encode($map->toArray(), JSON_PRETTY_PRINT));
    }

    /**
     * Test checkComposerDependencies fail
     */
    public function test_check_composer_dependencies_fail()
    {
        $called = false;
        $file   = codecept_data_dir('composer-files/1.json');
        checkComposerDependencies(composerFile($file), [
            'foo/bar' => '3.4.5',
            'bar/baz' => '3.4.5'
        ], static function ($input) use (&$called, &$lines) {
            $lines  = $input;
            $called = true;
        });

        $this->assertMatchesStringSnapshot(json_encode($lines, JSON_PRETTY_PRINT));
        $this->assertTrue($called);
    }

    /**
     * Test checkCompsoerDependencies pass
     */
    public function test_check_compsoer_dependencies_pass()
    {
        $called = false;
        $file   = codecept_data_dir('composer-files/1.json');
        checkComposerDependencies(composerFile($file), [
            'erusev/parsedown'                         => '^1.7',
            'lucatume/codeception-snapshot-assertions' => '^0.2',
        ], static function () use (&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }
}
