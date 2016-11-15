<?php
namespace Codeception\Lib\Generator;

use Codeception\Lib\Generator\Shared\Classname;
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

    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testMe()
    {
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

		return (new Template($this->template))->place('namespace', $ns)
			->place('baseClass', $this->baseClass)
			->place('name', $this->getShortClassName($this->name))
			->produce();
	}
}
