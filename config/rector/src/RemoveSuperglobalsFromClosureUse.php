<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Strip superglobals from a closure's `use ()` clause.
 *
 * DowngradeArrowFunctionRector converts `fn() => $GLOBALS['x']` into a closure that
 * captures every referenced variable, including superglobals. Superglobals are always
 * in scope and are illegal as lexical variables, so `use ($GLOBALS)` is a fatal error.
 * This rule runs after the downgrade and removes them.
 */
class RemoveSuperglobalsFromClosureUse extends AbstractRector
{
    private const SUPERGLOBALS = [
        'GLOBALS', '_GET', '_POST', '_SERVER', '_FILES',
        '_COOKIE', '_SESSION', '_REQUEST', '_ENV',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove superglobals from a closure use() clause (illegal as lexical variables)',
            [
                new CodeSample(
                    'function () use ($GLOBALS) { return $GLOBALS["x"]; };',
                    'function () { return $GLOBALS["x"]; };'
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Closure::class];
    }

    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->uses === []) {
            return null;
        }

        $kept = array_filter($node->uses, function ($use): bool {
            return !($use->var instanceof Variable
                && is_string($use->var->name)
                && in_array($use->var->name, self::SUPERGLOBALS, true));
        });

        if (count($kept) === count($node->uses)) {
            return null;
        }

        $node->uses = array_values($kept);

        return $node;
    }
}
