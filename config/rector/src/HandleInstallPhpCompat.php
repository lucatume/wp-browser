<?php

namespace lucatume\Rector;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to handle WordPress 6.1 file rename compatibility in install.php.
 *
 * Transforms:
 *   require_once ABSPATH . 'wp-includes/class-wpdb.php';
 *
 * Into:
 *   require_once file_exists(ABSPATH . 'wp-includes/class-wpdb.php')
 *       ? ABSPATH . 'wp-includes/class-wpdb.php'
 *       : ABSPATH . 'wp-includes/wp-db.php';
 */
class HandleInstallPhpCompat extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handle WordPress 6.1 file rename for class-wpdb.php vs wp-db.php',
            [
                new CodeSample(
                    "require_once ABSPATH . 'wp-includes/class-wpdb.php';",
                    "require_once file_exists(ABSPATH . 'wp-includes/class-wpdb.php')\n    ? ABSPATH . 'wp-includes/class-wpdb.php'\n    : ABSPATH . 'wp-includes/wp-db.php';"
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
        if (!$node->expr instanceof Node\Expr\Include_) {
            return null;
        }

        $include = $node->expr;

        // Check if this is a require_once
        if ($include->type !== Node\Expr\Include_::TYPE_REQUIRE_ONCE) {
            return null;
        }

        // Check if the expression is ABSPATH . 'wp-includes/class-wpdb.php'
        if (!$include->expr instanceof Concat) {
            return null;
        }

        $concat = $include->expr;

        // Check for ABSPATH constant on the left
        if (!$concat->left instanceof ConstFetch) {
            return null;
        }

        if ($concat->left->name->toString() !== 'ABSPATH') {
            return null;
        }

        // Check for the specific string on the right
        if (!$concat->right instanceof String_) {
            return null;
        }

        if ($concat->right->value !== 'wp-includes/class-wpdb.php') {
            return null;
        }

        // Create the ternary expression with file_exists check
        $classWpdbPath = new Concat(
            new ConstFetch(new Name('ABSPATH')),
            new String_('wp-includes/class-wpdb.php')
        );

        $wpDbPath = new Concat(
            new ConstFetch(new Name('ABSPATH')),
            new String_('wp-includes/wp-db.php')
        );

        $fileExistsCheck = new FuncCall(
            new Name('file_exists'),
            [new Arg($classWpdbPath)]
        );

        $ternary = new Ternary(
            $fileExistsCheck,
            new Concat(
                new ConstFetch(new Name('ABSPATH')),
                new String_('wp-includes/class-wpdb.php')
            ),
            $wpDbPath
        );

        $include->expr = $ternary;

        // Add a doc comment explaining the change
        $docComment = new Doc(
            "/**\n * File was renamed in WordPress 6.1.\n *\n * @see https://core.trac.wordpress.org/ticket/56268\n * @see https://github.com/WordPress/WordPress/commit/8484c7babb6b6ee951f83babea656a294157665d\n */"
        );
        $node->setDocComment($docComment);

        return $node;
    }
}
