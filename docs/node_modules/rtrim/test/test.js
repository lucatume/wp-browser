"use strict";


var rtrim = require('..'),
    range = require('array-range'),
    shuffle = require('shuffle-array'),
    expect = require('chai').expect;


describe( 'Strip characters from the end of a string', function() {


    // Strip trailing slash from the end
    it( 'Strip trailing slash', function() {

        expect(
            rtrim( 'https://goo.gl/', '/' )
        ).to.equal(
            'https://goo.gl'
        );

    } );


    // Strip whitespace from the end (default)
    it( 'Strip whitespace', function() {

        expect(
            rtrim( '    Hello World    ' )
        ).to.equal(
            '    Hello World'
        );

    } );


    // Strip random special chars from the end
    it( 'Strip random special chars', function() {

        expect(
            rtrim(
                'Hello World' + shuffledSpecialChars(),
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
