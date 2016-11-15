<?php
namespace Codeception\Module;


use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;
use SebastianBergmann\GlobalState\Restorer;
use tad\WPBrowser\Adapters\WP;

class WPBootstrapperTest extends \Codeception\TestCase\Test
{
	protected $backupGlobals = false;

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var ModuleContainer
	 */
	protected $module_container;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var Restorer
	 */
	protected $restorer;

	/**
	 * @var WP
	 */
	protected $wp;

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		if (!function_exists('set_site_transient')) {
			function set_site_transient($key, $value)
			{
				global $_transients;
				$_transients = is_array($_transients) ? $_transients : [];
				$_transients[$key] = $value;
			}
		}
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable()
	{
		$sut = $this->make_instance();

		$this->assertInstanceOf('Codeception\Module\WPBootstrapper', $sut);
	}

	private function make_instance()
	{
		return new WPBootstrapper($this->module_container->reveal(), $this->config, $this->restorer->reveal(),
			$this->wp->reveal());
	}

	/**
	 * @test
	 * it should throw if wpRootFolder is missing
	 */
	public function it_should_throw_if_wp_root_folder_is_missing()
	{
		$this->config = [];

		$this->expectException('Codeception\Exception\ModuleConfigException');

		$this->make_instance();
	}

	/**
	 * @test
	 * it should throw if wpRootFolder is not a folder
	 */
	public function it_should_throw_if_wp_root_folder_is_not_a_folder()
	{
		vfsStream::setup('wproot', null, ['wp' => 'a file really']);
		$this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

		$this->expectException('Codeception\Exception\ModuleConfigException');

		$sut = $this->make_instance();
		$sut->_initialize();
	}

	/**
	 * @test
	 * it should throw if wpRootFolder is not readable
	 */
	public function it_should_throw_if_wp_root_folder_is_not_readable()
	{
		$root = vfsStream::setup('wproot');
		$root->addChild(vfsStream::newDirectory('wp', 0000));
		$this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

		$this->expectException('Codeception\Exception\ModuleConfigException');

		$sut = $this->make_instance();
		$sut->_initialize();
	}

	/**
	 * @test
	 * it should throw if wp-load.php is not in wpRootFolder
	 */
	public function it_should_throw_if_wp_load_php_is_not_in_wp_root_folder()
	{
		$root = vfsStream::setup('wproot');
		$root->addChild(vfsStream::newDirectory('wp'));
		$this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

		$this->expectException('Codeception\Exception\ModuleConfigException');

		$sut = $this->make_instance();
		$sut->_initialize();
	}

	/**
	 * @test
	 * it should throw if wp-load.php is not readable
	 */
	public function it_should_throw_if_wp_load_php_is_not_readable()
	{
		vfsStream::setup('wproot', null, ['wp' => ['wp-load.php' => 'some content']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot/wp');
		chmod(vfsStream::url('wproot/wp/wp-load.php'), 0);

		$this->expectException('Codeception\Exception\ModuleConfigException');

		$sut = $this->make_instance();
		$sut->_initialize();
	}

	/**
	 * @test
	 * it should include the wp-load.php file
	 */
	public function it_should_include_the_wp_load_php_file()
	{
		vfsStream::setup('wproot', null, ['wp' => ['wp-load.php' => '<?php global $_flag; $_flag = true; ?>']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot/wp');

		$sut = $this->make_instance();
		$sut->_initialize();
		$sut->bootstrapWp();

		global $_flag;
		$this->assertTrue($_flag);
	}

	/**
	 * @test
	 * it should not include wp-load.php twice
	 */
	public function it_should_not_include_wp_load_php_twice()
	{
		vfsStream::setup('wproot2', null, ['wp' => ['wp-load.php' => '<?php global $_flag; $_flag = true; ?>']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot2/wp');
		$sut = $this->make_instance();
		$sut->_initialize();
		$sut->bootstrapWp();

		global $_flag;
		$this->assertTrue($_flag);

		$_flag = false;

		$sut->bootstrapWp();

		$this->assertFalse($_flag);
	}

	/**
	 * @test
	 * it should restore global state after first bootstrapping
	 */
	public function it_should_restore_global_state_after_first_bootstrapping()
	{
		vfsStream::setup('wproot3', null, ['wp' => ['wp-load.php' => 'some content']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot3/wp');
		$this->restorer->restoreGlobalVariables(Argument::any())->shouldBeCalled();
		$this->restorer->restoreStaticAttributes(Argument::any())->shouldBeCalled();

		$sut = $this->make_instance();

		$sut->_initialize();

		global $someVar;

		$someVar = 'foo';

		$sut->bootstrapWp();

		$someVar = null;

		$sut->bootstrapWp();

		$this->assertEquals('foo', $someVar);
	}

	/**
	 * @test
	 * it should deactivate updates when bootstrapping
	 */
	public function it_should_deactivate_updates_when_bootstrapping()
	{
		vfsStream::setup('wproot4', null, ['wp' => ['wp-load.php' => '<?php global $_flag; $_flag = true; ?>']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot4/wp');
		foreach (['update_core', 'update_plugins', 'update_themes'] as $key) {
			$this->wp->set_site_transient($key, Argument::type('stdClass'), Argument::any())->shouldBeCalled();
		}
		$sut = $this->make_instance();
		$sut->_initialize();

		$sut->bootstrapWp();
	}

	/**
	 * @test
	 * it should unset closures in global array when saving global state
	 */
	public function it_should_unset_closures_in_global_array_when_saving_global_state()
	{
		vfsStream::setup('wproot3', null, ['wp' => ['wp-load.php' => 'some content']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot3/wp');

		$sut = $this->make_instance();

		$sut->_initialize();

		global $someVar;

		$someVar = function () {
			$foo = 'bar';
		};

		$sut->bootstrapWp();

		$this->assertEquals(null, $someVar);
	}

	/**
	 * @test
	 * it should unset closures in nested global array when saving global state
	 */
	public function it_should_unset_closures_in_nested_global_array_when_saving_global_state()
	{
		vfsStream::setup('wproot3', null, ['wp' => ['wp-load.php' => 'some content']]);
		$this->config['wpRootFolder'] = vfsStream::url('wproot3/wp');

		$sut = $this->make_instance();

		$sut->_initialize();

		global $someVar;

		$someVar = array(
			'1' => array(
				'2' => array(
					'3' => function () {
						$foo = 'bar';
					}
				)
			)
		);


		$sut->bootstrapWp();

		$expected = array(
			'1' => array(
				'2' => array(
					'3' => null
				)
			)
		);
		$this->assertEquals($expected, $someVar);
	}

	protected function _before()
	{
		global $_flag;
		$_flag = false;
		$this->module_container = $this->prophesize(ModuleContainer::class);
		vfsStream::setup('wproot', null, ['wp' => ['wp-load.php' => '// load WordPress']]);
		$this->config = [
			'wpRootFolder' => vfsStream::url('wproot')
		];
		$this->restorer = $this->prophesize(Restorer::class);
		$this->wp = $this->prophesize(WP::class);
		$this->wp->set_site_transient(Argument::type('string'), Argument::any(), Argument::any())->willReturn(true);
	}

	protected function _after()
	{
	}
}