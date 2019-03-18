"use strict";


/**
 * Strip whitespace - or other characters - from the beginning of a string
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

    // Remove whitespace
    if ( ! chars ) {
        return str.replace( /^\s+/, '' );
    }

    // Convert to string
    chars = chars.toString();

    // Set vars
    var i = 0,
        letters = str.split(''),
        count = letters.length;

    // Loop letters
    for ( i; i < count; i ++ ) {
        if ( chars.indexOf( letters[i] ) === -1 ) {
            return str.substring( i );
        }
    }

    return str;

};
