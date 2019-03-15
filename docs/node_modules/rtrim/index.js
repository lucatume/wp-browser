"use strict";


/**
 * Strip whitespace - or other characters - from the end of a string
 *
 * @param  {String} str   Input string
 * @param  {String} chars Character(s) to strip [optional]
 * @return {String} str   Modified string
 */

module.exports = function ( str, chars ) {

    // Convert to string
    str = str.toString();

    // Empty string?
    if ( ! str ) {
        return '';
    }

    // Remove whitespace if chars arg is empty
    if ( ! chars ) {
        return str.replace( /\s+$/, '' );
    }

    // Convert to string
    chars = chars.toString();

    // Set vars
    var letters = str.split(''),
        i = letters.length - 1;

    // Loop letters
    for ( i; i >= 0; i --) {
        if ( chars.indexOf( letters[i] ) === -1 ) {
            return str.substring(0, i + 1);
        }
    }

    return str;

};
