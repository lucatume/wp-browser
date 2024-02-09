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
use PhpParser\PhpVersion;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Build a PHP parser using nikic/php-parser
$parser = (new ParserFactory())->createForVersion(PHPVersion::fromComponents(8, 0));

// Build an iterator over all the .php files in the includes directory
$files = new RegexIterator(
    new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            dirname(__DIR__) . '/includes/core-phpunit/includes',
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
        )
    ),
    '/\.php$/'
);

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
        $class = substr($match, strrpos($match, '\\') + 1);
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
            if ($class instanceof Name) {
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
                if ($type instanceof Name) {
                    $name = $type->toString();
                    if (!class_exists($name) && !interface_exists($name) && !trait_exists($name)) {
                        $file = $this->file;
                        $line = $node->getAttribute('startLine');
                        $match = $name;
                        $this->addMatch($file, $line, $match);
                    }
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

$filesToCure = [
    dirname(__DIR__) . "/includes/core-phpunit/includes/testcase-ajax.php",
    dirname(__DIR__) . "/includes/core-phpunit/includes/testcase-canonical.php",
    dirname(__DIR__) . "/includes/core-phpunit/includes/testcase-rest-api.php",
    dirname(__DIR__) . "/includes/core-phpunit/includes/testcase-rest-controller.php",
    dirname(__DIR__) . "/includes/core-phpunit/includes/testcase-rest-post-type-controller.php",
    dirname(__DIR__) . "/includes/core-phpunit/includes/testcase-xml.php",
    dirname(__DIR__) . '/includes/core-phpunit/includes/testcase-xmlrpc.php',
];
foreach ($namespaceFlagVisitor->getMatchesByFile() as $file => $matches) {
    if (!in_array($file, $filesToCure, true)) {
        // Target only the files to cure.
        continue;
    }

    $updatedContents = file($file);

    if ($updatedContents === false) {
        throw new RuntimeException('Could not read file: ' . $file);
    }

    foreach ($matches as [$line, $match]) {
        $updatedContents[$line - 1] = str_replace($match, '\\' . $match, $updatedContents[$line - 1]);
    }

    printf('Updating file: %s ... ', $file);
    if (!file_put_contents($file, implode('', $updatedContents), LOCK_EX)) {
        throw new RuntimeException('Could not update file: ' . $file);
    }
    echo "ok\n";
}
