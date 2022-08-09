<?php

declare( strict_types=1 );

use Rector\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector;
use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfIssetToNullCoalescingRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\Config\RectorConfig;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\PostRector\Rector\UseAddingPostRector;
use Rector\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function ( RectorConfig $rectorConfig ): void {
	$rectorConfig->paths( [
		__DIR__ . '/src'
	] );

	$rectorConfig->import( SetList::PHP_80 );
	$rectorConfig->rule(AddClosureReturnTypeRector::class);
	$rectorConfig->rule(AddReturnTypeDeclarationBasedOnParentClassMethodRector::class);
	$rectorConfig->rule(ReturnTypeFromStrictScalarReturnExprRector::class);
    $rectorConfig->import( SetList::PSR_4 );
    $rectorConfig->import( SetList::TYPE_DECLARATION_STRICT );
    $rectorConfig->rule(AddClosureReturnTypeRector::class);
    $rectorConfig->rule(AddMethodCallBasedStrictParamTypeRector::class);
    $rectorConfig->rule(AddParamTypeDeclarationRector::class);
    $rectorConfig->rule(AddVoidReturnTypeWhereNoReturnRector::class);
    $rectorConfig->rule(ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $rectorConfig->rule(ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
    $rectorConfig->rule(FuncGetArgsToVariadicParamRector::class);
    $rectorConfig->rule(MakeTypedPropertyNullableIfCheckedRector::class);
    $rectorConfig->rule(NameImportingPostRector::class);
    $rectorConfig->rule(NarrowUnionTypeDocRector::class);
    $rectorConfig->rule(NewStaticToNewSelfRector::class);
    $rectorConfig->rule(RestoreDefaultNullToNullableTypePropertyRector::class);
    $rectorConfig->rule(ReturnTypeFromReturnNewRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictBoolReturnExprRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictNativeCallRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictNewArrayRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictScalarReturnExprRector::class);
    $rectorConfig->rule(ReturnTypeFromStrictTypedPropertyRector::class);
    $rectorConfig->rule(SimplifyIfIssetToNullCoalescingRector::class);
    $rectorConfig->rule(SimplifyIfIssetToNullCoalescingRector::class);
    $rectorConfig->rule(SimplifyIfNotNullReturnRector::class);
    $rectorConfig->rule(TernaryToNullCoalescingRector::class);
    $rectorConfig->rule(TypedPropertyRector::class);
    $rectorConfig->rule(UnionTypesRector::class);
    $rectorConfig->rule(UseAddingPostRector::class);
};
