<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Catch_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to prefix global class references with a backslash in namespaced files.
 *
 * When a file is in a namespace, references to global classes need to be prefixed
 * with a backslash to ensure they resolve correctly.
 *
 * This rule hooks into Namespace_ nodes and traverses children to find class references
 * that need prefixing. This approach is compatible with Rector 0.17+ which removed
 * parent node attribute lookup.
 */
class PrefixGlobalClassNames extends AbstractRector
{
    /**
     * Built-in PHP types that should not be prefixed.
     */
    private const BUILT_IN_TYPES = [
        'int', 'float', 'string', 'bool', 'array', 'object', 'callable',
        'iterable', 'void', 'null', 'mixed', 'never', 'false', 'true',
        'self', 'static', 'parent'
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Prefix global class references with backslash in namespaced files',
            [
                new CodeSample(
                    "namespace Foo;\nnew WP_Error();",
                    "namespace Foo;\nnew \\WP_Error();"
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Namespace_) {
            return null;
        }

        $modified = false;

        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use (&$modified): ?Node {
            $result = $this->processNode($subNode);
            if ($result !== null) {
                $modified = true;
                return $result;
            }
            return null;
        });

        return $modified ? $node : null;
    }

    /**
     * Process a single node and return modified node if changed.
     */
    private function processNode(Node $node): ?Node
    {
        if ($node instanceof New_) {
            return $this->handleNew($node);
        }

        if ($node instanceof Instanceof_) {
            return $this->handleInstanceof($node);
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            return $this->handleFunctionParams($node);
        }

        if ($node instanceof StaticCall) {
            return $this->handleStaticCall($node);
        }

        if ($node instanceof StaticPropertyFetch) {
            return $this->handleStaticPropertyFetch($node);
        }

        if ($node instanceof Class_) {
            return $this->handleClassImplements($node);
        }

        if ($node instanceof Catch_) {
            return $this->handleCatch($node);
        }

        return null;
    }

    /**
     * Get the original unresolved class name from a Name node.
     * Returns null if the name was already fully qualified or doesn't need prefixing.
     */
    private function getOriginalClassName(Name $name): ?string
    {
        // Get the original name before resolution (Rector name resolver transforms names)
        $originalName = $name->getAttribute('originalName');

        // If there's no original name, this was already written as fully qualified
        if ($originalName === null) {
            return null;
        }

        // Check if the original was fully qualified
        if ($originalName instanceof FullyQualified) {
            return null;
        }

        if (!$originalName instanceof Name) {
            return null;
        }

        $className = $originalName->toString();

        // Check if the original name should be prefixed
        if (!$this->shouldPrefixClass($className)) {
            return null;
        }

        return $className;
    }

    private function handleNew(New_ $node): ?New_
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $className = $this->getOriginalClassName($node->class);
        if ($className === null) {
            return null;
        }

        $node->class = new FullyQualified($className);
        return $node;
    }

    private function handleInstanceof(Instanceof_ $node): ?Instanceof_
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $className = $this->getOriginalClassName($node->class);
        if ($className === null) {
            return null;
        }

        $node->class = new FullyQualified($className);
        return $node;
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    private function handleFunctionParams(Node $node): ?Node
    {
        $modified = false;

        // Handle parameter types
        foreach ($node->params as $param) {
            if ($param->type instanceof Name) {
                $typeName = $this->getOriginalClassName($param->type);
                if ($typeName !== null) {
                    $param->type = new FullyQualified($typeName);
                    $modified = true;
                }
            }
        }

        // Handle return type
        if ($node->returnType instanceof Name) {
            $typeName = $this->getOriginalClassName($node->returnType);
            if ($typeName !== null) {
                $node->returnType = new FullyQualified($typeName);
                $modified = true;
            }
        }

        return $modified ? $node : null;
    }

    private function handleStaticCall(StaticCall $node): ?StaticCall
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $className = $this->getOriginalClassName($node->class);
        if ($className === null) {
            return null;
        }

        $node->class = new FullyQualified($className);
        return $node;
    }

    private function handleStaticPropertyFetch(StaticPropertyFetch $node): ?StaticPropertyFetch
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $className = $this->getOriginalClassName($node->class);
        if ($className === null) {
            return null;
        }

        $node->class = new FullyQualified($className);
        return $node;
    }

    private function handleClassImplements(Class_ $node): ?Class_
    {
        $modified = false;

        foreach ($node->implements as $index => $implement) {
            if ($implement instanceof Name) {
                $interfaceName = $this->getOriginalClassName($implement);
                if ($interfaceName !== null) {
                    $node->implements[$index] = new FullyQualified($interfaceName);
                    $modified = true;
                }
            }
        }

        return $modified ? $node : null;
    }

    private function handleCatch(Catch_ $node): ?Catch_
    {
        $modified = false;

        foreach ($node->types as $index => $type) {
            if ($type instanceof Name) {
                $typeName = $this->getOriginalClassName($type);
                if ($typeName !== null) {
                    $node->types[$index] = new FullyQualified($typeName);
                    $modified = true;
                }
            }
        }

        return $modified ? $node : null;
    }

    private function shouldPrefixClass(string $className): bool
    {
        // Don't prefix built-in types
        if (in_array(strtolower($className), self::BUILT_IN_TYPES, true)) {
            return false;
        }

        // Don't prefix if it already contains a namespace separator
        if (str_contains($className, '\\')) {
            return false;
        }

        // Don't prefix classes in the same namespace (like WPTestCase)
        // These should be resolved within the namespace
        $sameNamespaceClasses = [
            'WPTestCase',
            'WPAjaxTestCase',
            'WPCanonicalTestCase',
            'WPRestApiTestCase',
            'WPRestControllerTestCase',
            'WPRestPostTypeControllerTestCase',
            'WPXMLTestCase',
            'WPXMLRPCTestCase',
        ];
        if (in_array($className, $sameNamespaceClasses, true)) {
            return false;
        }

        // Prefix WordPress classes (WP_*, wp_*)
        if (preg_match('/^[Ww][Pp]_/', $className)) {
            return true;
        }

        // Prefix known custom exception classes
        $customExceptions = [
            'WPDieException',
            'WPAjaxDieStopException',
            'WPAjaxDieContinueException',
            'WP_Tests_Exception',
        ];
        if (in_array($className, $customExceptions, true)) {
            return true;
        }

        // Prefix known WordPress/test utility classes
        $testUtilityClasses = [
            'Spy_REST_Server',
            'IXR_Error',
            'wp_xmlrpc_server',
        ];
        if (in_array($className, $testUtilityClasses, true)) {
            return true;
        }

        // Prefix common PHP built-in classes that need explicit global reference
        $phpBuiltInClasses = [
            'DOMDocument',
            'DOMXPath',
            'XSLTProcessor',
            'SimpleXMLElement',
            'DateTime',
            'DateTimeImmutable',
            'Exception',
            'Error',
            'RuntimeException',
            'InvalidArgumentException',
            'ReflectionClass',
            'ReflectionMethod',
            'RecursiveDirectoryIterator',
            'RecursiveIteratorIterator',
            'stdClass',
        ];
        if (in_array($className, $phpBuiltInClasses, true)) {
            return true;
        }

        // Prefix PHPUnit legacy underscore-style classes
        $phpunitLegacyClasses = [
            'PHPUnit_Framework_TestCase',
            'PHPUnit_Framework_Exception',
            'PHPUnit_Framework_ExpectationFailedException',
            'PHPUnit_Framework_Error_Deprecated',
            'PHPUnit_Framework_Error_Notice',
            'PHPUnit_Framework_Error_Warning',
            'PHPUnit_Framework_Test',
            'PHPUnit_Framework_Warning',
            'PHPUnit_Framework_AssertionFailedError',
            'PHPUnit_Framework_TestSuite',
            'PHPUnit_Framework_TestListener',
            'PHPUnit_Util_GlobalState',
            'PHPUnit_Util_Getopt',
            'PHPUnit_Util_Test',
            'PHPUnit_Adapter_TestCase',
            'PHPUnit_Runner_Version',
        ];
        if (in_array($className, $phpunitLegacyClasses, true)) {
            return true;
        }

        return false;
    }
}
