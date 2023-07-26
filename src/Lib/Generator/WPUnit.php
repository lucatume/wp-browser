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
use lucatume\WPBrowser\Compat\Compatibility;

/**
 * Class WPUnit
 *
 * @package Codeception\Lib\Generator
 */
class WPUnit
{
    use Classname;
    use Namespaces;

    /**
     * @var array{namespace: string, actor: string}
     */
    protected array $settings;

    protected string $name;

    /**
     * @var string
     */
    protected string $template = <<<EOF
<?php
{{namespace}}
class {{name}}Test extends {{baseClass}}
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

    /**
     * WPUnit constructor.
     *
     * @param array{namespace: string, actor: string} $settings The template settings.
     * @param string $name                                      The template name.
     * @param string $baseClass                                 The base class.
     */
    public function __construct(array $settings, string $name, protected string $baseClass)
    {
        $this->ensureSettings();
        $this->ensureSettingsAreAllStrings();
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
    }

    /**
     * Produces and return the rendered template.
     *
     * @return string The rendered template.
     */
    public function produce(): string
    {
        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . $this->name);

        return (new Template($this->template))->place('namespace', $ns)
            ->place('baseClass', '\\' . ltrim($this->baseClass, '\\'))
            ->place('name', $this->getShortClassName($this->name))
            ->place('tester', $this->getTester())
            ->produce();
    }

    /**
     * Returns the current tester name.
     *
     * @return string The current tester name.
     */
    protected function getTester(): string
    {
        if (isset($this->settings['actor'])) {
            $actor = $this->settings['actor'];
        }

        try {
            /** @var array{actor_suffix: string} $config */
            $config = Configuration::config();
            $propertyName = isset($config['actor_suffix']) ?
                lcfirst($config['actor_suffix'])
                : '';
        } catch (Exception) {
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
     * Checks the template settings.
     *
     * @return bool Whether the template settings are correct or not.
     */
    protected function ensureSettings(): bool
    {
        if (empty(static::$requiredSettings)) {
            return true;
        }

        foreach (static::$requiredSettings as $requiredSetting) {
            if (!isset($this->settings[$requiredSetting])) {
                throw new BadMethodCallException('Required template setting [{' . $requiredSetting . '}] is missing.');
            }
        }

        return true;
    }

    /**
     * Checks all settings are strings.
     */
    protected function ensureSettingsAreAllStrings(): bool
    {
        if (empty(static::$requiredSettings)) {
            return true;
        }

        foreach (static::$requiredSettings as $requiredSetting) {
            if (!is_string($this->settings[$requiredSetting])) {
                $message = 'Required template setting [{' . $requiredSetting . '}] is not a string.';
                throw new BadMethodCallException($message);
            }
        }

        return true;
    }
}
