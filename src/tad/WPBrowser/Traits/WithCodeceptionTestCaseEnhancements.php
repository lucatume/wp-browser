<?php
/**
 * Provides methods to enhance a test case adding Codeception enhancements.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

use Codeception\Exception\InjectionException;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Di;
use Codeception\Module\WPLoader;
use Codeception\Test\Loader\Unit as CodeceptionUnitLoader;
use ReflectionMethod;
use tad\WPBrowser\PHPUnit\TestListener;

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
        } catch (InjectionException $e) {
            $loader           = new CodeceptionUnitLoader();
            $reflectionMethod = new ReflectionMethod($loader, 'enhancePhpunitTest');
            $reflectionMethod->setAccessible(true);
            $reflectionMethod->invoke($loader, $this);
            $this->getMetadata()->setServices([ 'di' => new Di() ]);
        }
    }

    /**
     * Checks the test method is correctly configured to run in a separate process.
     *
     * @throws TestRuntimeException If the test method, or test case, is configured to run in a separate process
     *                              preserving the global state.
     */
    protected function checkSeparateProcessConfiguration()
    {
        if (! method_exists($this, 'getAnnotations')) {
            return;
        }

        $annotationGroups = $this->getAnnotations();

        foreach ([ 'class', 'method' ] as $annotationGroup) {
            if (! isset($annotationGroups[ $annotationGroup ])) {
                continue;
            }

            $a = array_combine(
                array_keys($annotationGroups[ $annotationGroup ]),
                array_column((array) $annotationGroups[ $annotationGroup ], 0)
            );

            if (isset($a['runInSeparateProcess'])) {
                if (isset($a['preserveGlobalState']) && $a['preserveGlobalState'] !== 'disabled'
                    || ! isset($a['preserveGlobalState'])
                ) {
                    $message = <<< OUT
Running WPTestCase tests in a separate processes requires the following annotations on the test case or test methods:

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
 
Read more at: 
* https://phpunit.readthedocs.io/en/9.1/annotations.html?highlight=runInSeparateProcess#runinseparateprocess
* https://wpbrowser.wptestkit.dev/advanced/run-in-separate-process

OUT;
                    throw new TestRuntimeException($message);
                }
            }
        }
    }
}
