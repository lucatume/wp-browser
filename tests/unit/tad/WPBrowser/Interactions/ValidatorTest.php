<?php
namespace tad\WPBrowser\Interactions;


use org\bovigo\vfs\vfsStream;

class ValidatorTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	public function noSpacesInputs()

	{
		return [
			['foo', true],
			['foo ', true],
			[' foo ', true],
			[' foo', true],
			['foobar', true],
			['foo bar', false],
			['foo bar baz', false],
		];
	}

	/**
	 * @test
	 * it should validate questions about spaces
	 * @dataProvider noSpacesInputs
	 */
	public function it_should_validate_questions_about_spaces($value, $shouldValidate)
	{
		if (!$shouldValidate) {
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('foo');
		}

		$sut = $this->make_instance();
		$f = $sut->noSpaces('foo');
		$f($value);
	}

	/**
	 * @return Validator
	 */
	protected function make_instance()
	{
		return new Validator();
	}

	public function urls()
	{
		return [
			['http://example.com', true],
			['https://example.com', true],
			['example.com', false],
			['http://localhost', true],
			['https://localhost', true],
			['localhost', false],
			['http://10.10.10.1', true],
			['https://10.10.10.1', true],
			['10.10.10.1', false],
			['http://localhost:8080', true],
			['https://localhost:8080', true],
			['localhost:8080', false],
			['http://localhost:8080', true],
			['https://localhost:8080', true],
			['localhost:8080', false],
			['http://localhost:8080/path/frag', true],
			['https://localhost:8080/path/frag', true],
			['localhost:8080/path/frag', false],
			['http://example.com:8080', true],
			['https://example.com:8080', true],
			['example.com:8080', false],
			['http://10.10.10.1:8080/path/frag', true],
			['https://10.10.10.1:8080/path/frag', true],
			['10.10.10.1:8080/path/frag', false],
			['http://10.10.10.1:8080', true],
			['https://10.10.10.1:8080', true],
			['10.10.10.1:8080', false],
			['http://10.10.10.1:8080/path/frag', true],
			['https://10.10.10.1:8080/path/frag', true],
			['10.10.10.1:8080/path/frag', false],
		];
	}

	/**
	 * @test
	 * it should validate questions abour URLs
	 * @dataProvider urls
	 */
	public function it_should_validate_questions_about_urls($value, $shouldValidate)
	{
		if (!$shouldValidate) {
			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('foo');
		}

		$sut = $this->make_instance();
		$f = $sut->isUrl('foo');
		$f($value);
	}

	/**
	 * @test
	 * it should validate good wp dir
	 */
	public function it_should_validate_good_wp_dir()
	{
		$root = vfsStream::setup('root', null, [
			'wp' => [
				'wp-load.php' => '<?php // silence is golden ?>'
			]
		]);
		$dir = $root->url() . '/wp';

		$sut = $this->make_instance();
		$f = $sut->isWpDir();

		$this->assertEquals($dir, $f($dir));
	}

	/**
	 * @test
	 * it should not validate inexistent wp dir
	 */
	public function it_should_not_validate_inexistent_wp_dir()
	{
		$sut = $this->make_instance();
		$f = $sut->isWpDir();

		$this->expectException(\RuntimeException::class);

		$f(__DIR__ . '/foo');
	}

	/**
	 * @test
	 * it should not validate wp dir that does not contain wp-load.php file
	 */
	public function it_should_not_validate_wp_dir_that_does_not_contain_wp_load_php_file()
	{
		$root = vfsStream::setup('root', null, [
			'wp' => [
				'not-wp-load.php' => '<?php // silence is golden ?>'
			]
		]);
		$dir = $root->url() . '/wp';

		$sut = $this->make_instance();
		$f = $sut->isWpDir();

		$this->expectException(\RuntimeException::class);

		$f($dir);
	}

	public function emails()
	{
		return [
			['luca@theaveragedev.com', true],
			['luca+spam@theaveragedev.com', true],
			['luca.at.theaveragedev.com', false],
			['hello', false],
			['hello@there', false],
		];
	}

	/**
	 * @test
	 * it should validate email values
	 * @dataProvider emails
	 */
	public function it_should_validate_email_values($value, $shouldValidate)
	{
		if (!$shouldValidate) {
			$this->expectException(\RuntimeException::class);
		}

		$sut = $this->make_instance();
		$f = $sut->isEmail();
		$f($value);
	}

	/**
	 * @test
	 * it should validate relative wp admin dirs
	 */
	public function it_should_validate_relative_wp_admin_dirs()
	{
		$root = vfsStream::setup('root', null, [
			'wp' => [
				'wp-admin' => [],
				'not-wp-load.php' => '<?php // silence is golden ?>'
			]
		]);
		$dir = $root->url() . '/wp';

		$sut = $this->make_instance();
		$f = $sut->isRelativeWpAdminDir($dir);

		$this->assertEquals('/wp-admin', $f('/wp-admin'));
	}

	/**
	 * @test
	 * it should not validate non existing wp admin relative dir
	 */
	public function it_should_not_validate_non_existing_wp_admin_relative_dir()
	{
		$dir = __DIR__ . '/wp';

		$this->expectException(\RuntimeException::class);

		$sut = $this->make_instance();
		$f = $sut->isRelativeWpAdminDir($dir);

		$this->assertEquals($dir . '/wp-admin', $f('/wp-admin'));
	}

	public function pluginBasenames()
	{
		return [
			['hello.php', true],
			['hello/there.php', true],
			['hello/there-2.php', true],
			['hello/there', false],
			['hello', false],
			['hello/there/more.php', false],
			['.php', false],
			['*.php', false],
			['{{handlebars}}.php', false],
			['{{handlebars}}/handlebars.php', false],
			['', true],
		];
	}

	/**
	 * @test
	 * it should validate plugin basenames
	 * @dataProvider pluginBasenames
	 */
	public function it_should_validate_plugin_basenames($value, $shouldValidate)
	{
		if (!$shouldValidate) {
			$this->expectException(\RuntimeException::class);
		}

		$sut = $this->make_instance();
		$f = $sut->isPlugin();
		$f($value);
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf(Validator::class, $sut);
	}

	public function themeEntries()
	{
		return [
			['foo', true],
			['foo-bar', true],
			['foo-bar-baz', true],
			['foo-bar-baz,bar', true],
			['foo-bar-baz,bar-foo', true],
			[',foo-bar-baz,bar-foo', true],
			['', false],
			['some/theme', false],
			['some/theme', false],
			['some-foo/theme', false],
			['some-foo,/theme', false],
			['some-foo,bar/theme', false],
		];
	}

	/**
	 * @test
	 * it should validate theme entries
	 * @dataProvider themeEntries
	 */
	public function it_should_validate_theme_entries($value, $shouldValidate)
	{
		if (!$shouldValidate) {
			$this->expectException(\RuntimeException::class);
		}

		$sut = $this->make_instance();
		$f = $sut->isTheme();
		$f($value);
	}
}