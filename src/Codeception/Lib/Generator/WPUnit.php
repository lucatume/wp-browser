<?php
/**
 * The WordPress unit test case template.
 *
 * @package Codeception\Lib\Generator
 */

namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;
use tad\WPBrowser\Compat\Compatibility;

/**
 * Class WPUnit
 *
 * @package Codeception\Lib\Generator
 */
class WPUnit extends AbstractGenerator implements GeneratorInterface
{
    use Classname;
    use Namespaces;

    /**
     * @var array<string,mixed>
     */
    protected $settings;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $baseClass;

    /**
     * @var string
     */
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

    /**
     * The injectable compatibility layer.
     *
     * @var Compatibility
     */
    protected $compatibilityLayer;

    /**
     * WPUnit constructor.
     *
     * @param array<string,mixed> $settings The template settings.
     * @param string $name The template name.
     * @param string $baseClass The base class.
     */
    public function __construct($settings, $name, $baseClass)
    {
        parent::__construct($settings);
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
        $this->baseClass = $baseClass;
        $this->compatibilityLayer = new Compatibility();
    }

    /**
     * Produces and return the rendered template.
     *
     * @return string The rendered template.
     */
    public function produce()
    {
        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . $this->name);

        $phpunitSeries = $this->compatibilityLayer->phpunitVersion();

        /** @var string $phpunitSeries */
        $voidReturnType = is_string($phpunitSeries) && version_compare($phpunitSeries, '8.0', '<') ?
            ''
            : ': void';

        return (new Template($this->template))->place('namespace', $ns)
            ->place('baseClass', $this->baseClass)
            ->place('name', $this->getShortClassName($this->name))
            ->place('voidReturnType', $voidReturnType)
            ->place('tester', $this->getTester())
            ->produce();
    }

    /**
     * Returns the current tester name.
     *
     * @return string The current tester name.
     */
    protected function getTester()
    {
        if (is_array($this->settings) && isset($this->settings['actor'])) {
            $actor = $this->settings['actor'];
        }

        try {
            $config = Configuration::config();
            $propertyName = isset($config['actor_suffix']) ?
                lcfirst($config['actor_suffix'])
                : '';
        } catch (\Exception $e) {
            $propertyName = '';
        }

        if (!isset($actor)) {
            return '';
        }

        $testerFrag = <<<EOF
    /**
     * @var \\$actor
     */
    protected $$propertyName;
    
EOF;

        return ltrim($testerFrag);
    }

    /**
     * Injects the compatibility layer object.
     *
     * @param Compatibility $compatibility An instance of the compatibility layer.
     *
     * @return void
     */
    public function setCompatibilityLayer(Compatibility $compatibility)
    {
        $this->compatibilityLayer = $compatibility;
    }
}
