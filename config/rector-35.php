<?php

declare(strict_types=1);

use lucatume\Rector\SwapEventDispatcherEventNameParameters;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Rector\Config\RectorConfig;
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
//        dirname(__DIR__) . '/includes',
//        dirname(__DIR__) . '/src',
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
//
//    $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_71]);
};
