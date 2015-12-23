<?php
namespace tad\WPBrowser\Filesystem;

class UtilsTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	}

	protected function tearDown() {
	}

	/**
	 * @test
	 * it should throw if path is not a string
	 */
	public function it_should_throw_if_path_is_not_a_string() {
		$this->setExpectedException( 'InvalidArgumentException' );
		Utils::homeify( 23 );
	}

	public function noHomeSymbolPaths() {
		return [
			[ 'foo' ],
			[ 'foo/bar' ],
			[ '/foo/baz/bar' ],
			[ '../some/path.here' ],
			[ '/../../foo/bar/baz.php' ],
			[ '' ]
		];
	}

	/**
	 * @test
	 * it should return same string if no home symbol
	 * @dataProvider noHomeSymbolPaths
	 */
	public function it_should_return_same_string_if_no_home_symbol( $path ) {
		$this->assertEquals( $path, Utils::homeify( $path ) );
	}

	public function homeSymbolPahts() {
		return [
			[ '~/some/folder/path.php', '/foo/bar/some/folder/path.php', '/foo/bar' ],
			[ '~/another/path', '/foo/bar/another/path', '/foo/bar' ],
			[ '~', '/foo/bar', '/foo/bar' ]
		];
	}

	/**
	 * @test
	 * it should return replaced home symbol
	 * @dataProvider homeSymbolPahts
	 */
	public function it_should_return_replaced_home_symbol( $path, $expected, $home ) {
		$filesystem = $this->prophesize( '\tad\WPBrowser\Filesystem\Filesystem' );
		$filesystem->getUserHome()->willReturn( $home );
		$this->assertEquals( $expected, Utils::homeify( $path, $filesystem->reveal() ) );
	}

	public function untrailslashPaths() {
		return [
			[ '/some', '/some' ],
			[ '/some/path', '/some/path' ],
			[ '/some/path/', '/some/path' ],
			[ 'some/path/', 'some/path' ],
			[ '../some/path/', '../some/path' ],
			[ '/some/path/../', '/some/path/..' ],
			[ '/', '/' ],
			[ '', '' ]
		];
	}

	/**
	 * @test
	 * it should allow untrailslash paths
	 * @dataProvider untrailslashPaths
	 */
	public function it_should_allow_untrailslash_paths( $path, $expected ) {
		$this->assertEquals( $expected, Utils::untrailslashit( $path ) );
	}

	public function unleadslashPaths() {
		return [
			[ '/some', 'some' ],
			[ '/some/path', 'some/path' ],
			[ '/some/path/', 'some/path/' ],
			[ 'some/path/', 'some/path/' ],
			[ '../some/path/', '../some/path/' ],
			[ '/some/path/../', 'some/path/../' ],
			[ '/', '/' ],
			[ '', '' ]
		];
	}

	/**
	 * @test
	 * it should allow unleadslash paths
	 * @dataProvider unleadslashPaths
	 */
	public function it_should_allow_unleadslash_paths( $path, $expected ) {
		$this->assertEquals( $expected, Utils::unleadslashit( $path ) );
	}

	/**
	 * @test
	 * it should allow finding file in current dir
	 */
	public function it_should_allow_finding_file_in_current_dir() {
		$path = dirname( __FILE__ ) . '/foo.php';
		touch( $path );
		$this->assertEquals( $path, Utils::findHereOrInParent( '/foo.php', dirname( __FILE__ ) ) );
		unlink( $path );
	}

	/**
	 * @test
	 * it should not find non existing file
	 */
	public function it_should_not_find_non_existing_file() {
		$this->assertFalse( Utils::findHereOrInParent( '/bar.php', dirname( __FILE__ ) ) );
	}

	/**
	 * @test
	 * it should find file in parent
	 */
	public function it_should_find_file_in_parent() {
		$path = dirname( dirname( dirname( __FILE__ ) ) ) . '/foo.php';
		touch( $path );
		$this->assertEquals( $path, Utils::findHereOrInParent( '/foo.php', dirname( __FILE__ ) ) );
		unlink( $path );
	}

	/**
	 * @test
	 * it should find file with just name
	 */
	public function it_should_find_file_with_just_name() {
		$path = dirname( dirname( dirname( __FILE__ ) ) ) . '/foo.php';
		touch( $path );
		$this->assertEquals( $path, Utils::findHereOrInParent( 'foo.php', dirname( __FILE__ ) ) );
		unlink( $path );
	}

	/**
	 * @test
	 * it should find file with relative path
	 */
	public function it_should_find_file_with_relative_path() {
		$folder = dirname( dirname( dirname( __FILE__ ) ) ) . '/someFolder';
		mkdir( $folder );
		$path = dirname( dirname( dirname( __FILE__ ) ) ) . '/someFolder/foo.php';
		touch( $path );
		$this->assertEquals( $path, Utils::findHereOrInParent( 'someFolder/foo.php', dirname( __FILE__ ) ) );
		unlink( $path );
		rmdir( $folder );
	}

	/**
	 * @test
	 * it should find files with relative dirnname
	 */
	public function it_should_find_files_with_relative_dirnname() {
		$path = dirname( dirname( dirname( __FILE__ ) ) ) . '/foo.php';
		touch( $path );
		$this->assertEquals( $path, Utils::findHereOrInParent( '../../foo.php', dirname( __FILE__ ) ) );
		unlink( $path );
	}
}
