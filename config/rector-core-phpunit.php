<?php

declare(strict_types=1);

use lucatume\Rector\HandleInstallPhpCompat;
use lucatume\Rector\HandleMockMailerPhpCompat;
use lucatume\Rector\ModifyAbstractTestCase;
use lucatume\Rector\PrefixGlobalClassNames;
use lucatume\Rector\RenameTestCaseClasses;
use lucatume\Rector\WrapClassAliasWithExistenceCheck;
use Rector\Config\RectorConfig;

/**
 * Rector configuration for transforming WordPress core PHPUnit test includes.
 *
 * This configuration applies the necessary transformations to make WordPress
 * core test utilities compatible with wp-browser.
 *
 * Note: Namespace additions and use statements are handled by sed in the shell script
 * because Rector doesn't handle file-level statement additions well.
 */
return static function (RectorConfig $rectorConfig): void {
    $corePHPUnitDir = dirname(__DIR__) . '/includes/core-phpunit/includes';

    $rectorConfig->paths([
        $corePHPUnitDir
    ]);

    // Skip certain files that don't need transformation
    $rectorConfig->skip([
        // Factory files don't need changes
        $corePHPUnitDir . '/factory',
        // Bootstrap and other utility files
        $corePHPUnitDir . '/class-basic-object.php',
        $corePHPUnitDir . '/class-basic-subclass.php',
        // Skip WrapClassAliasWithExistenceCheck for mock-mailer.php as HandleMockMailerPhpCompat handles it
        WrapClassAliasWithExistenceCheck::class => [
            $corePHPUnitDir . '/mock-mailer.php',
        ],
    ]);

    // 1. Rename test case classes and update parent class
    $rectorConfig->ruleWithConfiguration(RenameTestCaseClasses::class, [
        'WP_Ajax_UnitTestCase' => [
            'newName' => 'WPAjaxTestCase',
            'newParent' => 'WPTestCase',
        ],
        'WP_Canonical_UnitTestCase' => [
            'newName' => 'WPCanonicalTestCase',
            'newParent' => 'WPTestCase',
        ],
        'WP_Test_REST_TestCase' => [
            'newName' => 'WPRestApiTestCase',
            'newParent' => 'WPTestCase',
        ],
        'WP_Test_REST_Controller_Testcase' => [
            'newName' => 'WPRestControllerTestCase',
            'newParent' => 'WPTestCase',
        ],
        'WP_Test_REST_Post_Type_Controller_Testcase' => [
            'newName' => 'WPRestPostTypeControllerTestCase',
            'newParent' => 'WPRestControllerTestCase',
        ],
        'WP_Test_XML_TestCase' => [
            'newName' => 'WPXMLTestCase',
            'newParent' => 'WPTestCase',
        ],
        'WP_XMLRPC_UnitTestCase' => [
            'newName' => 'WPXMLRPCTestCase',
            'newParent' => 'WPTestCase',
        ],
    ]);

    // 2. Modify abstract test case class
    $rectorConfig->rule(ModifyAbstractTestCase::class);

    // 3. Wrap class_alias calls with existence checks (for phpunit6/compat.php)
    $rectorConfig->rule(WrapClassAliasWithExistenceCheck::class);

    // 4. Handle WordPress 6.1 file rename in install.php
    $rectorConfig->rule(HandleInstallPhpCompat::class);

    // 5. Handle WordPress 6.8 file rename in mock-mailer.php
    $rectorConfig->rule(HandleMockMailerPhpCompat::class);

    // 6. Prefix global class names in namespaced files
    // Note: This rule is applied in a second Rector pass after namespaces are added
    $rectorConfig->rule(PrefixGlobalClassNames::class);
};
