<?php


namespace lucatume\WPBrowser\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Codeception\Test\TestCaseWrapper;
use FilesystemIterator;
use Iterator;
use lucatume\WPBrowser\Opis\Closure\SerializableClosure;
use lucatume\WPBrowser\Process\SerializableThrowable;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\Utils\Property;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use RegexIterator;

class IsolationSupport extends Extension
{
    public const MAIN_PROCESS_PATCH_CONTEXT = 'Isolation-Support';
    public const ISOLATED_PROCESS_PATCH_CONTEXT = 'Isolation-Support-Process';

    /**
     * @var array<string,array{0: string, 1: int}>
     */
    public static $events = [
        Events::SUITE_INIT => ['onSuiteInit', 0],
        Events::TEST_START => ['onTestStart', 0],
        Events::TEST_FAIL => ['onTestFail', 0],
    ];

    /**
     * @var string
     */
    private $processCode = <<< PHP
\$dataName = \$this->getName();
\$args = func_get_args();
foreach(\$args as &\$arg){
    if(\$arg instanceof \Closure){
        \$arg = new \lucatume\WPBrowser\Opis\Closure\SerializableClosure(\$arg);
    }
}
\$encodedDataSet = base64_encode(serialize(\$args)); 
\$modules = \$this->getMetadata()->getCurrent('modules');
\$wploderModuleNameInSuite = isset(\$modules['WPLoader']) ? 'WPLoader' : \lucatume\WPBrowser\Module\WPLoader::class;
\$command = [
    \lucatume\WPBrowser\Utils\Composer::binDir('codecept'),
    \lucatume\WPBrowser\Command\RunOriginal::getCommandName(),
    sprintf('%s:%s', codecept_relative_path('{{file}}'), '{{name}}'),
    '--override',
    "modules: config: {\$wploderModuleNameInSuite}: skipInstall: true",
    '--ext',
    'IsolationSupport'
];
\$process = new \lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process(
    \$command, 
    null, 
    [
        'WPBROWSER_ISOLATED_RUN' => '1',
        'WPBROWSER_DATA_NAME' => \$dataName,
        'WPBROWSER_DATA_SET' => \$encodedDataSet,
        'WPBROWSER_TEST_FILE' => '{{file}}' 
    ] 
);
\$exitCode = \$process->run();

if (\$exitCode !== 0) {
    \$output = \$process->getOutput();
    preg_match(
        '/WPBROWSER_ISOLATION_RESULT_START(.*)WPBROWSER_ISOLATION_RESULT_END/us',
        \$output,
        \$matches
    );
    \$failure = \$matches[1] ?? null;

    if (\$failure === null) {
        \$this->fail("Test failed: {\$process->getErrorOutput()}");
    }
    \$serializableThrowable = unserialize(base64_decode(\$failure), ['allowed_classes' => true]);
    throw(\$serializableThrowable->getThrowable());
}
return;
PHP;

    /**
     * @throws ExtensionException
     */
    public function onSuiteInit(SuiteEvent $event): void
    {
        if ($this->isMainProcess()) {
            $this->monkeyPatchTestCasesToRunInSeparateProcess($event);
            return;
        }

        $this->monkeyPatchTestMethodsToReplaceAnnotations();
    }

    private function isMainProcess(): bool
    {
        return empty($_SERVER['WPBROWSER_ISOLATED_RUN']);
    }

    /**
     * @throws ExtensionException
     */
    protected function monkeyPatchTestCasesToRunInSeparateProcess(SuiteEvent $event): void
    {
        /** @var array{path: string} $settings */
        $settings = $event->getSettings();

        /** @var Iterator<string> $testFiles */
        $testFiles = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($settings['path'], FilesystemIterator::CURRENT_AS_PATHNAME)
            ),
            '/Test\.php$/'
        );

        foreach ($testFiles as $testFile) {
            $patchedFile = $this->getPatchedFile($testFile);

            if ($patchedFile === false) {
                continue;
            }

            MonkeyPatch::redirectFileToFile(
                $testFile,
                $patchedFile,
                false,
                self::MAIN_PROCESS_PATCH_CONTEXT
            );
        }
    }

    /**
     * @throws ExtensionException
     * @return string|false
     */
    private function getPatchedFile(string $testFile)
    {
        $patchedFile = MonkeyPatch::getReplacementFileName(
            $testFile,
            self::MAIN_PROCESS_PATCH_CONTEXT
        );

        if (is_file($patchedFile)) {
            return $patchedFile;
        }

        $fileContents = file_get_contents($testFile);

        if ($fileContents === false) {
            throw new ExtensionException($this, "Failed to to open {$testFile} for reading.");
        }

        if (strpos($fileContents, '@runTestsInSeparateProcesses') !== false
            || strpos($fileContents, '#[RunTestsInSeparateProcesses') !== false
        ) {
            return $this->patchFileContentsToRunTestsInSeparateProcesses($testFile, $fileContents);
        }

        if (strpos($fileContents, '@runInSeparateProcess') !== false
            || strpos($fileContents, '#[RunInSeparateProcess') !== false
        ) {
            return $this->patchFileContentsToRunTestInSeparateProcess($testFile, $fileContents);
        }

        return false;
    }

    /**
     * @throws ExtensionException
     * @return string|false
     */
    public function patchFileContentsToRunTestsInSeparateProcesses(
        string $testFile,
        string $fileContents
    ) {
        return $this->patchFileContentsToInjectSeparateProcessExecution(
            '/\\s*?public\\s+function\\s+(?<fname>[^(]+)[^{]*?{/um',
            $testFile,
            $fileContents
        );
    }

    /**
     * @throws ExtensionException
     * @return string|false
     */
    private function patchFileContentsToInjectSeparateProcessExecution(
        string $pattern,
        string $testFile,
        string $fileContents
    ) {
        // Starts with `test` OR contains the `@test` annotation OR contains the `#[Test]` attribute.
        $patchedFileContents = preg_replace_callback(
            $pattern,
            function ($matches) use ($testFile, $fileContents) {
                return $this->injectProcessCode($matches, $testFile, $fileContents);
            },
            $fileContents
        );

        if ($patchedFileContents === $fileContents) {
            return false;
        }

        if ($patchedFileContents === null) {
            throw new ExtensionException($this, "File contents patching failed for file {$testFile}.");
        }

        $patchedFileContents = preg_replace(
            [
                '/(^\\s*\\*\\s*)@runInSeparateProcess/um',
                '/(^\\s+#\\[)RunInSeparateProcess([^]]*?])/um',
                '/(^\\s*\\*\\s*)@runTestsInSeparateProcesses/um',
                '/(^\\s+#\\[)RunTestsInSeparateProcesses([^]]*?])/um'
            ],
            [
                '$1@willRunInSeparateProcess',
                '$1WillRunInSeparateProcess$2',
                '$1@willRunTestsInSeparateProcesses',
                '$1willRunTestsInSeparateProcesses$2'
            ],
            $patchedFileContents
        );

        $patchedFile = MonkeyPatch::getReplacementFileName(
            $testFile,
            self::MAIN_PROCESS_PATCH_CONTEXT
        );

        if (!file_put_contents($patchedFile, $patchedFileContents, LOCK_EX)) {
            throw new ExtensionException($this, "Failed writing patch file {$patchedFile} for {$testFile}.");
        }

        return $patchedFile;
    }

    /**
     * @param array<int|string,string> $matches
     */
    private function injectProcessCode(array $matches, string $testFile, string $fileContents): string
    {
        if (!$this->isTestMethod($matches['fname'], $fileContents)) {
            return $matches[0];
        }
        $compiledProcessCode = str_replace(
            ['{{file}}', '{{name}}'],
            [$testFile, $matches['fname']],
            $this->processCode
        );
        $processCode = preg_replace(["/[\r\n]*/", '~\\s{2,}~'], '', $compiledProcessCode);
        return sprintf("%s %s", $matches[0], $processCode);
    }

    private function isTestMethod(string $name, string $fileContents): bool
    {
        if (strncmp($name, 'test', strlen('test')) === 0) {
            return true;
        }

        $methodDocBlockLines = [];
        $methodPos = strpos($fileContents, $name);

        if ($methodPos === false) {
            return false;
        }

        $input = substr($fileContents, 0, $methodPos);
        // Drop the first line as it will be the `public function ...` one.
        $lines = explode("\n", $input, -1);
        $pattern = '/^(\\s*$|\\s*\\/\\*\\*|\\s*\\*|\\s*\\*\\s*\\/|\\s*#\\[[^]]+])/um';
        for ($i = count($lines) - 1; $i !== 0; $i--) {
            $line = $lines [$i];
            if (!preg_match($pattern, $line)) {
                break;
            }
            array_unshift($methodDocBlockLines, $line);
        }
        $methodDocBlock = implode("\n", $methodDocBlockLines);
        return strpos($methodDocBlock, '#[Test]') !== false
            || strpos($methodDocBlock, '@test') !== false;
    }

    /**
     * @throws ExtensionException
     * @return string|false
     */
    protected function patchFileContentsToRunTestInSeparateProcess(
        string $testFile,
        string $fileContents
    ) {
        $pattern = '/'
            . '^\\s*' # Start of line and arbitrary number of spaces.
            . '(' # Start OR group.
            . '\\*\\s*@runInSeparateProcess' # @runInSeparateProcess annotation.
            . '|' # OR ...
            . '#\\[RunInSeparateProcess]' # [RunInSeparateProcess] attribute.
            . ')' # Close OR group.
            . '.*?public\\s+function\\s+(?<fname>[^(\\s]*?)\\([^{]*?{' # The function declaration until the `{`.
            . '/usm'; # Multi-line pattern.
        return $this->patchFileContentsToInjectSeparateProcessExecution(
            $pattern,
            $testFile,
            $fileContents
        );
    }

    /**
     * @throws ExtensionException
     */
    protected function monkeyPatchTestMethodsToReplaceAnnotations(): void
    {
        $testFile = $_SERVER['WPBROWSER_TEST_FILE'];
        $fileContents = file_get_contents($testFile);

        if ($fileContents === false) {
            throw new ExtensionException($this, "Failed to to open {$testFile} for reading.");
        }

        $patchedTestFile = preg_replace(
            [
                '/(^\\s*\\*\\s*)@dataProvider/um',
                '/(^\\s+#\\[)DataProvider([^]]*?])/um',
                '/(^\\s*\\*\\s*)@runInSeparateProcess/um',
                '/(^\\s+#\\[)RunInSeparateProcess([^]]*?])/um',
                '/(^\\s*\\*\\s*)@runTestsInSeparateProcesses/um',
                '/(^\\s+#\\[)RunTestsInSeparateProcesses([^]]*?])/um'
            ],
            [
                '$1@dataProvidedBy',
                '$1DataProvidedBy$2',
                '$1@runningInSeparateProcess',
                '$1RunningInSeparateProcess$2',
                '$1@runningTestsInSeparateProcesses',
                '$1RunningTestsInSeparateProcesses$2'
            ],
            $fileContents
        );

        if ($patchedTestFile === null) {
            throw new ExtensionException($this, "File contents patching failed for file {$testFile}.");
        }

        MonkeyPatch::redirectFileContents($testFile, $patchedTestFile, false, self::ISOLATED_PROCESS_PATCH_CONTEXT);
    }

    public function onTestFail(FailEvent $failEvent): void
    {
        if ($this->isMainProcess()) {
            return;
        }

        $this->printSerializedFailure($failEvent);
    }

    private function printSerializedFailure(FailEvent $failEvent): void
    {
        printf("\r\nWPBROWSER_ISOLATION_RESULT_START\n");
        $fail = $failEvent->getFail();
        $serializableThrowable = new SerializableThrowable($fail);
        printf(base64_encode(serialize($serializableThrowable)));
        printf("WPBROWSER_ISOLATION_RESULT_END\r\n");
    }

    /**
     * @throws ReflectionException
     */
    public function onTestStart(TestEvent $e): void
    {
        if ($this->isMainProcess()) {
            return;
        }

        $this->injectProvidedDataSet($e);
    }

    /**
     * @throws ReflectionException
     */
    private function injectProvidedDataSet(TestEvent $e): void
    {
        $data = unserialize(base64_decode($_SERVER['WPBROWSER_DATA_SET']), ['allowed_classes' => true]);

        if (!is_array($data)) {
            throw new \RuntimeException('Test method data must be an array, but it is ' . gettype($data));
        }

        foreach ($data as &$dataElement) {
            if ($dataElement instanceof SerializableClosure) {
                $dataElement = $dataElement->getClosure();
            }
        }
        unset($dataElement);

        $dataName = $_SERVER['WPBROWSER_DATA_NAME'];

        /** @var TestCaseWrapper|TestCase $testCaseWrapper */
        $testCaseWrapper = $e->getTest();
        $testCase = $testCaseWrapper instanceof TestCase ? $testCaseWrapper : $testCaseWrapper->getTestCase();
        Property::setPrivateProperties($testCase, [
            'data' => $data,
            'dataName' => $dataName
        ]);
    }
}
