<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([dirname(__DIR__) . '/src']);
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->rule(ArrayShapeFromConstantArrayReturnRector::class);
    $rectorConfig->sets([SetList::PHP_80]);
};
