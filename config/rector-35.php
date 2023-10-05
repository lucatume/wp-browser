<?php

declare( strict_types=1 );

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        dirname(__DIR__) . '/includes',
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/tests',
    ]);

    $rectorConfig->sets([ DowngradeLevelSetList::DOWN_TO_PHP_71 ]);
};
