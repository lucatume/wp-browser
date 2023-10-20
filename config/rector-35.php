<?php

declare( strict_types=1 );

use lucatume\Rector\SwapEventDispatcherEventNameParameters;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        dirname(__DIR__) . '/includes',
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/tests',
    ]);

    $rectorConfig->ruleWithConfiguration(RenameClassRector::class,[
        'Symfony\Contracts\EventDispatcher\Event' => 'Symfony\Component\EventDispatcher\Event'
    ]);

    $rectorConfig->rule(SwapEventDispatcherEventNameParameters::class);

     $rectorConfig->sets([ DowngradeLevelSetList::DOWN_TO_PHP_71 ]);
};
