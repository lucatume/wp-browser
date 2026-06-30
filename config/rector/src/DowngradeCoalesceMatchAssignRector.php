<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Split `$x = $a ?? match (...) { ... };` into an if/else before the match is downgraded.
 *
 * Rector's DowngradeMatchToSwitchRector turns the nested match into a switch that assigns $x
 * directly but drops the `$a ??` fallback, so the provided value is ignored (e.g.
 * MachineInformation ignoring its constructor arguments and always auto-detecting the host).
 * Rewriting the coalesce into an explicit if/else keeps the match in a plain `$x = match(...)`
 * position the downgrade handles correctly, and preserves the `$a ??` semantics.
 */
class DowngradeCoalesceMatchAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Split an assignment of "value ?? match(...)" into if/else so the coalesce survives the match downgrade',
            [
                new CodeSample(
                    '$this->os = $os ?? match ($d) { "l" => "linux", default => "?" };',
                    "if (\$os !== null) {\n    \$this->os = \$os;\n} else {\n    \$this->os = match (\$d) { \"l\" => \"linux\", default => \"?\" };\n}"
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        $assign = $node->expr;
        if (!$assign instanceof Assign || !$assign->expr instanceof Coalesce) {
            return null;
        }

        $coalesce = $assign->expr;
        if (!$coalesce->right instanceof Match_) {
            return null;
        }

        $target = $assign->var;
        $fallbackValue = $coalesce->left;

        return new If_(
            new NotIdentical($fallbackValue, new ConstFetch(new Name('null'))),
            [
                'stmts' => [new Expression(new Assign($target, $fallbackValue))],
                'else' => new Else_([new Expression(new Assign($target, $coalesce->right))]),
            ]
        );
    }
}
