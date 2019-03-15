var decode = require('../decode');
var BLOCKS = require('../../constants/blocks');

describe('decode', function() {
    var content;

    before(function() {
        content = decode({
            token: {
                type: BLOCKS.DOCUMENT,
                tokens: [
                    {
                        type: BLOCKS.PARAGRAPH,
                        text: 'Hello World',
                        raw: 'Hello World'
                    }
                ]
            }
        });
    });

    it('should decode tokens tree', function() {
        var doc = content.getToken();
        var tokens = doc.getTokens();
        tokens.size.should.equal(1);

        var p = tokens.get(0);
        p.getType().should.equal(BLOCKS.PARAGRAPH);
        p.getText().should.equal('Hello World');
        p.getRaw().should.equal('Hello World');
        p.getTokens().size.should.equal(0);
    });
});
