<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Downgrade `$x = $a ?? match (...) { ... };` to an if/else for PHP < 8.0.
 *
 * Rector's built-in DowngradeMatchToSwitchRector turns the nested match into a switch that
 * assigns $x directly but drops the `$a ??` fallback, so the provided value is ignored (e.g.
 * MachineInformation ignoring its constructor arguments and always auto-detecting the host).
 * The built-in is skipped for the affected file in rector-35.php and this rule does the whole
 * downgrade: `if ($a !== null) { $x = $a; } else { switch (subject) { ... } }`, preserving the
 * `$a ??` semantics.
 */
class DowngradeCoalesceMatchAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Downgrade an assignment of "value ?? match(...)" to if/else, preserving the coalesce',
            [
                new CodeSample(
                    '$this->os = $os ?? match ($d) { "l" => "linux", default => "?" };',
                    "if (\$os !== null) {\n    \$this->os = \$os;\n} else {\n    switch (\$d) {\n        case \"l\":\n            \$this->os = \"linux\";\n            break;\n        default:\n            \$this->os = \"?\";\n            break;\n    }\n}"
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

        return new If_(
            new NotIdentical($coalesce->left, new ConstFetch(new Name('null'))),
            [
                'stmts' => [new Expression(new Assign($target, $coalesce->left))],
                'else' => new Else_([$this->matchToSwitch($coalesce->right, $target)]),
            ]
        );
    }

    private function matchToSwitch(Match_ $match, Node $target): Switch_
    {
        $cases = [];
        foreach ($match->arms as $arm) {
            $cases = array_merge($cases, $this->armToCases($arm, $target));
        }
        return new Switch_($match->cond, $cases);
    }

    /**
     * @return Case_[]
     */
    private function armToCases(MatchArm $arm, Node $target): array
    {
        $body = [new Expression(new Assign($target, $arm->body)), new Break_()];

        // Default arm: conds is null.
        if ($arm->conds === null) {
            return [new Case_(null, $body)];
        }

        // One arm can list several conditions (`'a', 'b' => ...`): fall through to the last.
        $cases = [];
        $last = count($arm->conds) - 1;
        foreach ($arm->conds as $i => $cond) {
            $cases[] = new Case_($cond, $i === $last ? $body : []);
        }
        return $cases;
    }
}
