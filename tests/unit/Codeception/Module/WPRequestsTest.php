<?php
namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use tad\WPBrowser\Services\WP\Bootstrapper;

class WPRequestsTest extends \Codeception\Test\Unit
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Bootstrapper
     */
    protected $wpBootstrapper;

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(WPRequests::class, $sut);
    }

    private function make_instance()
    {
        return new WPRequests($this->moduleContainer->reveal(), $this->config, $this->wpBootstrapper->reveal());
    }

    /**
     * @test
     * it should throw if wpRootFolder is missing
     */
    public function it_should_throw_if_wp_root_folder_is_missing()
    {
        $this->config = [];

        $this->expectException(ModuleConfigException::class);

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

        $this->expectException(ModuleConfigException::class);

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

        $this->expectException(ModuleConfigException::class);

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

        $this->expectException(ModuleConfigException::class);

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

        $this->expectException(ModuleConfigException::class);

        $sut = $this->make_instance();
        $sut->_initialize();
    }

    /**
     * @test
     * it should create nonces
     */
    public function it_should_create_nonces()
    {
        $credentials = ['some' => 'credentials'];
        $this->wpBootstrapper->createNonce('some_action', $credentials)->willReturn('foobar');

        $sut = $this->make_instance();
        $this->assertEquals('foobar', $sut->createNonce('some_action', $credentials));
    }

    /**
     * @test
     * it should throw if nonce creation is falsy
     */
    public function it_should_throw_if_nonce_creation_is_falsy()
    {
        $credentials = ['some' => 'credentials'];
        $this->wpBootstrapper->createNonce('some_action', $credentials)->willReturn(false);

        $this->expectException(\RuntimeException::class);

        $sut = $this->make_instance();
        $this->assertEquals('foobar', $sut->createNonce('some_action', $credentials));
    }

    protected function _before()
    {
        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
        $this->wpBootstrapper = $this->prophesize(Bootstrapper::class);

        $wpRootFolder = vfsStream::newDirectory('wp');
        $wpLoadFile = vfsStream::newFile('wp-load.php');
        $wpLoadFile->setContent('foo');
        $wpRootFolder->addChild($wpLoadFile);
        $this->config['wpRootFolder'] = $wpRootFolder->url();
    }
}
