<?php
/**
 * Provides methods to enhance a test case adding Codeception enhancements.
 *
 * @package lucatume\WPBrowser\Traits
 */

namespace lucatume\WPBrowser\Traits;

use Codeception\Exception\InjectionException;
use Codeception\Lib\Di;
use Codeception\Test\Loader\Unit as CodeceptionUnitLoader;
use lucatume\WPBrowser\Module\WPLoader;
use ReflectionMethod;
use lucatume\WPBrowser\PHPUnit\TestListener;

/**
 * Trait WithCodeceptionTestCaseEnhancements
 *
 * @package lucatume\WPBrowser\Traits
 *
 * @method getMetadata()
 */
trait WithCodeceptionTestCaseEnhancements
{
    /**
     * Tries to set up the WPLoader module at the very last useful moment.
     *
     * This is required when the test runs in a separate process.
     */
    protected static function setupForSeparateProcessBeforeClass()
    {
        WPLoader::_maybeInit();
    }

    /**
     * Enhances the test case if the 'di' service, added by Codeception, is not set on its metadata.
     *
     * @throws \ReflectionException If there's an issue reflecting on the Codeception Unit Loader class or methods.
     */
    protected function maybeEnhanceTestCaseIfWoDiService()
    {
        try {
            $this->getMetadata()->getService('di');
        } catch (InjectionException) {
            $loader           = new CodeceptionUnitLoader();
            $reflectionMethod = new ReflectionMethod($loader, 'enhancePhpunitTest');
            $reflectionMethod->setAccessible(true);
            $reflectionMethod->invoke($loader, $this);
            $this->getMetadata()->setServices([ 'di' => new Di() ]);
        }
    }
}
