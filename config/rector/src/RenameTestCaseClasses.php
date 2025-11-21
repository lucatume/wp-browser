<?php

namespace lucatume\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Rector rule to rename WordPress test case classes, add namespace, and update parent class.
 *
 * This rule:
 * 1. Renames the class to the new name
 * 2. Updates the parent class reference
 *
 * Note: Namespace addition is handled separately via AddNamespaceToFile rule.
 */
class RenameTestCaseClasses extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * Configuration mapping:
     * [
     *     'WP_Ajax_UnitTestCase' => [
     *         'newName' => 'WPAjaxTestCase',
     *         'newParent' => 'WPTestCase',
     *     ],
     * ]
     */
    private array $classRenames = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename WordPress test case classes and update parent class',
            [
                new ConfiguredCodeSample(
                    'abstract class WP_Ajax_UnitTestCase extends WP_UnitTestCase {}',
                    'abstract class WPAjaxTestCase extends WPTestCase {}',
                    [
                        'WP_Ajax_UnitTestCase' => [
                            'newName' => 'WPAjaxTestCase',
                            'newParent' => 'WPTestCase',
                        ],
                    ]
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->name === null) {
            return null;
        }

        $className = $node->name->toString();

        if (!isset($this->classRenames[$className])) {
            return null;
        }

        $config = $this->classRenames[$className];

        // Rename the class
        $node->name = new Identifier($config['newName']);

        // Update the parent class if specified
        if (isset($config['newParent']) && $node->extends !== null) {
            $node->extends = new Name($config['newParent']);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->classRenames = $configuration;
    }
}
