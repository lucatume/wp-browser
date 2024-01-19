<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use PHPStan\Type\ObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class SwapEventDispatcherEventNameParameters extends AbstractRector
{

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Swap the event and name parameters of the EventDispatcherInterface::dispatch method.',
            [
                new CodeSample(
                    '$eventDispatcher->dispatch($event, $eventName);',
                    '$eventDispatcher->dispatch($eventName, $event);'
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodCallName = $this->getName($node->name);
        if ($methodCallName !== 'dispatch') {
            return null;
        }

        if (!$this->isObjectType(
            $node->var,
            new ObjectType('Symfony\Component\EventDispatcher\EventDispatcherInterface')
        )) {
            return null;
        }

        $args = $node->args;

        if (!($args[0] instanceof Node\Arg && $args[1] instanceof Node\Arg
            && $args[0]->value?->name === 'event'
            && $args[1]->value?->name === 'name')) {
            return null;
        }

        $node->args = [$args[1], $args[0]];

        return $node;
    }
}
