"use strict";


var rtrim = require('..');


/**
 * Strip whitespace from the end of a string
 */

console.log(
    rtrim(
        '    Hello    '
    ) + ' World'
);

//      Hello World


/**
 * Strip multiple special chars (e.g. space & dot) from the end of a string
 */

console.log(
    rtrim(
        '... Hello World ...',
        ' .'
    )
);

// ... Hello World


/**
 * Strip multiple chars from the end of a string
 */

console.log(
    rtrim(
        'Hello World',
        'Hdle'
    )
);

// Hello Wor


/**
 * Strip trailing slash from the end of a string
 */

console.log(
    rtrim(
        'https://goo.gl/',
        '/'
    )
);

// https://goo.gl
