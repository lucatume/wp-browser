var Token = require('../../models/token');
var Content = require('../../models/content');
var encode = require('../encode');
var BLOCKS = require('../../constants/blocks');
var STYLES = require('../../constants/styles');

describe('encode', function() {
    var json;

    before(function() {
        var content = Content.createFromToken('testing',
            Token.create(BLOCKS.DOCUMENT, {
                tokens: [
                    Token.create(BLOCKS.PARAGRAPH, {
                        tokens: [
                            Token.createText('Hello '),
                            Token.create(STYLES.BOLD, {
                                tokens: [
                                    Token.createText('World')
                                ]
                            })
                        ]
                    })
                ]
            })
        );

        json = encode(content);
    });

    it('should encode tokens', function() {
        json.should.have.property('token');

        var doc = json.token;
        doc.tokens.should.have.lengthOf(1);

        var p = doc.tokens[0];
        p.type.should.equal(BLOCKS.PARAGRAPH);
        p.tokens.should.have.lengthOf(2);

        var text = p.tokens[0];
        text.text.should.equal('Hello ');

        var bold = p.tokens[1];
        bold.tokens[0].text.should.equal('World');
    });
});
