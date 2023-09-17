<?php


namespace lucatume\WPBrowser\TestCase;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Metadata;
use Codeception\Test\TestCaseWrapper;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Tests\TestCase\WooCommerceLoadTestCase;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Utils\Env;

class WPTestCaseTest extends Unit
{
    use LoopIsolation;

    /**
     * It should correctly run tests when loading WooCommerce
     *
     * @test
     */
    public function should_correctly_run_tests_when_loading_woo_commerce(): void
    {
        // Configure and build the WPLoader module to load WooCommerce.
        $moduleContainer = new ModuleContainer(new Di(), []);
        $config = [
            'wpRootFolder' => Env::get('WORDPRESS_ROOT_DIR'),
            'dbName' => Env::get('WORDPRESS_DB_NAME'),
            'dbHost' => Env::get('WORDPRESS_DB_HOST'),
            'dbUser' => Env::get('WORDPRESS_DB_USER'),
            'dbPassword' => Env::get('WORDPRESS_DB_PASSWORD'),
            'plugins' => ['woocommerce/woocommerce.php'],
        ];
        $wpLoaderModule = new WPLoader($moduleContainer, $config);

        $this->assertInIsolation(static function () use ($wpLoaderModule): void {
            // Initialize the module, load WordPress.
            $wpLoaderModule->_initialize();

            $testCase = new WooCommerceLoadTestCase();
            $metadata = new Metadata();
            $metadata->setServices(['di' => new Di()]);
            $testCase->setMetadata($metadata);

            // Run the first test.
            $testCase->setName('testWordPressLoadedCorrectly');
            $wrapper = new TestCaseWrapper($testCase);
            $wrapper->test();

            // Run the second test.
            $testCase->setName('testWooCommerceIsActivated');
            $wrapper = new TestCaseWrapper($testCase);
            $wrapper->test();

            // Run the third test.
            $testCase->setName('testWooCommerceFunctionsExist');
            $wrapper = new TestCaseWrapper($testCase);
            $wrapper->test();
        });
    }
}
