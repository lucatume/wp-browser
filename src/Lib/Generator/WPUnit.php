<?php
/**
 * The WordPress unit test case template.
 *
 * @package Codeception\Lib\Generator
 */

namespace lucatume\WPBrowser\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;
use Exception;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;

/**
 * Class WPUnit
 *
 * @package Codeception\Lib\Generator
 */
class WPUnit extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    {{tester}}
    public function setUp() :void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown() :void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_factory() :void
    {
        \$post = static::factory()->post->create_and_get();
        
        \$this->assertInstanceOf(\\WP_Post::class, \$post);
    }
}
EOF;
}
