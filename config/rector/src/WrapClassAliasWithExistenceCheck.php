<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to wrap class_alias() calls with class_exists() checks.
 *
 * Transforms:
 *   class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
 *
 * Into:
 *   if (!class_exists('PHPUnit_Framework_TestCase')) {
 *       class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
 *   }
 */
class WrapClassAliasWithExistenceCheck extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Wrap class_alias() calls with class_exists() checks',
            [
                new CodeSample(
                    "class_alias('PHPUnit\\Framework\\TestCase', 'PHPUnit_Framework_TestCase');",
                    "if (!class_exists('PHPUnit_Framework_TestCase')) {\n    class_alias('PHPUnit\\Framework\\TestCase', 'PHPUnit_Framework_TestCase');\n}"
                )
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
        if (!$node->expr instanceof FuncCall) {
            return null;
        }

        $funcCall = $node->expr;

        if (!$funcCall->name instanceof Name) {
            return null;
        }

        if ($funcCall->name->toString() !== 'class_alias') {
            return null;
        }

        // Check if we have at least 2 arguments
        if (count($funcCall->args) < 2) {
            return null;
        }

        // Check if this is already wrapped in an if statement with class_exists
        $parent = $node->getAttribute('parent');
        if ($parent instanceof If_) {
            // Check if the parent if is a class_exists check
            $cond = $parent->cond;
            if ($cond instanceof BooleanNot && $cond->expr instanceof FuncCall) {
                $funcName = $cond->expr->name;
                if ($funcName instanceof Name && $funcName->toString() === 'class_exists') {
                    // Already wrapped with class_exists, skip
                    return null;
                }
            }
        }

        // Get the alias name (second argument)
        $aliasArg = $funcCall->args[1];
        if (!$aliasArg instanceof Node\Arg) {
            return null;
        }

        // Create: if (!class_exists('AliasName')) { class_alias(...); }
        return new If_(
            new BooleanNot(
                new FuncCall(
                    new Name('class_exists'),
                    [$aliasArg]
                )
            ),
            [
                'stmts' => [$node]
            ]
        );
    }
}
