<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to make SerializableThrowable compatible with PHP <7.3.
 *
 * This rule:
 * 1. Adds `implements \Serializable` to the SerializableThrowable class
 * 2. Adds serialize() method that calls $this->__serialize()
 * 3. Adds unserialize($data) method that calls $this->__unserialize(unserialize($data))
 * 4. Downgrades str_contains() to strpos() !== false for PHP 7.1 compatibility
 */
class SerializableThrowableCompatibilityRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Make SerializableThrowable compatible with PHP <7.3 and downgrade str_contains()',
            [
                new CodeSample(
                    'class SerializableThrowable { }',
                    'class SerializableThrowable implements \Serializable { }'
                ),
                new CodeSample(
                    'str_contains($haystack, $needle)',
                    'strpos($haystack, $needle) !== false'
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, FuncCall::class];
    }

    /**
     * @param Class_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        if ($node instanceof FuncCall) {
            return $this->refactorStrContains($node);
        }

        return null;
    }

    /**
     * Add Serializable interface and serialize/unserialize methods to SerializableThrowable class
     */
    private function refactorClass(Class_ $node): ?Class_
    {
        // Only process the SerializableThrowable class
        if (!$this->isName($node, 'SerializableThrowable')) {
            return null;
        }

        $hasChanged = false;

        // Add implements \Serializable if not already present
        if (!$this->implementsInterface($node, 'Serializable')) {
            $node->implements[] = new FullyQualified('Serializable');
            $hasChanged = true;
        }

        // Check if serialize() method already exists
        if (!$this->hasMethod($node, 'serialize')) {
            $node->stmts[] = $this->createSerializeMethod();
            $hasChanged = true;
        }

        // Check if unserialize() method already exists
        if (!$this->hasMethod($node, 'unserialize')) {
            $node->stmts[] = $this->createUnserializeMethod();
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    /**
     * Replace str_contains() with strpos() !== false
     */
    private function refactorStrContains(FuncCall $node): ?Node
    {
        if (!$this->isName($node, 'str_contains')) {
            return null;
        }

        // str_contains($haystack, $needle) => strpos($haystack, $needle) !== false
        $strposCall = new FuncCall(
            new Name('strpos'),
            $node->args
        );

        return new NotIdentical($strposCall, $this->nodeFactory->createFalse());
    }

    /**
     * Create the serialize() method
     */
    private function createSerializeMethod(): ClassMethod
    {
        $method = new ClassMethod('serialize');
        $method->flags = Class_::MODIFIER_PUBLIC;

        // Create: return serialize($this->__serialize());
        $thisVar = new Variable('this');
        $serializeMethodCall = new MethodCall($thisVar, '__serialize');
        $serializeFuncCall = new FuncCall(
            new Name('serialize'),
            [new Node\Arg($serializeMethodCall)]
        );
        $method->stmts = [new Return_($serializeFuncCall)];

        // Add doc-block
        $method->setDocComment(new \PhpParser\Comment\Doc(
            "/**\n" .
            "     * Added to provide compatibility with PHP <7.3.\n" .
            "     */"
        ));

        return $method;
    }

    /**
     * Create the unserialize() method
     */
    private function createUnserializeMethod(): ClassMethod
    {
        $method = new ClassMethod('unserialize');
        $method->flags = Class_::MODIFIER_PUBLIC;

        // Add parameter: $data
        $dataParam = new Param(new Variable('data'));
        $method->params = [$dataParam];

        // Create: $this->__unserialize(unserialize($data));
        $dataVar = new Variable('data');
        $unserializeFuncCall = new FuncCall(
            new Name('unserialize'),
            [new Node\Arg($dataVar)]
        );
        $thisVar = new Variable('this');
        $unserializeMethodCall = new MethodCall(
            $thisVar,
            '__unserialize',
            [new Node\Arg($unserializeFuncCall)]
        );
        $method->stmts = [new Expression($unserializeMethodCall)];

        // Add doc-block
        $method->setDocComment(new \PhpParser\Comment\Doc(
            "/**\n" .
            "     * Added to provide compatibility with PHP <7.3.\n" .
            "     */"
        ));

        return $method;
    }

    /**
     * Check if class implements an interface
     */
    private function implementsInterface(Class_ $class, string $interfaceName): bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, $interfaceName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if class has a method
     */
    private function hasMethod(Class_ $class, string $methodName): bool
    {
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $this->isName($stmt, $methodName)) {
                return true;
            }
        }
        return false;
    }
}
