var MarkupIt = require('../../');
var htmlToTokens = require('./parse');

var documentRule = MarkupIt.Rule(MarkupIt.BLOCKS.DOCUMENT)
    .match(function(state, text) {
        return {
            tokens: htmlToTokens(text)
        };
    })
    .toText(function(state, token) {
        return state.renderAsBlock(token);
    });


module.exports = MarkupIt.Syntax('html', {
    entryRule: documentRule,

    // List of rules for parsing blocks
    inline: require('./inline'),

    // List of rules for parsing inline styles/entities
    blocks: require('./blocks')
});
