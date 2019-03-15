"use strict";


var ltrim = require('..');


/**
 * Strip whitespace from the beginning of a string
 */

console.log(
    ltrim(
        '     Hello     '
    ) + ' World'
);

// Hello      World


/**
 * Strip multiple special chars (e.g. space & dot) from the beginning of a string
 */

console.log(
    ltrim(
        '... Hello World ...',
        ' .'
    )
);

// Hello World ...


/**
 * Strip multiple chars from the beginning of a string
 */

console.log(
    ltrim(
        'Hello World',
        'Hdle'
    )
);

// o World


/**
 * Strip url protocol from the beginning of a string
 */

console.log(
    ltrim(
        'https://goo.gl/',
        '/:htps'
    )
);

// goo.gl/
