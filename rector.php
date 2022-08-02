<?php

declare( strict_types=1 );

use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function ( RectorConfig $rectorConfig ): void {
	$rectorConfig->paths( [
		__DIR__ . '/src'
	] );

	$rectorConfig->import( SetList::PHP_80 );
	$rectorConfig->rule(ReturnTypeFromStrictScalarReturnExprRector::class);
	$rectorConfig->rule(AddClosureReturnTypeRector::class);
	$rectorConfig->rule(AddReturnTypeDeclarationBasedOnParentClassMethodRector::class);
};
