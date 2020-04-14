<?php namespace tad\WPBrowser;

use Codeception\Exception\ConfigurationException;
use Codeception\Lib\ModuleContainer;

class wpbrowserTest extends \Codeception\Test\Unit
{
    /**
     * It should throw if a module requirement is not satisfied
     *
     * @test
     */
    public function should_throw_if_a_module_requirement_is_not_satisfied()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }

        $this->expectException(ConfigurationException::class);

        requireCodeceptionModules('TestModule', ['NotExisting']);
    }


    /**
     * It should throw if one of required modules is not present
     *
     * @test
     */
    public function should_throw_if_one_of_required_modules_is_not_present()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }

        $this->expectException(ConfigurationException::class);

        requireCodeceptionModules('TestModule', ['NotExisting', 'Filesystem']);
    }

    /**
     * It should throw message with information about all missing requirements
     *
     * @test
     */
    public function should_throw_message_with_information_about_all_missing_requirements()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }

        ModuleContainer::$packages['ModuleOne'] = 'lucatume/module-one';
        ModuleContainer::$packages['ModuleTwo'] = 'lucatume/module-two';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageRegExp('/.*ModuleOne.*ModuleTwo.*lucatume\\/module-one.*lucatume\\/module-two/us');

        requireCodeceptionModules('TestModule', [ 'ModuleOne', 'Filesystem', 'ModuleTwo' ]);
    }

    /**
     * It should not throw if module requirements are met
     *
     * @test
     */
    public function should_not_throw_if_module_requirements_are_met()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }

        requireCodeceptionModules('TestModule', [ 'Db', 'Filesystem' ]);
    }

    /**
     * It should allow specifying the module by fully-qualified class name
     *
     * @test
     */
    public function should_allow_specifying_the_module_by_fully_qualified_class_name()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }

        requireCodeceptionModules('TestModule', ['\\Codeception\\Lib\\Framework' ]);
    }

    /**
     * It should throw if required module specified by fully qualified class name does not exist
     *
     * @test
     */
    public function should_throw_if_required_module_specified_by_fully_qualified_class_name_does_not_exist()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageRegExp('/\\\\Codeception\\\\Lib\\\\NotExisting/us');

        requireCodeceptionModules('TestModule', ['\\Codeception\\Lib\\NotExisting' ]);
    }

    /**
     * It should throw correct message when mix of modules and components are missing
     *
     * @test
     */
    public function should_throw_correct_message_when_mix_of_modules_and_components_are_missing()
    {
        if (! property_exists(ModuleContainer::class, 'packages')) {
            $this->markTestSkipped('This test will require Codeception 4.0+');
        }
        ModuleContainer::$packages['ModuleOne'] = 'lucatume/module-one';

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageRegExp('/\\\\Codeception\\\\Lib\\\\NotExisting.*ModuleOne.*lucatume\\/module-one/us');

        requireCodeceptionModules('TestModule', ['\\Codeception\\Lib\\NotExisting', 'Db', 'ModuleOne' ]);
    }
}
