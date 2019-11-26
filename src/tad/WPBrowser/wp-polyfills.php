<?php
/**
 * WP functions, ported over.
 *
 * These functions are copy & paste, or small modification, of WordPress Core functions.
 * To keep the "familiarity" the function names are snake_case.
 */

namespace tad\WPBrowser;

use VRia\Utils\NoDiacritic;

/**
 * Strip tags from a string.
 *
 * @param string $string       The string to strip tags from.
 * @param bool   $removeBreaks Whether to remove breaks and new lines or not.
 *
 * @return string The clean version of the input string.
 */
function strip_all_tags($string, $removeBreaks = false)
{
    $string = strip_tags(preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string));

    if ($removeBreaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return trim($string);
}

/**
 * Removes accents from string.
 *
 * @param string $string The string to remove accents from.
 * @return string The clean string.
 */
function remove_accents($string)
{
    return NoDiacritic::filter($string);
}

/**
 * Sanitizes a user login name.
 *
 * @param string $username The user name to sanitize.
 * @param bool   $strict   Whether to reduce the ASCII set or not.
 *
 * @return string The normalized username.
 */
function sanitize_user($username, $strict = false)
{
    $username = remove_accents(strip_all_tags($username));
    $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
    $username = preg_replace('/&.+?;/', '', $username); // Kill entities

    if ($strict) {
        $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
    }

    return preg_replace('|\s+|', ' ', trim($username));
}
