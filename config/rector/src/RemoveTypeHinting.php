<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class RemoveTypeHinting extends AbstractRector implements ConfigurableRectorInterface
{
    public const REMOVE_ALL = 'remove_all';
    public const REMOVE_RETURN_TYPE_HINTING = 'remove_return_type_hinting';
    public const REMOVE_PARAM_TYPE_HINTING = 'remove_param_type_hinting';
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

        if (!(isset($this->configuration[$className][$methodName]))) {
            return null;
        }

        $methodConfig = $this->configuration[$className][$methodName];
        $removeAll = isset($methodConfig[self::REMOVE_ALL]);

        foreach ($node->params as $param) {
            if ($removeAll) {
                $param->type = null;
                continue;
            }

            if (!$param->var instanceof Node\Expr\Variable) {
                continue;
            }

            $paramName = $param->var->name instanceof Node\Identifier ?
                $param->var->name->toString()
                : $param->var->name;

            if (!in_array($paramName, $methodConfig[self::REMOVE_PARAM_TYPE_HINTING] ?? [], true)) {
                continue;
            }

            $param->type = null;
        }

        $removeReturnTypeHinting = isset($methodConfig[self::REMOVE_RETURN_TYPE_HINTING]);
        if ($removeAll || $removeReturnTypeHinting) {
            $node->returnType = null;
        }

        return $node;
    }
}
