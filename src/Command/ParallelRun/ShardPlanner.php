<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

/**
 * @internal
 */
final class ShardPlanner
{
    public const MODE_SHARD   = 'shard';
    public const MODE_TIMINGS = 'timings';
    public const MODE_TAGS    = 'tags';

    public const WEIGHT_FAST   = 1.0;
    public const WEIGHT_NORMAL = 5.0;
    public const WEIGHT_SLOW   = 15.0;

    /**
     * Parse a JUnit report.xml and return per-file cumulative time in seconds.
     *
     * @return array<string,float>
     */
    public function fromReport(string $reportXmlPath): array
    {
        if (!is_file($reportXmlPath)) {
            return [];
        }
        $content = file_get_contents($reportXmlPath);
        if ($content === false || $content === '') {
            return [];
        }
        $prev = libxml_use_internal_errors(true);
        $doc  = simplexml_load_string($content);
        libxml_use_internal_errors($prev);
        if (!$doc instanceof \SimpleXMLElement) {
            return [];
        }

        $weights = [];
        foreach ($doc->xpath('//testcase') ?: [] as $case) {
            $attrs = $case->attributes();
            if ($attrs === null) {
                continue;
            }
            $file = (string)($attrs['file'] ?? '');
            if ($file === '') {
                continue;
            }
            $time = (float)($attrs['time'] ?? 0);
            $weights[$file] = ($weights[$file] ?? 0.0) + $time;
        }
        return $weights;
    }

    /**
     * Scan PHP test files under $suiteRoot and compute a per-file weight based on @group tags.
     *
     * A class-level `@group fast|slow` applies to every test method.
     * Per-method tags override (or add, when no class tag) — untagged methods count as "normal".
     *
     * @return array<string,float>
     */
    public function fromTags(string $suiteRoot): array
    {
        if (!is_dir($suiteRoot)) {
            return [];
        }

        $weights = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($suiteRoot, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $name = $file->getFilename();
            if (substr_compare($name, 'Test.php', -strlen('Test.php')) !== 0 && substr_compare($name, 'Cest.php', -strlen('Cest.php')) !== 0) {
                continue;
            }
            $path = (string)$file->getRealPath();
            if ($path === '') {
                continue;
            }
            $weights[$path] = $this->weighFile($path);
        }
        return $weights;
    }

    /**
     * Pack files with given weights into $workers shards using Longest Processing Time (LPT).
     *
     * @param array<string,float> $fileWeights
     * @return array<int, array{files: string[], weight: float}> 1-indexed shards
     */
    public function plan(array $fileWeights, int $workers): array
    {
        $workers = max(1, $workers);
        $shards = [];
        for ($i = 1; $i <= $workers; $i++) {
            $shards[$i] = ['files' => [], 'weight' => 0.0];
        }
        if ($fileWeights === []) {
            return $shards;
        }

        arsort($fileWeights);
        foreach ($fileWeights as $file => $weight) {
            $target = 1;
            $min = $shards[1]['weight'];
            for ($i = 2; $i <= $workers; $i++) {
                if ($shards[$i]['weight'] < $min) {
                    $min = $shards[$i]['weight'];
                    $target = $i;
                }
            }
            $shards[$target]['files'][]   = (string)$file;
            $shards[$target]['weight']   += (float)$weight;
        }
        return $shards;
    }

    /**
     * Return which external resources are referenced by the given set of
     * test files via their @group annotations.
     *
     * @param string[] $files
     * @return array{server: bool, chromedriver: bool, mysql: bool}
     */
    public function needsResources(array $files): array
    {
        $need = ['server' => false, 'chromedriver' => false, 'mysql' => false];
        foreach ($files as $file) {
            if ($file === '' || !is_file($file)) {
                continue;
            }
            $src = @file_get_contents($file);
            if ($src === false || $src === '') {
                continue;
            }
            $tokens = @token_get_all($src);
            if (!is_array($tokens)) {
                continue;
            }
            foreach ($tokens as $tok) {
                if (!is_array($tok) || $tok[0] !== T_DOC_COMMENT) {
                    continue;
                }
                $doc = $tok[1];
                if (!$need['server'] && preg_match('/@group\s+requires-server\b/', $doc)) {
                    $need['server'] = true;
                }
                if (!$need['chromedriver'] && preg_match('/@group\s+requires-chromedriver\b/', $doc)) {
                    $need['chromedriver'] = true;
                }
                if (!$need['mysql'] && preg_match('/@group\s+requires-mysql-server\b/', $doc)) {
                    $need['mysql'] = true;
                }
                if ($need['server'] && $need['chromedriver'] && $need['mysql']) {
                    break 2;
                }
            }
        }
        return $need;
    }

    private function weighFile(string $path): float
    {
        $src = @file_get_contents($path);
        if ($src === false || $src === '') {
            return self::WEIGHT_NORMAL;
        }

        $tokens = @token_get_all($src);
        if (!is_array($tokens)) {
            return self::WEIGHT_NORMAL;
        }

        $classTag = null;
        $pendingDoc = null;
        $methodCount = 0;
        $total = 0.0;

        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];
            if (!is_array($tok)) {
                $pendingDoc = null;
                continue;
            }
            $id = $tok[0];
            if ($id === T_DOC_COMMENT) {
                $pendingDoc = $tok[1];
                continue;
            }
            if ($id === T_WHITESPACE
                || $id === T_ABSTRACT
                || $id === T_FINAL
                || $id === T_PUBLIC
                || $id === T_PROTECTED
                || $id === T_PRIVATE
                || $id === T_STATIC
            ) {
                continue;
            }
            if ($id === T_CLASS) {
                if ($classTag === null && $pendingDoc !== null) {
                    $classTag = $this->tagFor($pendingDoc);
                }
                $pendingDoc = null;
                continue;
            }
            if ($id === T_FUNCTION) {
                $name = $this->nextFunctionName($tokens, $i);
                if ($name !== null && $this->looksLikeTest($name)) {
                    $methodCount++;
                    $tag = $pendingDoc !== null ? $this->tagFor($pendingDoc) : null;
                    $total += $this->weightFor($tag ?? $classTag);
                }
                $pendingDoc = null;
                continue;
            }
            $pendingDoc = null;
        }

        if ($methodCount === 0) {
            return $classTag !== null ? $this->weightFor($classTag) : self::WEIGHT_NORMAL;
        }
        return $total;
    }

    /**
     * @param array<int,array{0:int,1:string,2:int}|string> $tokens
     */
    private function nextFunctionName(array $tokens, int $fromIndex): ?string
    {
        $count = count($tokens);
        for ($j = $fromIndex + 1; $j < $count; $j++) {
            $t = $tokens[$j];
            if (!is_array($t)) {
                continue;
            }
            if ($t[0] === T_WHITESPACE) {
                continue;
            }
            if ($t[0] === T_STRING) {
                return $t[1];
            }
            return null;
        }
        return null;
    }

    private function looksLikeTest(string $name): bool
    {
        return strncmp($name, 'test', strlen('test')) === 0
            || strncmp($name, 'it_', strlen('it_')) === 0
            || strncmp($name, 'should_', strlen('should_')) === 0;
    }

    private function tagFor(string $docBlock): ?string
    {
        if (preg_match('/@group\s+(fast|slow|normal)/', $docBlock, $m)) {
            return $m[1];
        }
        return null;
    }

    private function weightFor(?string $tag): float
    {
        switch ($tag) {
            case 'fast':
                return self::WEIGHT_FAST;
            case 'slow':
                return self::WEIGHT_SLOW;
            default:
                return self::WEIGHT_NORMAL;
        }
    }
}
