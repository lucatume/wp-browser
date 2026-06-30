<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to downgrade PHP_OS_FAMILY (PHP 7.2+) to PHP_OS (PHP 7.1).
 *
 * This rule:
 * 1. Replaces `PHP_OS_FAMILY === 'Windows'` with `strtolower(substr(PHP_OS, 0, 3)) === 'win'`
 * 2. Replaces `PHP_OS_FAMILY !== 'Windows'` with `strtolower(substr(PHP_OS, 0, 3)) !== 'win'`
 * 3. Replaces other uses of `PHP_OS_FAMILY` with `PHP_OS`
 */
class DowngradePhpOsFamily extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Downgrade PHP_OS_FAMILY to PHP_OS for PHP 7.1 compatibility',
            [
                new CodeSample(
                    'if (PHP_OS_FAMILY === \'Windows\') {}',
                    'if (strtolower(substr(PHP_OS, 0, 3)) === \'win\') {}'
                ),
                new CodeSample(
                    'if (PHP_OS_FAMILY !== \'Windows\') {}',
                    'if (strtolower(substr(PHP_OS, 0, 3)) !== \'win\') {}'
                ),
                new CodeSample(
                    'substr(PHP_OS_FAMILY, 0, 3)',
                    'substr(PHP_OS, 0, 3)'
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class, ConstFetch::class];
    }

    /**
     * @param Identical|NotIdentical|ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identical || $node instanceof NotIdentical) {
            return $this->refactorComparison($node);
        }

        if ($node instanceof ConstFetch) {
            return $this->refactorConstFetch($node);
        }

        return null;
    }

    /**
     * Refactor comparison operations (=== or !==) involving PHP_OS_FAMILY and 'Windows'
     */
    private function refactorComparison(Identical|NotIdentical $node): ?Node
    {
        // Check if one side is PHP_OS_FAMILY constant and the other is 'Windows'
        $isPhpOsFamily = false;
        $isWindowsString = false;

        if ($node->left instanceof ConstFetch && $this->isName($node->left, 'PHP_OS_FAMILY')) {
            $isPhpOsFamily = true;
            if ($node->right instanceof String_ && $node->right->value === 'Windows') {
                $isWindowsString = true;
            }
        } elseif ($node->right instanceof ConstFetch && $this->isName($node->right, 'PHP_OS_FAMILY')) {
            $isPhpOsFamily = true;
            if ($node->left instanceof String_ && $node->left->value === 'Windows') {
                $isWindowsString = true;
            }
        }

        if (!$isPhpOsFamily || !$isWindowsString) {
            return null;
        }

        // Build: strtolower(substr(PHP_OS, 0, 3))
        $phpOsConst = new ConstFetch(new Name('PHP_OS'));
        $substrCall = new FuncCall(
            new Name('substr'),
            [
                new Node\Arg($phpOsConst),
                new Node\Arg(new Node\Scalar\LNumber(0)),
                new Node\Arg(new Node\Scalar\LNumber(3))
            ]
        );
        $strtolowerCall = new FuncCall(
            new Name('strtolower'),
            [new Node\Arg($substrCall)]
        );

        // Create the 'win' string
        $winString = new String_('win');

        // Create the comparison
        if ($node instanceof Identical) {
            return new Identical($strtolowerCall, $winString);
        } else {
            return new NotIdentical($strtolowerCall, $winString);
        }
    }

    /**
     * Refactor direct PHP_OS_FAMILY constant fetch to PHP_OS
     */
    private function refactorConstFetch(ConstFetch $node): ?Node
    {
        if (!$this->isName($node, 'PHP_OS_FAMILY')) {
            return null;
        }

        // Don't replace if this is part of a comparison we already handled
        $parent = $node->getAttribute('parent');
        if ($parent instanceof Identical || $parent instanceof NotIdentical) {
            // Check if the comparison is with 'Windows'
            if ($parent->left instanceof String_ && $parent->left->value === 'Windows') {
                return null; // Already handled by refactorComparison
            }
            if ($parent->right instanceof String_ && $parent->right->value === 'Windows') {
                return null; // Already handled by refactorComparison
            }
        }

        // Replace PHP_OS_FAMILY with PHP_OS
        return new ConstFetch(new Name('PHP_OS'));
    }
}