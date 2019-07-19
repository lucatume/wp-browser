<?php

namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\TestCase\WPTestCase;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class WPUnit extends AbstractGenerator implements GeneratorInterface
{
    use Classname;
    use Namespaces;

    protected $settings;
    protected $name;
    protected $baseClass;
    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends {{baseClass}}
{

    {{tester}}
    
    public function setUp(){{voidReturnType}}
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(){{voidReturnType}}
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_it_works()
    {
        \$post = static::factory()->post->create_and_get();
        
        \$this->assertInstanceOf(\\WP_Post::class, \$post);
    }
}

EOF;

    public function __construct($settings, $name, $baseClass)
    {
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
        $this->baseClass = $baseClass;
    }

    public function produce()
    {
        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . $this->name);

        $phpunitSeries = getenv('WPBROWSER_PHPUNIT_SERIES');
        if (empty($phpunitSeries) && class_exists(WPTestCase::class)) {
            $phpunitSeries = getenv('WPBROWSER_PHPUNIT_SERIES');
        }
        $voidReturnType = version_compare($phpunitSeries, '8.0', '<') ?
            ''
            : ': void';

        return (new Template($this->template))->place('namespace', $ns)
            ->place('baseClass', $this->baseClass)
            ->place('name', $this->getShortClassName($this->name))
            ->place('voidReturnType', $voidReturnType)
            ->place('tester', $this->getTester())
            ->produce();
    }

    private function getTester()
    {
        if (is_array($this->settings) && isset($this->settings['actor'])) {
            $actor = $this->settings['actor'];
        }

        try {
            $property = lcfirst(Configuration::config()['actor_suffix']);
        } catch (\Exception $e) {
            $property = '';
        }

        if (empty($property) || empty($actor)) {
            return '';
        }

        $tester = <<<EOF
    /**
     * @var \\$actor
     */
    protected $$property;
EOF;

        return trim($tester);
    }
}
