<?php

namespace lucatume\WPBrowser\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;
use Exception;

abstract class AbstractGenerator
{
    use Classname;
    use Namespaces;

    /**
     * @var array{namespace: string, actor: string}
     */
    protected array $settings;
    protected string $name;
    protected string $template;

    /**
     * @param array{namespace: string, actor: string} $settings The template settings.
     */
    public function __construct(array $settings, string $name)
    {
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
    }

    public function produce(): string
    {
        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . $this->name);

        return (new Template($this->template))->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->place('tester', $this->getTester())
            ->produce();
    }

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
}
