<?php namespace tad\WPBrowser;

class dbTest extends \Codeception\Test\Unit
{
    public function test_dsnToArr_with_full_string()
    {
        $map = dsnToMap('mysql:host=foo;dbname=bar;port=2345');

        $this->assertEquals('mysql', $map['type']);
        $this->assertEquals('mysql', $map('prefix'));
        $this->assertEquals('foo', $map['host']);
        $this->assertEquals('bar', $map('dbname'));
        $this->assertEquals('2345', $map('port','3306'));
    }

    public function test_dsnToArr_with_string_missing_prefix()
    {
        $map = dsnToMap('host=foo;dbname=bar;port=2345');

        $this->assertEquals('unknown', $map['type']);
        $this->assertEquals('unknown', $map('prefix'));
        $this->assertEquals('foo', $map['host']);
        $this->assertEquals('bar', $map('dbname'));
        $this->assertEquals('2345', $map('port','3306'));
    }

    public function test_dsnToArr_with_sqlite_string_and_file()
    {
        $map = dsnToMap('sqlite:/foo/bar.sqlite');

        $this->assertEquals('sqlite', $map['type']);
        $this->assertEquals('sqlite', $map('prefix'));
        $this->assertEquals('localhost', $map('host','localhost'));
        $this->assertEquals('/foo/bar.sqlite', $map('file','/db.sqlite'));
    }

    public function test_dsnToArr_with_sqlite_memory_string()
    {
        $map = dsnToMap('sqlite2::memory:');

        $this->assertEquals('sqlite2', $map['type']);
        $this->assertEquals('sqlite2', $map('prefix'));
        $this->assertEquals('localhost', $map('host','localhost'));
        $this->assertEquals(true, $map('memory',false));
    }
}
