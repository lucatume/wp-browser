<?php

namespace lucatume\Rector;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to handle WordPress 6.8 file rename compatibility in mock-mailer.php.
 *
 * Transforms:
 *   require_once ABSPATH . 'wp-includes/class-wp-phpmailer.php';
 *
 * Into:
 *   if ( is_file( ABSPATH . 'wp-includes/class-wp-phpmailer.php' ) ) {
 *       // The two require_once above are already loading the correct files.
 *       include_once ABSPATH . 'wp-includes/class-wp-phpmailer.php';
 *   } else {
 *       // Deprecated in version 5.5.0.
 *       // Removed in version 6.8.0 in favor of the class-wp-phpmailer.php file.
 *       include_once ABSPATH . 'wp-includes/class-phpmailer.php';
 *       class_alias( 'PHPMailer\PHPMailer\PHPMailer', 'WP_PHPMailer' );
 *   }
 */
class HandleMockMailerPhpCompat extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handle WordPress 6.8 file rename for class-wp-phpmailer.php vs class-phpmailer.php',
            [
                new CodeSample(
                    "require_once ABSPATH . 'wp-includes/class-wp-phpmailer.php';",
                    "if ( is_file( ABSPATH . 'wp-includes/class-wp-phpmailer.php' ) ) {\n    include_once ABSPATH . 'wp-includes/class-wp-phpmailer.php';\n} else {\n    include_once ABSPATH . 'wp-includes/class-phpmailer.php';\n    class_alias( 'PHPMailer\\PHPMailer\\PHPMailer', 'WP_PHPMailer' );\n}"
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
        if (!$node->expr instanceof Include_) {
            return null;
        }

        $include = $node->expr;

        // Check if this is a require_once
        if ($include->type !== Include_::TYPE_REQUIRE_ONCE) {
            return null;
        }

        // Check if the expression is ABSPATH . 'wp-includes/class-wp-phpmailer.php'
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

        if ($concat->right->value !== 'wp-includes/class-wp-phpmailer.php') {
            return null;
        }

        // Create the path expressions
        $classWpPhpMailerPath = new Concat(
            new ConstFetch(new Name('ABSPATH')),
            new String_('wp-includes/class-wp-phpmailer.php')
        );

        $classPhpMailerPath = new Concat(
            new ConstFetch(new Name('ABSPATH')),
            new String_('wp-includes/class-phpmailer.php')
        );

        // Create the is_file() condition
        $isFileCheck = new FuncCall(
            new Name('is_file'),
            [new Arg($classWpPhpMailerPath)]
        );

        // Create the if branch: include_once ABSPATH . 'wp-includes/class-wp-phpmailer.php';
        $ifInclude = new Include_(
            new Concat(
                new ConstFetch(new Name('ABSPATH')),
                new String_('wp-includes/class-wp-phpmailer.php')
            ),
            Include_::TYPE_INCLUDE_ONCE
        );
        $ifStmt = new Expression($ifInclude);
        $ifStmt->setDocComment(new \PhpParser\Comment\Doc('// The two require_once above are already loading the correct files.'));

        // Create the else branch
        // 1. include_once ABSPATH . 'wp-includes/class-phpmailer.php';
        $elseInclude = new Include_(
            $classPhpMailerPath,
            Include_::TYPE_INCLUDE_ONCE
        );
        $elseIncludeStmt = new Expression($elseInclude);
        $elseIncludeStmt->setDocComment(new \PhpParser\Comment\Doc("// Deprecated in version 5.5.0.\n// Removed in version 6.8.0 in favor of the class-wp-phpmailer.php file."));

        // 2. class_alias( 'PHPMailer\PHPMailer\PHPMailer', 'WP_PHPMailer' );
        $classAlias = new FuncCall(
            new Name('class_alias'),
            [
                new Arg(new String_('PHPMailer\\PHPMailer\\PHPMailer')),
                new Arg(new String_('WP_PHPMailer'))
            ]
        );
        $classAliasStmt = new Expression($classAlias);

        // Create the if/else statement
        $ifStatement = new If_($isFileCheck);
        $ifStatement->stmts = [$ifStmt];
        $ifStatement->elseifs = [];
        $ifStatement->else = new Else_([$elseIncludeStmt, $classAliasStmt]);

        return $ifStatement;
    }
}
