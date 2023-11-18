<?php

declare(strict_types=1);

use Codeception\TestInterface;
use lucatume\Rector\RemoveTypeHinting;
use lucatume\Rector\SwapEventDispatcherEventNameParameters;
use Rector\Config\RectorConfig;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        dirname(__DIR__) . '/includes',
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/tests',
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

    $rectorConfig->rule(SwapEventDispatcherEventNameParameters::class);

    $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_71]);
    $rectorConfig->skip([DowngradeParameterTypeWideningRector::class]);

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
        ]
    ]);
};
