<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class RemoveClassMethodTypeHinting extends AbstractRector implements ConfigurableRectorInterface
{
    private array $configuration;
    private ?Class_ $currentClass = null;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Update a method to conform to parent method signature',
            [
                new ConfiguredCodeSample(
                    'public function _cleanup(string $databaseKey = null, array $databaseConfig = null): void',
                    'public function _cleanup($databaseKey = null, $databaseConfig = null)',
                    $this->configuration
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class];
    }

    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node instanceof Class_ ? $this->handleClassNode($node) : $this->handleMethodNode($node);
    }

    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function handleClassNode(Class_ $node): Class_
    {
        $className = $node->namespacedName ? $node->namespacedName->toString() : $node->name->toString();
        if (isset($this->configuration[$className])) {
            $this->currentClass = $node;
        } else {
            $this->currentClass = null;
        }
        return $node;
    }

    private function handleMethodNode(ClassMethod $node): ?ClassMethod
    {
        if ($this->currentClass === null) {
            return null;
        }

        $className = $this->currentClass->namespacedName ? $this->currentClass->namespacedName->toString(
        ) : $node->name->toString();
        $methodName = $node->name->toString();

        if (!(isset($this->configuration[$className]) && in_array(
            $methodName,
            $this->configuration[$className],
            true
        ))) {
            return null;
        }

        $node->params = array_map(function (Param $param) {
            $param->type = null;
            return $param;
        }, $node->params);
        $node->returnType = null;

        return $node;
    }
}
