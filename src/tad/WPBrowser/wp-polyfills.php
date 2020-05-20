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
 * @param string $string The string to strip tags from.
 * @param bool $removeBreaks Whether to remove breaks and new lines or not.
 *
 * @return string The clean version of the input string.
 *
 * @throws \InvalidArgumentException If the string cannot be sanitized.
 */
function strip_all_tags($string, $removeBreaks = false)
{
    $woTags = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);

    if (!is_string($woTags)) {
        throw new \InvalidArgumentException("Could not remove tags from '{$string}'.");
    }

    $string = strip_tags($woTags);

    if ($removeBreaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        if (!is_string($string)) {
            throw new \InvalidArgumentException("Could not remove breaks from '{$string}'.");
        }
    }

    return trim($string);
}

/**
 * Removes accents from string.
 *
 * @param string $string The string to remove accents from.
 *
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
 * @param bool $strict Whether to reduce the ASCII set or not.
 *
 * @return string The normalized username.
 *
 * @throws \InvalidArgumentException If the username cannot be sanitized.
 */
function sanitize_user($username, $strict = false)
{
    $username = remove_accents(strip_all_tags($username));
    $username = preg_replace(['|%([a-fA-F0-9][a-fA-F0-9])|', '/&.+?;/'], ['', ''], $username);

    if (!is_string($username)) {
        throw new \InvalidArgumentException("Could not sanitize username '{$username}'.");
    }

    if ($strict) {
        $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);

        if (!is_string($username)) {
            throw new \InvalidArgumentException("Could not apply strict sanitize to username '{$username}'.");
        }
    }

    $sanitized = preg_replace('|\s+|', ' ', trim($username));

    if (!is_string($sanitized)) {
        throw new \InvalidArgumentException("Could not normalize whitespaces in username '{$username}'.");
    }

    return $sanitized;
}
