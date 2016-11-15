<?php
namespace Codeception\Lib\Generator;


class AcceptanceSuiteConfigTest extends \Codeception\Test\Unit
{
	protected $backupGlobals = false;
	protected $classUnderTest = AcceptanceSuiteConfig::class;

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	public function singleSettings()
	{
		return array_map(function ($setting) {
			return [$setting];
		}, $this->getClassRequiredSettings());
	}

	protected function getClassRequiredSettings()
	{
		$class = $this->classUnderTest;
		return $class::$requiredSettings;
	}

	/**
	 * @test
	 * it should throw if required setting is missing
	 * @dataProvider singleSettings
	 */
	public function it_should_throw_if_required_setting_is_missing($unsetSetting)
	{
		$settings = $this->getClassRequiredSettings();
		$settings = array_combine($settings, $settings);
		unset($settings[$unsetSetting]);


		$this->expectException(\BadMethodCallException::class);

		$class = $this->classUnderTest;
		new $class($settings);
	}

	/**
	 * @test
	 * it should throw if a setting is not a string
	 * @dataProvider singleSettings
	 */
	public function it_should_throw_if_a_setting_is_not_a_string($notStringSetting)
	{
		$settings = $this->getClassRequiredSettings();
		$settings = array_combine($settings, $settings);
		$settings[$notStringSetting] = ['foo' => 'bar'];


		$this->expectException(\BadMethodCallException::class);

		$class = $this->classUnderTest;
		new $class($settings);
	}
}
