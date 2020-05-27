<?php
/**
 * Provides methods to enhance a test case adding Codeception enhancements.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

use Codeception\Exception\InjectionException;
use Codeception\Lib\Di;
use Codeception\Test\Loader\Unit as CodeceptionUnitLoader;
use ReflectionMethod;

/**
 * Trait WithCodeceptionTestCaseEnhancements
 *
 * @package tad\WPBrowser\Traits
 *
 * @method getMetadata()
 */
trait WithCodeceptionTestCaseEnhancements
{

    /**
     * Enhances the test case if the 'di' service, added by Codeception, is not set on its metadata.
     *
     * @throws \ReflectionException If there's an issue reflecting on the Codeception Unit Loader class or methods.
     */
    protected function maybeEnhanceTestCaseIfWoDiService()
    {
        try {
            $this->getMetadata()->getService('di');
        } catch (InjectionException $e) {
            $loader           = new CodeceptionUnitLoader();
            $reflectionMethod = new ReflectionMethod($loader, 'enhancePhpunitTest');
            $reflectionMethod->setAccessible(true);
            $reflectionMethod->invoke($loader, $this);
            $this->getMetadata()->setServices([ 'di' => new Di() ]);
        }
    }
}
