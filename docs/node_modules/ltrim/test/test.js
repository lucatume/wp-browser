"use strict";


var ltrim = require('..'),
    range = require('array-range'),
    shuffle = require('shuffle-array'),
    expect = require('chai').expect;


describe( 'Strip characters from the beginning of a string', function() {


    // Strip url protocol from the beginning
    it( 'Strip url protocol', function() {

        expect(
            ltrim( 'https://goo.gl/', '/:htps' )
        ).to.equal(
            'goo.gl/'
        );

    } );


    // Strip whitespace from the beginning (default)
    it( 'Strip whitespace', function() {

        expect(
            ltrim( '    Hello World    ' )
        ).to.equal(
            'Hello World    '
        );

    } );


    // Strip random special chars from the beginning
    it( 'Strip random special chars', function() {

        expect(
            ltrim(
                shuffledSpecialChars() + 'Hello World',
                shuffledSpecialChars() // shuffle again
            )
        ).to.equal(
            'Hello World'
        );

    } );


} );


/**
 * Get shuffled special chars from a custom code range
 *
 * @return {String} Shuffled special characters
 */

var shuffledSpecialChars = function() {

    var chars = [];

    range( 32, 65 ).concat( [91, 92, 93, 94, 95, 96, 123, 124, 125, 126] )
    .forEach( function( _ ) {
        chars.push( String.fromCharCode( _ ) );
    } );

    return shuffle( chars ).join('');

}
