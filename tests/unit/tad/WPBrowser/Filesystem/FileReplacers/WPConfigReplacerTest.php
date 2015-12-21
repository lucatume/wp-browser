<?php
namespace tad\WPBrowser\Filesystem\FileReplacers;


use Codeception\Exception\ModuleConfigException;
use org\bovigo\vfs\vfsStream;

class WPConfigReplacerTest extends \Codeception\TestCase\Test {

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	protected function _before() {
		$this->fsRoot = vfsStream::setup( 'root', null, [
			'missing-wp-config' => [ ],
			'wordpress'         => [ 'wp-config.php' => 'original' ]
		] );
	}

	protected function _after() {
	}

	/**
	 * @test
	 * it should throw if destination path is not a string
	 */
	public function it_should_throw_if_destination_path_is_not_a_string() {
		$path             = 23;
		$wpconfigContents = $this->contentsProvider();
		$this->setExpectedException( '\Codeception\Exception\ModuleConfigException' );

		$sut = new WPConfigReplacer( $path, $wpconfigContents->reveal() );
	}

	/**
	 * @test
	 * it should throw if destination path is not a folder
	 */
	public function it_should_throw_if_destination_path_is_not_a_folder() {
		$path             = 23;
		$wpconfigContents = $this->contentsProvider();
		$this->setExpectedException( '\Codeception\Exception\ModuleConfigException' );

		$sut = new WPConfigReplacer( vfsStream::url( 'root' ) . '/wordpress/wp-config.php', $wpconfigContents->reveal() );
	}

	/**
	 * @test
	 * it should throw if destination path is not readable
	 */
	public function it_should_throw_if_destination_path_is_not_readable() {
		$path             = 23;
		$wpconfigContents = $this->contentsProvider();
		$this->setExpectedException( '\Codeception\Exception\ModuleConfigException' );
		vfsStream::setup( 'writeable', 0222, [ 'wordpress' => [ 'wp-config.php' ] ] );

		$sut = new WPConfigReplacer( vfsStream::url( 'writeable' ) . '/wordpress/wp-config.php', $wpconfigContents->reveal() );
	}

	/**
	 * @test
	 * it should throw if destination file is not writeable
	 */
	public function it_should_throw_if_destination_file_is_not_writeable() {
		$path             = 23;
		$wpconfigContents = $this->contentsProvider();
		$this->setExpectedException( '\Codeception\Exception\ModuleConfigException' );
		vfsStream::setup( 'readable', 0444, [ 'wordpress' => [ 'wp-config.php' ] ] );

		$sut = new WPConfigReplacer( vfsStream::url( 'readable' ) . '/wordpress/wp-config.php', $wpconfigContents->reveal() );
	}

	/**
	 * @test
	 * it should throw if the root folder does not containa a wp-config.php file
	 */
	public function it_should_throw_if_the_root_folder_does_not_containa_a_wp_config_php_file() {
		$path             = 23;
		$wpconfigContents = $this->contentsProvider();
		$this->setExpectedException( '\Codeception\Exception\ModuleConfigException' );

		$sut = new WPConfigReplacer( vfsStream::url( 'root' ) . '/missing-wp-config', $wpconfigContents->reveal() );
	}

	/**
	 * @test
	 * it should not throw if root folder is write and read able
	 */
	public function it_should_not_throw_if_root_folder_is_write_and_read_able() {
		$wpconfigContents = $this->contentsProvider();
		$path             = 23;

		$sut = new WPConfigReplacer( vfsStream::url( 'root' ) . '/wordpress', $wpconfigContents->reveal() );
	}

	/**
	 * @test
	 * it should move the original wp-config.php file to original-wp-config.php
	 */
	public function it_should_move_the_original_wp_config_php_file_to_original_wp_config_php() {
		$original         = vfsStream::url( 'root' ) . '/wordpress/wp-config.php';
		$moved            = vfsStream::url( 'root' ) . '/wordpress/original-wp-config.php';
		$wpconfigContents = $this->contentsProvider();

		$sut = new WPConfigReplacer( vfsStream::url( 'root' ) . '/wordpress', $wpconfigContents->reveal() );
		$sut->replaceOriginal();

		$this->assertFileExists( $moved );
		$this->assertEquals( 'original', file_get_contents( $moved ) );
	}

	/**
	 * @test
	 * it should create an alternative wp-config.php file
	 */
	public function it_should_create_an_alternative_wp_config_php_file() {
		$wpconfigContents = $this->contentsProvider();
		$sut              = new WPConfigReplacer( vfsStream::url( 'root' ) . '/wordpress', $wpconfigContents->reveal() );
		$sut->replaceOriginal();

		$file = vfsStream::url( 'root' ) . '/wordpress/wp-config.php';
		$this->assertFileExists( $file );
		$this->assertEquals( 'modified', file_get_contents( $file ) );
	}

	/**
	 * @test
	 * it should restore the original wp-config.php file
	 */
	public function it_should_restore_the_original_wp_config_php_file() {
		$wpconfigContents = $this->contentsProvider();
		$sut              = new WPConfigReplacer( vfsStream::url( 'root' ) . '/wordpress', $wpconfigContents->reveal() );

		$sut->replaceOriginal();
		$sut->restoreOriginal();

		$file = vfsStream::url( 'root' ) . '/wordpress/wp-config.php';
		$this->assertFileExists( $file );
		$this->assertEquals( 'original', file_get_contents( $file ) );
	}

	/**
	 * @test
	 * it should unlink the alternative wp-config.php file
	 */
	public function it_should_unlink_the_alternative_wp_config_php_file() {
		$wpconfigContents = $this->contentsProvider();
		$sut              = new WPConfigReplacer( vfsStream::url( 'root' ) . '/wordpress', $wpconfigContents->reveal() );

		$sut->replaceOriginal();
		$sut->restoreOriginal();

		$file = vfsStream::url( 'root' ) . '/wordpress/original-wp-config.php';
		$this->assertFileNotExists( $file );
	}

	/**
	 * @return \Prophecy\Prophecy\ObjectProphecy
	 */
	protected function contentsProvider() {
		$wpconfigContents = $this->prophesize( '\tad\WPBrowser\Generators\RedirectingWPConfig' );
		$wpconfigContents->getContents()->willReturn( 'modified' );
		return $wpconfigContents;
	}
}