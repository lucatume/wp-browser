<?php
/**
 * Polyfill PHP 8.0 tokenizer constants on PHP < 8.0.
 *
 * Transpiled code (e.g. lucatume\WPBrowser\Utils\Packer) references PHP 8.0 token
 * constants directly in class-constant arrays, which cannot be guarded with constant().
 * On PHP < 8.0 those constants are undefined and loading the class fatals. The polyfilled
 * value is an arbitrary high integer that never collides with a real 7.x token id, so the
 * comparisons it feeds simply never match (no `?->` can exist in valid PHP 7.x source).
 *
 * The T_NAME_* tokens are also defined: the code resolves them through constant(), which on
 * PHP < 8.0 emits a "Couldn't find constant" warning (fatal under PHPUnit) when they are
 * undefined. The PHP 7.x tokenizer never emits these ids, so the sentinel values never match.
 */

if (PHP_VERSION_ID < 80000) {
    if (!defined('T_NULLSAFE_OBJECT_OPERATOR')) {
        define('T_NULLSAFE_OBJECT_OPERATOR', 10001);
    }
    if (!defined('T_NAME_QUALIFIED')) {
        define('T_NAME_QUALIFIED', 10002);
    }
    if (!defined('T_NAME_FULLY_QUALIFIED')) {
        define('T_NAME_FULLY_QUALIFIED', 10003);
    }
    if (!defined('T_NAME_RELATIVE')) {
        define('T_NAME_RELATIVE', 10004);
    }
}
