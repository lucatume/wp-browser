<?php
namespace lucatume\WPBrowser\Services\WP;

use lucatume\WPBrowser\Traits\WithStubProphecy;
use lucatume\WPBrowser\Utils\System;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\BrowserKit\Cookie;

class BootstrapperTest extends \Codeception\Test\Unit
{
    use WithStubProphecy;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var string
     */
    protected $wpLoadPath;

    /**
     * @var \lucatume\WPBrowser\Utils\System
     */
    protected $system;

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf('lucatume\WPBrowser\Services\WP\Bootstrapper', $sut);
    }

    /**
     * @return Bootstrapper
     */
    private function make_instance()
    {
        return new Bootstrapper($this->wpLoadPath, $this->system->reveal());
    }

    /**
     * @test
     * it should allow setting the wpLoadPath
     */
    public function it_should_allow_setting_the_wp_load_path()
    {
        $sut = $this->make_instance();

        $sut->setWpLoadPath('foo');

        $this->assertEquals('foo', $sut->getWpLoadPath());
    }

    /**
     * @test
     * it should allow setting the bootstrap file path
     */
    public function it_should_allow_setting_the_bootstrap_file_path()
    {
        $sut = $this->make_instance();

        $sut->setBootstrapScriptFilePath('foo');

        $this->assertEquals('foo', $sut->getBootstrapScriptFilePath());
    }

    /**
     * @test
     * it should exec bootstrap script with request
     */
    public function it_should_exec_bootstrap_script_with_request()
    {
        $request = ['some' => 'request'];

        $this->system->system(PHP_BINARY . ' ' . escapeshellarg('foo') . ' ' . escapeshellarg($this->wpLoadPath) . ' ' . escapeshellarg(serialize($request)))
            ->willReturn(serialize(['some' => 'output']));

        $sut = $this->make_instance();
        $sut->setBootstrapScriptFilePath('foo');
        $sut->bootstrapWpAndExec($request);
    }

    /**
     * @test
     * it should exec bootstrap with proper parameters when requesting nonces
     */
    public function it_should_exec_bootstrap_with_proper_parameters_when_requesting_nonces()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar',
            'authCookie' => new Cookie('auth', 'foo'),
            'loginCookie' => new Cookie('login', 'bar')
        ];

        $request = [
            'action' => 'some_action',
            'credentials' => [
                'user_login' => $credentials['username'],
                'user_password' => $credentials['password'],
                'remember' => true
            ],
            'cookies' => [
                'auth' => 'foo',
                'login' => 'bar'
            ]
        ];
        $system  = $this->system->system(PHP_BINARY . ' ' . escapeshellarg('foo') . ' ' . escapeshellarg($this->wpLoadPath) . ' ' . escapeshellarg(serialize($request)));
        $system ->willReturn(serialize(['some' => 'output']));

        $sut = $this->make_instance();
        $sut->setBootstrapScriptFilePath('foo');
        $sut->createNonce('some_action', $credentials);
    }

    protected function _before()
    {
        $wp = vfsStream::newDirectory('wp');
        $wpLoadFile = vfsStream::newFile('wp-load.php');
        $wpLoadFile->setContent('foo');
        $wp->addChild($wpLoadFile);
        $this->wpLoadPath = $wp->url() . '/wp-load.php';
        $this->system = $this->stubProphecy(System::class);
    }
}
