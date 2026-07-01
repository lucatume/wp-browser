<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Downgrade ReflectionFunction::getClosureCalledClass() (PHP 8.1+) for PHP < 8.1.
 *
 * Rewrites `$r->getClosureCalledClass()` to
 * `method_exists($r, 'getClosureCalledClass') ? $r->getClosureCalledClass() : $r->getClosureScopeClass()`.
 * On PHP < 8.1 the scope class is the closest available ReflectionClass; both return
 * ReflectionClass|null, so callers are unaffected.
 */
class DowngradeGetClosureCalledClassRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Downgrade ReflectionFunction::getClosureCalledClass() to a PHP < 8.1 safe equivalent',
            [
                new CodeSample(
                    '$class = $reflection->getClosureCalledClass();',
                    '$class = method_exists($reflection, \'getClosureCalledClass\') '
                    . '? $reflection->getClosureCalledClass() : $reflection->getClosureScopeClass();'
                ),
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
        if (!$node->name instanceof Identifier || $node->name->toString() !== 'getClosureCalledClass') {
            return null;
        }

        // Already wrapped (avoid infinite recursion on re-traversal).
        $parent = $node->getAttribute('parent');
        if ($parent instanceof Ternary) {
            return null;
        }

        $receiver = $node->var;

        $guard = new FuncCall(
            new Name('method_exists'),
            [new Arg($receiver), new Arg(new String_('getClosureCalledClass'))]
        );

        $scopeCall = new MethodCall($receiver, new Identifier('getClosureScopeClass'));

        return new Ternary($guard, clone $node, $scopeCall);
    }
}
