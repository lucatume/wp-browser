<?php

declare(strict_types=1);

use lucatume\Rector\DowngradeGetClosureCalledClassRector;
use lucatume\Rector\DowngradePhpOsFamily;
use lucatume\Rector\RemoveSuperglobalsFromClosureUse;
use lucatume\Rector\RemoveTypeHinting;
use lucatume\Rector\SerializableThrowableCompatibilityRector;
use Rector\Config\RectorConfig;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector;
use Rector\DowngradePhp81\Rector\StmtsAwareInterface\DowngradeSetAccessibleReflectionPropertyRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

// Load the custom rules explicitly so the harness does not depend on the target
// project's composer autoload mapping the lucatume\Rector namespace. This lets the
// transpile run against any source tree (e.g. a fresh master worktree) that only has
// Rector installed. require_once is a no-op when composer already autoloaded them.
foreach (glob(__DIR__ . '/rector/src/*.php') as $customRule) {
    require_once $customRule;
}

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        dirname(__DIR__) . '/includes',
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/tests'
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Symfony\Contracts\EventDispatcher\Event' => 'Symfony\Component\EventDispatcher\Event',
        'Psr\EventDispatcher\EventDispatcherInterface' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface'
    ]);
    $rectorConfig->ruleWithConfiguration(RenamePropertyRector::class, [
        new RenameProperty(
            'lucatume\WPBrowser\TestCase\WPTestCase',
            'backupStaticAttributesExcludeList',
            'backupStaticAttributesBlacklist'
        ),
        new RenameProperty(
            'lucatume\WPBrowser\TestCase\WPTestCase',
            'backupGlobalsExcludeList',
            'backupGlobalsBlacklist'
        )
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('PHPUnit\Framework\Assert', 'assertMatchesRegularExpression', 'assertRegExp'),
        new MethodCallRename('PHPUnit\Framework\Assert', 'assertDoesNotMatchRegularExpression', 'assertNotRegExp'),
        new MethodCallRename('PHPUnit\Framework\Assert', 'assertFileDoesNotExist', 'assertFileNotExists')
    ]);

    $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_71]);
    // DowngradeHashAlgorithmXxHashRector references \MHASH_XXH32 (PHP 8.1+) at instantiation,
    // fataling on the PHP 8.0 transpile runtime. The source uses no xxh* hashing, so skip it.
    //
    // DowngradeSetAccessibleReflectionPropertyRector injects an unguarded $prop->setAccessible(true)
    // after every `new ReflectionProperty`. The master source already guards each call with
    // `PHP_VERSION_ID < 80100 && $prop->setAccessible(true)`, so the injected copies are redundant
    // and, in one spot, land before the variable is assigned (fatal). Skip it; master's guards stand.
    $rectorConfig->skip([
        DowngradeParameterTypeWideningRector::class,
        DowngradeHashAlgorithmXxHashRector::class,
        DowngradeSetAccessibleReflectionPropertyRector::class,
    ]);

    // Downgrade PHP_OS_FAMILY (PHP 7.2+) to PHP_OS for PHP 7.1 compatibility
    $rectorConfig->rule(DowngradePhpOsFamily::class);

    // Make SerializableThrowable compatible with PHP <7.3 and downgrade str_contains()
    $rectorConfig->rule(SerializableThrowableCompatibilityRector::class);

    // Strip superglobals the arrow-function downgrade illegally captures into use()
    $rectorConfig->rule(RemoveSuperglobalsFromClosureUse::class);

    // Downgrade ReflectionFunction::getClosureCalledClass() (PHP 8.1+) for PHP < 8.1
    $rectorConfig->rule(DowngradeGetClosureCalledClassRector::class);

    $rectorConfig->ruleWithConfiguration(RemoveTypeHinting::class, [
        'lucatume\WPBrowser\Module\WPDb' => [
            '_cleanup' => [
                // from: public function _cleanup(string $databaseKey = null, array $databaseConfig = null): void
                // to: public function _cleanup($databaseKey = null, $databaseConfig = null)
                RemoveTypeHinting::REMOVE_ALL => true
            ],
            '_loadDump' => [
                // from: public function _loadDump(string $databaseKey = null, array $databaseConfig = null): void
                // public function _loadDump($databaseKey = null, $databaseConfig = null)
                RemoveTypeHinting::REMOVE_ALL => true
            ],
            'loadDumpUsingDriver' => [
                // from: protected function loadDumpUsingDriver(string $databaseKey): void
                // to: protected function loadDumpUsingDriver($databaseKey)
                RemoveTypeHinting::REMOVE_ALL => true
            ]
        ],
        'lucatume\WPBrowser\Module\WPFilesystem' => [
            // from: public function _failed(TestInterface $test, Exception $fail): void
            // to: public function _failed(TestInterface $test, Exception $fail)
            '_failed' => [
                RemoveTypeHinting::REMOVE_RETURN_TYPE_HINTING => true,
                RemoveTypeHinting::REMOVE_PARAM_TYPE_HINTING => ['fail']
            ],
            // from: public function assertDirectoryExists(string $directory, string $message = ''): void
            // to: protected function assertDirectoryExists($directory, $message = '')
            'assertDirectoryExists' => [
                RemoveTypeHinting::REMOVE_ALL => true
            ]
        ],
        'lucatume\WPBrowser\Module\WPLoader' => [
            // from: public function _beforeSuite(array $settings = [])
            // to: public function _beforeSuite($settings = [])
            '_beforeSuite' => [
                RemoveTypeHinting::REMOVE_RETURN_TYPE_HINTING => true,
                RemoveTypeHinting::REMOVE_PARAM_TYPE_HINTING => ['settings']
            ],
        ]
    ]);
};
