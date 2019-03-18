var MarkupIt = require('../');

describe('Custom Syntax', function() {
    var syntax = MarkupIt.Syntax('mysyntax', {
        inline: [
            MarkupIt.Rule(MarkupIt.STYLES.BOLD)
                .regExp(/^\*\*([\s\S]+?)\*\*/, function(state, match) {
                    return {
                        text: match[1]
                    };
                })
                .toText('**%s**')
        ]
    });
    var markup = new MarkupIt(syntax);

    describe('.toContent', function() {
        it('should return correct syntax name', function() {
            var content = markup.toContent('Hello');
            content.getSyntax().should.equal('mysyntax');
        });

        it('should parse as unstyled', function() {
            var content = markup.toContent('Hello World');

            var doc = content.getToken();
            var blocks = doc.getTokens();

            blocks.size.should.equal(1);
            var p = blocks.get(0);

            p.getType().should.equal(MarkupIt.BLOCKS.TEXT);
            p.getAsPlainText().should.equal('Hello World');
        });

        it('should parse inline', function() {
            var content = markup.toContent('Hello **World**');
            var doc = content.getToken();
            var blocks = doc.getTokens();

            blocks.size.should.equal(1);
            var p = blocks.get(0);

            p.getType().should.equal(MarkupIt.BLOCKS.TEXT);
            p.getAsPlainText().should.equal('Hello World');
        });
    });

    describe('.toText', function() {
        it('should output correct string', function() {
            var content = MarkupIt.JSONUtils.decode({
                syntax: 'mysyntax',
                token: {
                    type: MarkupIt.BLOCKS.DOCUMENT,
                    tokens: [
                        {
                            type: MarkupIt.BLOCKS.PARAGRAPH,
                            tokens: [
                                {
                                    type: MarkupIt.STYLES.TEXT,
                                    text: 'Hello '
                                },
                                {
                                    type: MarkupIt.STYLES.BOLD,
                                    text: 'World'
                                }
                            ]
                        }
                    ]
                }
            });
            var text = markup.toText(content);
            text.should.equal('Hello **World**\n');
        });
    });
});
