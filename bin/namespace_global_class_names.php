#! /usr/bin/env php

<?php

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

// Build a PHP parser using nikic/php-parser
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);

// Build an iterator over all the .php files in the includes directory
$files = [
    $rootDir . "/includes/core-phpunit/includes/testcase-ajax.php",
    $rootDir . "/includes/core-phpunit/includes/testcase-canonical.php",
    $rootDir . "/includes/core-phpunit/includes/testcase-rest-api.php",
    $rootDir . "/includes/core-phpunit/includes/testcase-rest-controller.php",
    $rootDir . "/includes/core-phpunit/includes/testcase-rest-post-type-controller.php",
    $rootDir . "/includes/core-phpunit/includes/testcase-xml.php",
    $rootDir . '/includes/core-phpunit/includes/testcase-xmlrpc.php',
];

// Add a node visitor that will resolve to fully qualified namespaces.
$traverser = new NodeTraverser();
$traverser->addVisitor(new NameResolver());

// Add a visitor that will flag any use of a class name that does not exist.
$namespaceFlagVisitor = new class extends NodeVisitorAbstract {
    private array $matchesByFile = [];
    private string $file;

    private function addMatch(string $file, int $line, string $match)
    {
        // Pick the fragment after the last \ in the match.
        $class = substr($match, (strrpos($match, '\\') ?: -1) + 1);
        $this->matchesByFile[$file][] = [$line, $class];
    }

    public function getMatchesByFile(): array
    {
        return $this->matchesByFile;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof New_) {
            $class = $node->class;
            if ($class instanceof Name && count($class->getParts()) > 1) {
                $name = $class->toString();
                if (!class_exists($name) && !interface_exists($name) && !trait_exists($name)) {
                    $file = $this->file;
                    $line = $node->getAttribute('startLine');
                    $match = $class->toString();
                    $this->addMatch($file, $line, $match);
                }
            }
        } elseif ($node instanceof Function_ || $node instanceof ClassMethod) {
            foreach ($node->getParams() as $param) {
                $type = $param->type;
                if ($type instanceof Name && count($type->getParts()) > 1) {
                    $name = $type->toString();
                    if (!class_exists($name) && !interface_exists($name) && !trait_exists($name)) {
                        $file = $this->file;
                        $line = $node->getAttribute('startLine');
                        $match = $name;
                        $this->addMatch($file, $line, $match);
                    }
                }
            }
        } elseif($node instanceof Node\Expr\Instanceof_){
            $class = $node->class;
            if ($class instanceof Name && count($class->getParts()) > 1) {
                $name = $class->toString();
                if (!class_exists($name) && !interface_exists($name) && !trait_exists($name)) {
                    $file = $this->file;
                    $line = $node->getAttribute('startLine');
                    $match = $class->toString();
                    $this->addMatch($file, $line, $match);
                }
            }
        }
    }

    public function setPossibleNamespaces(array $possibleNamespaces): void
    {
        $this->possibleNamespaces = $possibleNamespaces;
    }
};

$traverser->addVisitor($namespaceFlagVisitor);

// Scan all the files to build the parser information.
foreach ($files as $file) {
    $stmts = $parser->parse(file_get_contents($file));
    $namespaceFlagVisitor->setFile($file);
    $stmts = $traverser->traverse($stmts);
}

$matchesByFile = $namespaceFlagVisitor->getMatchesByFile();

if (!count($matchesByFile)) {
    printf("No replacemenents required.\n");
}

foreach ($matchesByFile as $file => $matches) {
    $updatedContents = file($file);

    if ($updatedContents === false) {
        throw new RuntimeException('Could not read file: ' . $file);
    }

    foreach ($matches as [$line, $match]) {
        $updatedContents[$line - 1] = str_replace(ltrim($match, '\\'), '\\' . $match, $updatedContents[$line - 1]);
    }

    printf('Updating file: %s ... ', $file);
    if (!file_put_contents($file, implode('', $updatedContents), LOCK_EX)) {
        throw new RuntimeException('Could not update file: ' . $file);
    }
    echo "ok\n";
}
