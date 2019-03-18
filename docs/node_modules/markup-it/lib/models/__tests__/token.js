var Token = require('../token');
var STYLES = require('../../constants/styles');

describe('Token', function() {
    describe('.mergeWith', function() {
        it('should merge text and raw', function() {
            var base = Token.createText('Hello ');
            var other = Token.createText('world');

            var token = base.mergeWith(other);
            token.getType().should.equal(STYLES.TEXT);
            token.getText().should.equal('Hello world');
        });
    });
});
