<?php
namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Prophecy\Argument;
use tad\WPBrowser\Environment\Executor;

class WPCLITest extends \Codeception\Test\Unit
{
    protected $backupGlobals = false;
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;

    /**
     * @var vfsStreamDirectory
     */
    protected $root;

    /**
     * @var Executor
     */
    protected $executor;

    /**
     * @var array
     */
    protected $config = [
        'throw' => true
    ];

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(WPCLI::class, $sut);
    }

    /**
     * @return WPCLI
     */
    private function make_instance()
    {
        return new WPCLI($this->moduleContainer->reveal(), $this->config, $this->executor->reveal());
    }

    /**
     * @test
     * it should throw if path is not folder
     */
    public function it_should_throw_if_path_is_not_folder()
    {
        $this->config = ['path' => '/some/path/to/null'];

        $this->expectException(ModuleConfigException::class);

        $this->make_instance();
    }

    /**
     * @test
     * it should call the executor with proper parameters
     */
    public function it_should_call_the_executor_with_proper_parameters()
    {
        $this->executor->exec(Argument::containingString('--path=' . $this->root->url() . '/wp'), Argument::any(),
            Argument::any())->shouldBeCalled();
        $this->executor->exec(Argument::containingString('core version'), Argument::any(),
            Argument::any())->shouldBeCalled();

        $sut = $this->make_instance();

        $sut->cli('core version');
    }

    public function optionalOptionsWithArguments()
    {
        return [
            ['ssh', 'some-ssh'],
            ['http', 'some-http'],
            ['url', 'some-url'],
            ['user', 'some-user'],
            ['skip-plugins', 'some-plugin, another-plugin'],
            ['skip-themes', 'some-theme, another-theme'],
            ['skip-packages', 'some-package, another-package'],
            ['require', 'some-file']
        ];
    }

    /**
     * @test
     * it should allow setting additional wp-cli options in the config file
     * @dataProvider optionalOptionsWithArguments
     */
    public function it_should_allow_setting_additional_wp_cli_options_in_the_config_file($option, $optionValue)
    {
        $this->config[$option] = $optionValue;
        $this->executor->exec(Argument::containingString('--' . $option . '=' . $optionValue), Argument::any(),
            Argument::any())->shouldBeCalled();

        $sut = $this->make_instance();

        $sut->cli('core version');
    }

    public function skippedOptions()
    {
        return [
            ['debug'],
            ['color'],
            ['prompt'],
            ['quiet']
        ];
    }

    /**
     * @test
     * it should skip some options by default
     * @dataProvider skippedOptions
     */
    public function it_should_skip_some_options_by_default($option)
    {
        $this->config[$option] = true;
        $this->executor->exec(Argument::not(Argument::containingString('--' . $option)), Argument::any(),
            Argument::any())->shouldBeCalled();

        $sut = $this->make_instance();

        $sut->cli('core version');
    }

    /**
     * @test
     * it should allow overriding options with inline command
     * @dataProvider optionalOptionsWithArguments
     */
    public function it_should_allow_overriding_options_with_inline_command($option, $optionValue)
    {
        $this->config[$option] = $optionValue;
        $overrideValue = 'another-' . $option . '-value';
        $this->executor->exec(Argument::containingString('--' . $option . '=' . $overrideValue), Argument::any(),
            Argument::any())->shouldBeCalled();

        $sut = $this->make_instance();

        $sut->cli('core version --' . $option . '=' . $overrideValue);
    }

    /**
     * @test
     * it should cast wp-cli errors to exceptions if specified in config
     */
    public function it_should_cast_wp_cli_errors_to_exceptions_if_specified_in_config()
    {
        $this->config['throw'] = true;
        $this->executor->exec(Argument::type('string'), Argument::any(), Argument::any())->willReturn(-1);
        $this->expectException(ModuleException::class);

        $sut = $this->make_instance();

        $sut->cli('core version');
    }

    /**
     * @test
     * it should not throw any exception if specified in config
     */
    public function it_should_not_throw_any_exception_if_specified_in_config()
    {
        $this->config['throw'] = false;
        $this->executor->exec(Argument::type('string'), Argument::any(), Argument::any())->willReturn(-1);

        $sut = $this->make_instance();

        $sut->cli('core version');
    }

    public function cliReturnValues()
    {
        return [
            ['1 2 3 4 5', [1, 2, 3, 4, 5]],
            ['', []],
            ["Post 1\nPost 2\nPost 3", ['Post 1', 'Post 2', 'Post 3']],
            ["Post 1\n Post 2\n Post 3", ['Post 1', 'Post 2', 'Post 3']],
            ["Post 1 \n Post 2 \n Post 3", ['Post 1', 'Post 2', 'Post 3']],
            ["Post 1 \nPost 2 \nPost 3", ['Post 1', 'Post 2', 'Post 3']],
        ];
    }

    /**
     * @test
     * it should not cast output to any format
     * @dataProvider cliReturnValues
     */
    public function it_should_not_cast_output_to_any_format($raw, $expected)
    {
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn($raw);

        $sut = $this->make_instance();

        $ids = $sut->cliToArray('post list --format==ids');

        $this->assertEquals($expected, $ids);
    }

    /**
     * @test
     * it should allow defining a split callback function
     */
    public function it_should_allow_defining_a_split_callback_function()
    {
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn('23 12');
        $expected = [1, 2, 3];
        $splitCallback = function () use ($expected) {
            return $expected;
        };

        $sut = $this->make_instance();
        $ids = $sut->cliToArray('post list --format==ids', $splitCallback);

        $this->assertEquals($expected, $ids);
    }

    /**
     * @test
     * it should throw if split callback function does not return an array
     */
    public function it_should_throw_if_split_callback_function_does_not_return_an_array()
    {
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn('23 12');
        $splitCallback = function () {
            return 'foo';
        };

        $sut = $this->make_instance();

        $this->expectException(ModuleException::class);
        $sut->cliToArray('post list --format=ids', $splitCallback);
    }

    protected function _before()
    {
        $this->moduleContainer = $this->prophesize(ModuleContainer::class);
        $this->root = vfsStream::setup('root');
        $wpDir = vfsStream::newDirectory('wp');
        $this->root->addChild($wpDir);
        $this->config = ['path' => $this->root->url() . '/wp'];
        $this->executor = $this->prophesize(Executor::class);
    }

    /**
     * @test
     * it should handle the case where the command output is not a string
     */
    public function it_should_handle_the_case_where_the_command_output_is_not_a_string()
    {
        $expected = $output = ['23', '89', '13', '45'];
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn($output);

        $sut = $this->make_instance();

        $this->assertEquals($expected, $sut->cliToArray('post list --format=ids'));
    }

    /**
     * @test
     * it should handle the case where the command output is an empty array
     */
    public function it_should_handle_the_case_where_the_command_output_is_an_empty_array()
    {
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn([]);

        $sut = $this->make_instance();

        $this->assertEquals([], $sut->cliToArray('post list --format=ids'));
    }

    /**
     * @test
     * it should handle the case where the command output is null
     */
    public function it_should_handle_the_case_where_the_command_output_is_null()
    {
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn(null);

        $sut = $this->make_instance();

        $this->assertEquals([], $sut->cliToArray('post list --format=ids'));
    }

    /**
     * @test
     * it should call the split callback even if the output is an array
     */
    public function it_should_call_the_split_callback_even_if_the_output_is_an_array()
    {
        $output = ['123foo', 'foo123', '123foo', 'bar'];
        $this->executor->execAndOutput(Argument::type('string'), Argument::any())->willReturn($output);

        $sut = $this->make_instance();

        $callback = function ($output) {
            return preg_split('/123\\n/', $output);
        };

        $expected = preg_split('/123\\n/', implode(PHP_EOL, $output));
        $this->assertEquals($expected, $sut->cliToArray('post list --format=ids', $callback));
    }
}
