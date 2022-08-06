<?php namespace tad\WPBrowser\Traits;

use lucatume\WPBrowser\Module\WPFilesystem;
use PHPUnit\Framework\AssertionFailedError;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use tad\WPBrowser\StubProphecy\Arg;

class TestObject
{
    public function returnInt()
    {
    }

    public function returnString()
    {
    }

    public function returnFloat()
    {
    }
}

class WithStubProphecyTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;
    use WithStubProphecy;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * It should allow creating a method prophecy that will return a value
     *
     * @test
     */
    public function should_allow_creating_a_method_prophecy_that_will_return_a_value()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
            ->getWpRootFolder()->willReturn(__DIR__);

        $revealed = $fs->reveal();

        $this->assertEquals(__DIR__, $revealed->getWpRootFolder());
    }

    /**
     * It should fail if arg expectation fails
     *
     * @test
     */
    public function should_fail_if_arg_expectation_fails()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')->willReturn(__DIR__);

        // Expect a failure a first time as the expected arguments are not passed.
        $this->expectException(AssertionFailedError::class);

        $revealed = $fs->reveal();

        $revealed->getBlogUploadsPath(89, 'some/other/file.php');
    }

    /**
     * It should not throw if arg expectation is matched
     *
     * @test
     */
    public function should_not_throw_if_arg_expectation_is_matched()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')->willReturn(__DIR__);

        $revealed = $fs->reveal();

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php');
    }

    /**
     * It should fail if actual argument is not expected
     *
     * @test
     */
    public function should_fail_if_actual_argument_is_not_expected()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')->willReturn(__DIR__);

        // Expect a failure a first time as the expected arguments are not passed.
        $this->expectException(AssertionFailedError::class);

        $revealed = $fs->reveal();

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php', new \DateTime('yesterday'));
    }

    /**
     * It should allow setting parameter expectations using closures
     *
     * @test
     */
    public function should_allow_setting_parameter_expectations_using_closures()
    {
        $assertSameDate = static function ($date) {
            return $date instanceof \DateTime && $date->format('Y-m-d') === '2019-01-01';
        };
        $fs             = $this->stubProphecy(WPFilesystem::class)
                               ->getBlogUploadsPath(23, 'some/file/foo.php', Arg::that($assertSameDate))
                               ->willReturn(__DIR__);

        $revealed = $fs->reveal();

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php', new \DateTime('2019-01-01'));
    }

    /**
     * It should fail correctly when using closures for assertions.
     *
     * @test
     */
    public function should_fail_correctly_when_using_closures_for_assertions_()
    {
        $assertSameDate = static function ($date) {
            return $date instanceof \DateTime && $date->format('Y-m-d') === '2020-01-01';
        };
        $fs             = $this->stubProphecy(WPFilesystem::class)
                               ->getBlogUploadsPath(23, 'some/file/foo.php', $assertSameDate)
                               ->willReturn(__DIR__);

        $revealed = $fs->reveal();

        $this->expectException(AssertionFailedError::class);

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php', new \DateTime('2019-01-01'));
    }

    /**
     * It should fail if method not expected to be called
     *
     * @test
     */
    public function should_fail_if_method_not_expected_to_be_called()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')
                   ->shouldNotBeCalled();

        $revealed = $fs->reveal();

        $this->expectException(AssertionFailedError::class);

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php');
    }

    /**
     * It should fail if method called more than expected times
     *
     * @test
     */
    public function should_fail_if_method_called_more_than_expected_times()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')
                   ->shouldBeCalledOnce();

        $revealed = $fs->reveal();

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php');

        $this->expectException(AssertionFailedError::class);

        $revealed->getBlogUploadsPath(23, 'some/file/foo.php');
    }

    /**
     * It should allow setting expectations on a method to be called at least once
     *
     * @test
     */
    public function should_allow_setting_expectations_on_a_method_to_be_called_at_least_once()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')
                   ->shouldBeCalled();

        $revealed = $fs->reveal();

        $this->expectException(AssertionFailedError::class);

        $fs->_assertPostConditions();
    }

    /**
     * It should not fail if method expected to be called at least once is called
     *
     * @test
     */
    public function should_not_fail_if_method_expected_to_be_called_at_least_once_is_called()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')
                   ->shouldBeCalled();

        $revealed = $fs->reveal();
        $revealed->getBlogUploadsPath(23, 'some/file/foo.php');

        $fs->_assertPostConditions();
    }

    /**
     * It should return the same revealed prophecy when revealing stub prophecy a second time
     *
     * @test
     */
    public function should_return_the_same_revealed_prophecy_when_revealing_stub_prophecy_a_second_time()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
                   ->getBlogUploadsPath(23, 'some/file/foo.php')
                   ->willReturn('some-path');

        $this->assertSame($fs->reveal(), $fs->reveal());
    }

    /**
     * It should allow returning a new revelead prophecy when revealing a second time anew
     *
     * @test
     */
    public function should_allow_returning_a_new_revelead_prophecy_when_revealing_a_second_time_anew()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
            ->getBlogUploadsPath(23, 'some/file/foo.php')
            ->willReturn('some-path');

        $originallyRevealed = $fs->reveal();
        $this->assertSame($originallyRevealed, $fs->reveal());
        $this->assertNotSame($fs->reveal(), $fs->reveal(true));
        $this->assertNotSame($fs->reveal(true), $fs->reveal(true));
        $this->assertSame($originallyRevealed, $fs->reveal());
    }

    /**
     * It should not cache the revealed anew prophecy if first reveal call
     *
     * @test
     */
    public function should_not_cache_the_revealed_anew_prophecy_if_first_reveal_call()
    {
        $fs = $this->stubProphecy(WPFilesystem::class)
            ->getBlogUploadsPath(23, 'some/file/foo.php')
            ->willReturn('some-path');

        $originallyRevealed = $fs->reveal(true);
        $this->assertNotSame($originallyRevealed, $fs->reveal());
        $this->assertSame($fs->reveal(), $fs->reveal());
    }

    /**
     * It should allow setting the revealed prophecy as return value in willReturn method
     *
     * @test
     */
    public function should_allow_setting_the_revealed_prophecy_as_return_value_in_will_return_method()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(23, 'some/file/foo.php')->willReturn(23);
        $fs->copyDir('foo', 'bar')->willReturn($fs->itself());

        $revealed = $fs->reveal();

        $this->assertSame($revealed, $revealed->copyDir('foo', 'bar'));
    }

    /**
     * It should support null as a valid expected parameter value
     *
     * @test
     */
    public function should_support_null_as_a_valid_expected_parameter_value()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->amInPluginPath(null)->willReturn(true);
    }

    /**
     * It should allow setting more than one expectation for method
     *
     * @test
     */
    public function should_allow_setting_more_than_one_expectation_for_method()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(23, 'some/file/foo.php')->willReturn(23);
        $fs->getBlogUploadsPath(89, 'some/file/foo.php')->willReturn(89);

        $revealed = $fs->reveal();

        $this->assertEquals(23, $revealed->getBlogUploadsPath(23, 'some/file/foo.php'));
        $this->assertEquals(89, $revealed->getBlogUploadsPath(89, 'some/file/foo.php'));
    }

    /**
     * It should allow expecting calls on the same method w/ diff arguments
     *
     * @test
     */
    public function should_allow_expecting_calls_on_the_same_method_w_diff_arguments()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(23, 'some/file/foo.php')->willReturn(23);
        $fs->getBlogUploadsPath(89, 'some/file/foo.php')->shouldBeCalled();

        $revealed = $fs->reveal();

        $this->assertEquals(23, $revealed->getBlogUploadsPath(23, 'some/file/foo.php'));

        $this->expectException(AssertionFailedError::class);

        $fs->_assertPostConditions();
    }

    /**
     * It should allow setting expectations on the type of the argument
     *
     * @test
     */
    public function should_allow_setting_expectations_on_the_type_of_the_argument()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(Arg::type('int'), Arg::type('string'))->willReturn(23);

        $revealed = $fs->reveal();

        $this->assertEquals(23, $revealed->getBlogUploadsPath(23, 'some/file/foo.php'));

        $this->expectException(AssertionFailedError::class);

        $revealed->getBlogUploadsPath('foo-bar', 'some/file/foo.php');
    }

    /**
     * It should allow setting expectations for the instanceof the input argument
     *
     * @test
     */
    public function should_allow_setting_expectations_for_the_instanceof_the_input_argument()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(Arg::type(WPFilesystem::class), Arg::type('string'))->willReturn(23);

        $revealed = $fs->reveal();

        $this->assertEquals(23, $revealed->getBlogUploadsPath($revealed, 'some/file/foo.php'));

        $this->expectException(AssertionFailedError::class);

        $revealed->getBlogUploadsPath('foo-bar', 'some/file/foo.php');
    }

    public function typeExpectationsDataProvider()
    {
        return [
        'object' => [new \stdClass()],
        'string' => ['foo-bar'],
        'null' => [null]
        ]   ;
    }
    /**
     * It should correctly format error when argument expectation is a type
     *
     * @test
     * @dataProvider typeExpectationsDataProvider
     */
    public function should_correctly_format_error_when_argument_expectation_is_a_type($input)
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(Arg::type('array'), Arg::type('string'))->willReturn(23);

        $revealed = $fs->reveal();

        try {
            $revealed->getBlogUploadsPath($input, 'some/file/foo.php');
        } catch (AssertionFailedError $e) {
            $this->assertMatchesStringSnapshot($e->getMessage());
        }
    }

    /**
     * It should allow building promises with will method
     *
     * @test
     */
    public function should_allow_building_promises_with_will_method()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(23, Arg::type('string'))->will(static function ($input) use (&$calls) {
            $calls++;
            return $input+ 23   ;
        });

        $revealed = $fs->reveal();

        $this->assertEquals(46, $revealed->getBlogUploadsPath(23, 'some/file/foo.php'));
        $this->assertEquals(1, $calls);
    }

    /**
     * It should support cetera argument expectation
     *
     * @test
     */
    public function should_support_cetera_argument_expectation()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(Arg::cetera())->willReturn(23);

        $revealed = $fs->reveal();

        $this->assertEquals(23, $revealed->getBlogUploadsPath(new \stdClass(), ['foo'=>'bar']));
    }

    /**
     * It should allow setting a closure as expected argument
     *
     * @test
     */
    public function should_allow_setting_a_closure_as_expected_argument()
    {
        $fs      = $this->stubProphecy(WPFilesystem::class);
        $closure = static function () {
        };
        $fs->getBlogUploadsPath(23, $closure)->willReturn(23);
        $fs->getBlogUploadsPath(89, $closure)->willReturn(89);

        $revealed = $fs->reveal();

        $this->assertEquals(23, $revealed->getBlogUploadsPath(23, $closure));
        $this->assertEquals(89, $revealed->getBlogUploadsPath(89, $closure));
    }

    /**
     * It should allow setting expectation on argument containing string
     *
     * @test
     */
    public function should_allow_setting_expectation_on_argument_containing_string()
    {
        $fs = $this->stubProphecy(WPFilesystem::class);
        $fs->getBlogUploadsPath(23, Arg::containingString('foo'))->willReturn(23);
        $fs->getBlogUploadsPath(89, Arg::containingString('/bar/i'))->willReturn(89);

        $revealed = $fs->reveal();


        $this->assertEquals(23, $revealed->getBlogUploadsPath(23, 'foo lorem dolor'));
        $this->assertEquals(89, $revealed->getBlogUploadsPath(89, 'FOO_BAR'));

        $this->expectException(AssertionFailedError::class);

        $revealed->getBlogUploadsPath(89, 'lorem-dolor');
    }

    /**
     * It should allow bulk setting return value and callbacks
     *
     * @test
     */
    public function should_allow_bulk_setting_return_value_and_callbacks()
    {
        $prefix = 'lorem';
        $stub = $this->stubProphecy(TestObject::class, [
            'returnInt' => 23,
            'returnFloat' => 8.9,
            'returnString' => static function () use ($prefix) {
                return $prefix . ' dolor';
            }
        ])->reveal();

        $this->assertEquals(23, $stub->returnInt());
        $this->assertEquals(8.9, $stub->returnFloat());
        $this->assertEquals('lorem dolor', $stub->returnString());
    }
}
